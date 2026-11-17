<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$u = me();
function out(array $d, int $code = 200): void { http_response_code($code); echo json_encode($d, JSON_UNESCAPED_UNICODE); exit; }
if (!$u || $u['role'] !== 'learner') out(['error' => 'Please sign in again.'], 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(['error' => 'Bad request.'], 405);
$in = json_decode(file_get_contents('php://input') ?: '{}', true) ?: [];
$do = (string)($in['do'] ?? '');
$s = (string)($in['s'] ?? ''); $l = (int)($in['l'] ?? 0); $a = (int)($in['a'] ?? 0);
if (!isset(STRANDS[$s]) || $l < 1 || $l > 3 || $a < 1 || $a > 3) out(['error' => 'Bad activity.'], 400);
$uid = (int)$u['id'];
if (!level_open(progress_map($uid), $s, $l)) out(['error' => 'This level is still locked.'], 403);

$meta = ['strand' => STRANDS[$s][0] . ' ' . STRANDS[$s][1], 'level' => $l, 'levelName' => LEVELS[$l], 'activity' => $a];

if ($do === 'state' || $do === 'restart') {
    $at = $do === 'restart' ? null : current_attempt($uid, $s, $l, $a);
    if (!$at) $at = start_attempt($u, $s, $l, $a);
    if (!$at) out(['error' => 'This activity has no questions yet.'], 404);
    out(['meta' => $meta, 'state' => attempt_state($at)]);
}

db()->beginTransaction();
$at = one('SELECT * FROM attempts WHERE user_id = ? AND strand = ? AND level = ? AND activity = ? AND status = "playing" ORDER BY id DESC LIMIT 1 FOR UPDATE', [$uid, $s, $l, $a]);
if (!$at) { db()->rollBack(); out(['error' => 'This activity is not running. Start it again.'], 409); }
$ids = array_map('intval', explode(',', $at['item_ids']));
$pos = (int)$at['pos'];
if ($pos >= count($ids)) { db()->rollBack(); out(['error' => 'This activity is already finished.'], 409); }
$it = one('SELECT * FROM items WHERE id = ?', [$ids[$pos]]);
if ((int)($in['pos'] ?? -1) !== $pos) { db()->rollBack(); out(['error' => 'stale', 'state' => attempt_state($at), 'meta' => $meta], 409); }

if ($do === 'hint') {
    if ((int)$at['hinted_pos'] === $pos) { db()->commit(); out(['remove' => hint_letters($it, (int)$at['id']), 'hints' => (int)$at['hints']]); }
    if ((int)$at['hints'] < 1) { db()->rollBack(); out(['error' => 'No hints left for this activity.'], 409); }
    q('UPDATE attempts SET hints = hints - 1, hinted_pos = ? WHERE id = ?', [$pos, $at['id']]);
    db()->commit();
    out(['remove' => hint_letters($it, (int)$at['id']), 'hints' => (int)$at['hints'] - 1]);
}

if ($do === 'answer') {
    $choice = strtoupper((string)($in['choice'] ?? ''));
    $timedOut = $choice === 'T' && (int)setting('item_seconds', '60') > 0;
    if (!$timedOut && !in_array($choice, ['A', 'B', 'C', 'D'], true)) { db()->rollBack(); out(['error' => 'Pick an answer first.'], 400); }
    $ok = $choice === $it['answer'];
    $lives = (int)$at['lives']; $streak = (int)$at['streak']; $gained = false;
    if ($ok) {
        $streak++;
        if ($streak % STREAK_FOR_LIFE === 0 && $lives < MAX_LIVES) { $lives++; $gained = true; }
    } else { $lives--; $streak = 0; }
    q('INSERT INTO answers (attempt_id, user_id, item_id, choice, correct) VALUES (?, ?, ?, ?, ?)', [$at['id'], $uid, $it['id'], $choice, (int)$ok]);
    q('UPDATE attempts SET pos = pos + 1, correct = correct + ?, lives = ?, streak = ?, best_streak = GREATEST(best_streak, ?) WHERE id = ?', [(int)$ok, $lives, $streak, $streak, $at['id']]);
    $at = one('SELECT * FROM attempts WHERE id = ?', [$at['id']]);
    $result = null; $certs = [];
    if ($lives <= 0) {
        q('UPDATE attempts SET status = "lost", finished_at = NOW() WHERE id = ?', [$at['id']]);
        save_progress($uid, $at, 0.0, false, count($ids));
        $result = ['lost' => true, 'passed' => false, 'score' => (int)round($at['correct'] / count($ids) * 100)];
    } elseif ((int)$at['pos'] >= count($ids)) {
        $result = finish_attempt($at, $uid);
        $certs = $result['passed'] ? award_certs($uid) : [];
    }
    db()->commit();
    $at = one('SELECT * FROM attempts WHERE id = ?', [$at['id']]);
    $letters = ['A' => 'opt_a', 'B' => 'opt_b', 'C' => 'opt_c', 'D' => 'opt_d'];
    $resp = [
        'correct' => $ok, 'answer' => $it['answer'],
        'answerText' => $it['image_only'] ? '' : (string)$it[$letters[$it['answer']]],
        'explanation' => (string)$it['explanation'], 'lifeGained' => $gained,
        'state' => attempt_state($at), 'result' => $result,
    ];
    if ($result) {
        $resp['result']['correct'] = (int)$at['correct']; $resp['result']['total'] = count($ids);
        $resp['result']['bestStreak'] = (int)$at['best_streak'];
        $resp['result']['certs'] = array_map(fn($c) => $c[0] === 'readiness' ? 'Certificate of Readiness' : 'Certificate of Passing · ' . STRANDS[$c[1]][0] . ' ' . STRANDS[$c[1]][1] . ' Level ' . $c[2], $certs);
        $resp['result']['nextUrl'] = $a < 3 ? url('play', ['s' => $s, 'l' => $l, 'a' => $a + 1]) : url('strand', ['s' => $s]);
        $resp['result']['nextLabel'] = $a < 3 ? 'Next activity' : 'Back to levels';
    }
    out($resp);
}
db()->rollBack();
out(['error' => 'Unknown action.'], 400);
