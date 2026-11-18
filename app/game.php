<?php
declare(strict_types=1);

function activity_items(string $grade, string $strand, int $level, int $act): array {
    return all('SELECT * FROM items WHERE grade = ? AND strand = ? AND level = ? AND activity = ? AND active = 1 ORDER BY num', [$grade, $strand, $level, $act]);
}
function activity_range(int $act): string { $a = ($act - 1) * 25 + 1; return $a . '–' . ($a + 24); }

/** progress rows for a learner keyed "STRAND|L|A" */
function progress_map(int $uid): array {
    $m = [];
    foreach (all('SELECT * FROM progress WHERE user_id = ?', [$uid]) as $r) $m[$r['strand'] . '|' . $r['level'] . '|' . $r['activity']] = $r;
    return $m;
}
function level_done(array $pm, string $s, int $l): bool {
    for ($a = 1; $a <= 3; $a++) if (empty($pm["$s|$l|$a"]['passed'])) return false;
    return true;
}
function strand_done(array $pm, string $s): bool {
    for ($l = 1; $l <= 3; $l++) if (!level_done($pm, $s, $l)) return false;
    return true;
}
/** Strands go in order (LS1 English, LS1 Filipino, LS2 … LS6). A strand opens only after
 *  every activity of the strand before it is passed with 75% or more. */
function strand_open(array $pm, string $s): bool {
    foreach (STRANDS as $code => $_) {
        if ($code === $s) return true;
        if (!strand_done($pm, $code)) return false;
    }
    return false;
}
function previous_strand(string $s): ?string {
    $prev = null;
    foreach (STRANDS as $code => $_) { if ($code === $s) return $prev; $prev = $code; }
    return null;
}
/** Levels 2 and 3 open only after all of Level 1 is passed (and the strand itself is open). */
function level_open(array $pm, string $s, int $l): bool { return strand_open($pm, $s) && ($l === 1 || level_done($pm, $s, 1)); }
/** Inside a level, activities go in order: Activity 2 opens after Activity 1 is passed (75%), Activity 3 after Activity 2.
 *  An activity with no questions yet does not block the next one. */
function activity_open(array $pm, string $s, int $l, int $a, string $grade = 'ELEM'): bool {
    if (!level_open($pm, $s, $l)) return false;
    for ($b = 1; $b < $a; $b++) if (empty($pm["$s|$l|$b"]['passed']) && activity_items($grade, $s, $l, $b)) return false;
    return true;
}
function strand_done_count(array $pm, string $s): int {
    $n = 0; for ($l = 1; $l <= 3; $l++) for ($a = 1; $a <= 3; $a++) if (!empty($pm["$s|$l|$a"]['passed'])) $n++;
    return $n;
}
function all_done(array $pm): bool {
    foreach (STRANDS as $s => $_) if (!strand_done($pm, $s)) return false;
    return true;
}
function points_of(int $uid): int {
    return (int)val('SELECT COALESCE(SUM(best_correct * 10 + passed * 50), 0) FROM progress WHERE user_id = ?', [$uid]);
}

function new_cert_code(): string {
    $abc = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    do {
        $c = 'GA-'; for ($i = 0; $i < 8; $i++) $c .= $abc[random_int(0, strlen($abc) - 1)];
    } while (val('SELECT 1 FROM certificates WHERE code = ?', [$c]));
    return $c;
}
/** Give any certificates the learner has earned. Returns the new ones. */
function award_certs(int $uid): array {
    $pm = progress_map($uid); $new = [];
    foreach (STRANDS as $s => $_) for ($l = 1; $l <= 3; $l++) {
        if (level_done($pm, $s, $l) && !val('SELECT 1 FROM certificates WHERE user_id = ? AND type = "level" AND strand = ? AND level = ?', [$uid, $s, $l])) {
            q('INSERT INTO certificates (user_id, type, strand, level, code) VALUES (?, "level", ?, ?, ?)', [$uid, $s, $l, new_cert_code()]);
            $new[] = ['level', $s, $l];
        }
    }
    if (all_done($pm) && !val('SELECT 1 FROM certificates WHERE user_id = ? AND type = "readiness"', [$uid])) {
        q('INSERT INTO certificates (user_id, type, code) VALUES (?, "readiness", ?)', [$uid, new_cert_code()]);
        $new[] = ['readiness', '', 0];
    }
    return $new;
}

