<?php
$u = need('teacher', 'admin');
$l = one('SELECT * FROM users WHERE id = ?', [(int)($_GET['id'] ?? 0)]);
if (!$l || !can_manage_learner($u, $l)) { render('error', ['title' => 'Not found', 'msg' => 'That learner is not in your CLCs.']); return; }
$clcs = my_clcs($u); $ids = array_map('intval', array_column($clcs, 'id'));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $do = $_POST['do'] ?? '';
    if ($do === 'save') {
        $clc = (int)$_POST['clc_id'];
        if (!in_array($clc, $ids, true)) $clc = (int)$l['clc_id'];
        q('UPDATE users SET first_name = ?, middle_name = ?, last_name = ?, lrn = ?, sex = ?, birthdate = ?, grade = ?, clc_id = ? WHERE id = ?', [
            proper((string)$_POST['first_name']) ?: $l['first_name'], proper((string)$_POST['middle_name']), proper((string)$_POST['last_name']) ?: $l['last_name'],
            preg_replace('/\D/', '', (string)$_POST['lrn']), in_array($_POST['sex'] ?? '', ['M', 'F'], true) ? $_POST['sex'] : '',
            preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$_POST['birthdate']) ? $_POST['birthdate'] : null,
            isset(GRADES[$_POST['grade'] ?? '']) ? $_POST['grade'] : $l['grade'], $clc, $l['id']]);
        flash('Saved.');
    } elseif ($do === 'reset') {
        $pw = make_password();
        q('UPDATE users SET password_hash = ?, must_change = 1, pin_fails = 0 WHERE id = ?', [password_hash($pw, PASSWORD_DEFAULT), $l['id']]);
        $_SESSION['new_accounts'][] = [full_name($l), $l['als_id'], $pw, $l['grade']];
        flash('New password made. It is on the sign-in slips page.');
    } elseif ($do === 'pin') {
        q('UPDATE users SET pin_hash = NULL, pin_fails = 0 WHERE id = ?', [$l['id']]);
        flash('PIN cleared. The learner makes a new PIN on their next sign-in.');
    } elseif ($do === 'toggle') {
        q('UPDATE users SET active = 1 - active WHERE id = ?', [$l['id']]);
        flash($l['active'] ? 'This learner can no longer sign in.' : 'This learner can sign in again.');
    }
    redirect('learner', ['id' => $l['id']]);
}
$pm = progress_map((int)$l['id']);
$mastered = []; $least = [];
foreach ($pm as $k => $p) {
    [$s, $lv, $a] = explode('|', $k);
    $row = ['label' => STRANDS[$s][0] . ' ' . STRANDS[$s][1] . ' · Level ' . $lv . ' · Activity ' . $a . ' (items ' . activity_range((int)$a) . ')', 'score' => (float)$p['best_score'], 'tries' => (int)$p['tries']];
    if ($p['passed']) $mastered[] = $row; else $least[] = $row;
}
usort($mastered, fn($x, $y) => $y['score'] <=> $x['score']);
usort($least, fn($x, $y) => $x['score'] <=> $y['score']);
// questions this learner missed most
$missed = all('SELECT i.strand, i.level, i.num, LEFT(i.question, 140) q, COUNT(*) n FROM answers a JOIN items i ON i.id = a.item_id WHERE a.user_id = ? AND a.correct = 0 GROUP BY a.item_id ORDER BY n DESC, i.strand, i.level, i.num LIMIT 10', [$l['id']]);
render('learner', ['title' => full_name($l), 'l' => $l, 'clcs' => $clcs, 'pm' => $pm, 'mastered' => $mastered, 'least' => $least, 'missed' => $missed,
  'certs' => all('SELECT * FROM certificates WHERE user_id = ? ORDER BY issued_at', [$l['id']]), 'points' => points_of((int)$l['id'])]);
