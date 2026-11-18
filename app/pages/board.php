<?php
$u = need();
$where = ["l.role = 'learner'", 'l.active = 1']; $args = []; $scope = '';
$clcs = []; $f = ['clc' => (int)($_GET['clc'] ?? 0), 'mun' => (int)($_GET['mun'] ?? 0), 'dist' => (int)($_GET['dist'] ?? 0), 'grade' => (string)($_GET['grade'] ?? '')];
if ($u['role'] === 'learner') {
    $where[] = 'l.grade <=> ?'; $args[] = $u['grade'];
    $scope = GRADES[$u['grade'] ?: 'ELEM'] . ' learners';
} elseif ($u['role'] === 'teacher') {
    $ids = teacher_clc_ids((int)$u['id']) ?: [0];
    $clcs = all('SELECT * FROM clcs WHERE id IN (' . implode(',', $ids) . ') ORDER BY name');
    if ($f['clc'] && in_array($f['clc'], $ids, true)) { $where[] = 'l.clc_id = ?'; $args[] = $f['clc']; }
    else $where[] = 'l.clc_id IN (' . implode(',', $ids) . ')';
    $scope = 'Your CLCs';
} else {
    $clcs = all('SELECT * FROM clcs ORDER BY name');
    if ($f['clc']) { $where[] = 'l.clc_id = ?'; $args[] = $f['clc']; }
    place_where($f, $where, $args, 'c');
    $scope = 'Whole division';
}
if ($u['role'] !== 'learner' && isset(GRADES[$f['grade']])) { $where[] = 'l.grade = ?'; $args[] = $f['grade']; }
$rows = all('SELECT l.id, l.avatar, l.first_name, l.middle_name, l.last_name, l.grade, c.name clc, c.municipality,
    COALESCE(SUM(p.best_correct * 10 + p.passed * 50), 0) pts, COALESCE(SUM(p.passed), 0) passed,
    (SELECT COUNT(*) FROM certificates x WHERE x.user_id = l.id) certs
  FROM users l LEFT JOIN clcs c ON c.id = l.clc_id LEFT JOIN progress p ON p.user_id = l.id
  WHERE ' . implode(' AND ', $where) . ' GROUP BY l.id HAVING pts > 0 ORDER BY pts DESC, passed DESC, l.last_name LIMIT 100', $args);
render('board', ['title' => 'Leaderboard', 'rows' => $rows, 'scope' => $scope, 'clcs' => $clcs, 'f' => $f,
  'muns' => $u['role'] === 'admin' ? municipalities() : [], 'dists' => $u['role'] === 'admin' ? districts() : [], 'scripts' => ['places.js']]);