// ---------- attempts ----------
function current_attempt(int $uid, string $s, int $l, int $a): ?array {
    return one('SELECT * FROM attempts WHERE user_id = ? AND strand = ? AND level = ? AND activity = ? AND status = "playing" ORDER BY id DESC LIMIT 1', [$uid, $s, $l, $a]) ?: null;
}
function start_attempt(array $u, string $s, int $l, int $a): ?array {
    $items = activity_items($u['grade'] ?: 'ELEM', $s, $l, $a);
    if (!$items) return null;
    q('UPDATE attempts SET status = "quit", finished_at = NOW() WHERE user_id = ? AND strand = ? AND level = ? AND activity = ? AND status = "playing"', [$u['id'], $s, $l, $a]);
    q('INSERT INTO attempts (user_id, strand, level, activity, item_ids, lives, hints) VALUES (?, ?, ?, ?, ?, ?, ?)',
      [$u['id'], $s, $l, $a, implode(',', array_column($items, 'id')), START_LIVES, HINTS_PER_ACTIVITY]);
    return one('SELECT * FROM attempts WHERE id = ?', [db()->lastInsertId()]);
}
function attempt_state(array $at): array {
    $ids = array_map('intval', explode(',', $at['item_ids']));
    $total = count($ids);
    $st = [
        'id' => (int)$at['id'], 'status' => $at['status'], 'pos' => (int)$at['pos'], 'total' => $total,
        'correct' => (int)$at['correct'], 'lives' => (int)$at['lives'], 'maxLives' => MAX_LIVES,
        'hints' => (int)$at['hints'], 'streak' => (int)$at['streak'], 'bestStreak' => (int)$at['best_streak'],
        'streakGoal' => STREAK_FOR_LIFE, 'passMark' => (int)round(PASS_MARK * 100),
        'need' => (int)ceil(PASS_MARK * $total),
        'marks' => array_map('intval', array_column(all('SELECT correct FROM answers WHERE attempt_id = ? ORDER BY id', [$at['id']]), 'correct')),
    ];
    if ($at['status'] === 'playing' && $at['pos'] < $total) {
        $it = one('SELECT * FROM items WHERE id = ?', [$ids[(int)$at['pos']]]);
        $st['item'] = public_item($it, (int)$at['pos'] + 1);
        $st['hinted'] = ((int)$at['hinted_pos'] === (int)$at['pos']) ? hint_letters($it, (int)$at['id']) : [];
    }
    return $st;
}
function public_item(array $it, int $no): array {
    $opts = [];
    foreach (['A' => 'opt_a', 'B' => 'opt_b', 'C' => 'opt_c', 'D' => 'opt_d'] as $k => $c) {
        $opts[] = ['k' => $k, 't' => $it['image_only'] ? '' : (string)$it[$c]];
    }
    return [
        'no' => $no, 'num' => (int)$it['num'], 'passage' => $it['passage'], 'q' => $it['question'],
        'opts' => $opts, 'img' => $it['image'] ? array_map(fn($p) => 'assets/qimg/' . $p, explode(',', $it['image'])) : [],
        'imgOnly' => (bool)$it['image_only'],
    ];
}
/** Two wrong letters to take away, the same each time for this attempt. */
function hint_letters(array $it, int $attemptId): array {
    $wrong = array_values(array_diff(['A', 'B', 'C', 'D'], [$it['answer']]));
    mt_srand($attemptId * 1000 + (int)$it['id']);
    shuffle($wrong);
    mt_srand();
    $out = array_slice($wrong, 0, 2); sort($out);
    return $out;
}
function finish_attempt(array $at, int $uid): array {
    $ids = explode(',', $at['item_ids']); $total = count($ids);
    $score = $total ? $at['correct'] / $total : 0;
    $passed = $score + 1e-9 >= PASS_MARK;
    q('UPDATE attempts SET status = ?, finished_at = NOW() WHERE id = ?', [$passed ? 'passed' : 'failed', $at['id']]);
    save_progress($uid, $at, $score, $passed, $total);
    return ['passed' => $passed, 'score' => (int)round($score * 100)];
}
function save_progress(int $uid, array $at, float $score, bool $passed, int $total): void {
    q('INSERT INTO progress (user_id, strand, level, activity, best_score, best_correct, total, tries, passed, passed_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, IF(?, NOW(), NULL))
       ON DUPLICATE KEY UPDATE tries = tries + 1, total = VALUES(total),
         passed_at = IF(passed = 0 AND VALUES(passed) = 1, NOW(), passed_at),
         passed = GREATEST(passed, VALUES(passed)),
         best_correct = GREATEST(best_correct, VALUES(best_correct)),
         best_score = GREATEST(best_score, VALUES(best_score))',
      [$uid, $at['strand'], $at['level'], $at['activity'], round($score * 100, 2), $at['correct'], $total, (int)$passed, (int)$passed]);
}
