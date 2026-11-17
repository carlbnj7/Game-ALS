<?php
// Printable QR sign-in passes. Teachers: learners in their CLCs. Admin: everyone.
$u = need('teacher', 'admin');
$isAdmin = $u['role'] === 'admin';
$clcs = my_clcs($u); $ids = array_map('intval', array_column($clcs, 'id')) ?: [0];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $who = one('SELECT * FROM users WHERE id = ?', [(int)($_POST['id'] ?? 0)]);
    if ($who && ($isAdmin || can_manage_learner($u, $who))) {
        if (($_POST['do'] ?? '') === 'renew') { qr_token((int)$who['id'], true); q('UPDATE users SET pin_fails = 0 WHERE id = ?', [$who['id']]); flash('New QR code made for ' . full_name($who) . '. The old pass no longer works.'); }
        if (($_POST['do'] ?? '') === 'unlock') { q('UPDATE users SET pin_fails = 0 WHERE id = ?', [$who['id']]); flash(full_name($who) . ' can use their pass again.'); }
    }
    redirect('passes', array_intersect_key($_POST, array_flip(['role', 'clc', 'grade', 'mun', 'dist', 'q'])));
}
$f = ['role' => $isAdmin && in_array($_GET['role'] ?? '', ['learner', 'teacher', 'admin'], true) ? $_GET['role'] : 'learner',
      'clc' => (int)($_GET['clc'] ?? 0), 'grade' => (string)($_GET['grade'] ?? ''), 'q' => trim((string)($_GET['q'] ?? ''))] + place_filter();
$where = ['u.active = 1', 'u.role = ?']; $args = [$f['role']];
if ($f['role'] === 'learner') {
    if ($f['clc'] && in_array($f['clc'], $ids, true)) { $where[] = 'u.clc_id = ?'; $args[] = $f['clc']; }
    elseif (!$isAdmin) $where[] = 'u.clc_id IN (' . implode(',', $ids) . ')';
    if (isset(GRADES[$f['grade']])) { $where[] = 'u.grade = ?'; $args[] = $f['grade']; }
    if ($isAdmin) place_where($f, $where, $args, 'c');
} elseif ($f['role'] === 'teacher') {
    $w2 = []; $a2 = [];
    if ($f['clc']) { $w2[] = 'c.id = ?'; $a2[] = $f['clc']; }
    place_where($f, $w2, $a2, 'c');
    if ($w2) { $where[] = 'u.id IN (SELECT t.teacher_id FROM teacher_clc t JOIN clcs c ON c.id = t.clc_id WHERE ' . implode(' AND ', $w2) . ')'; $args = array_merge($args, $a2); }
}
if ($f['q'] !== '') { $where[] = '(u.first_name LIKE ? OR u.last_name LIKE ? OR u.username LIKE ? OR u.als_id LIKE ?)'; array_push($args, "%{$f['q']}%", "%{$f['q']}%", "%{$f['q']}%", "%{$f['q']}%"); }
$rows = all('SELECT u.*, c.name clc, c.municipality, c.district,
    (SELECT GROUP_CONCAT(x.name ORDER BY x.name SEPARATOR ", ") FROM teacher_clc t JOIN clcs x ON x.id = t.clc_id WHERE t.teacher_id = u.id) tclcs
  FROM users u LEFT JOIN clcs c ON c.id = u.clc_id WHERE ' . implode(' AND ', $where) . ' ORDER BY c.name, u.last_name, u.first_name LIMIT 300', $args);
foreach ($rows as &$r) if ($r['qr_token'] === null) $r['qr_token'] = qr_token((int)$r['id']);
unset($r);
render('passes', ['title' => 'QR passes', 'rows' => $rows, 'clcs' => $clcs, 'f' => $f, 'isAdmin' => $isAdmin, 'base' => base_url() . 'index.php?p=qr&t=',
  'muns' => $isAdmin ? municipalities() : [], 'dists' => $isAdmin ? districts() : [], 'scripts' => ['qrcode.js', 'passes.js', 'places.js']]);
