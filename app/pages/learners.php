<?php
$u = need('teacher', 'admin');
$clcs = my_clcs($u);
$ids = array_map('intval', array_column($clcs, 'id'));
$pf = place_filter(); $clc = (int)($_GET['clc'] ?? 0); $grade = (string)($_GET['grade'] ?? ''); $find = trim((string)($_GET['q'] ?? ''));
$where = ["l.role = 'learner'"]; $args = [];
if ($clc && in_array($clc, $ids, true)) { $where[] = 'l.clc_id = ?'; $args[] = $clc; }
elseif ($u['role'] === 'teacher') $where[] = 'l.clc_id IN (' . ($ids ? implode(',', $ids) : '0') . ')';
if ($u['role'] === 'admin') place_where($pf, $where, $args, 'c');
if (isset(GRADES[$grade])) { $where[] = 'l.grade = ?'; $args[] = $grade; }
if ($find !== '') { $where[] = '(l.first_name LIKE ? OR l.last_name LIKE ? OR l.username LIKE ? OR l.lrn LIKE ? OR l.als_id LIKE ?)'; array_push($args, "%$find%", "%$find%", "%$find%", "%$find%", "%$find%"); }
$rows = all('SELECT l.*, c.name clc, COALESCE(SUM(p.passed), 0) passed, ROUND(AVG(p.best_score)) avg, MAX(a.finished_at) last_play
  FROM users l LEFT JOIN clcs c ON c.id = l.clc_id LEFT JOIN progress p ON p.user_id = l.id
  LEFT JOIN attempts a ON a.user_id = l.id AND a.status <> "playing"
  WHERE ' . implode(' AND ', $where) . ' GROUP BY l.id ORDER BY l.active DESC, l.last_name, l.first_name LIMIT 500', $args);
$shown = $_SESSION['new_accounts'] ?? null;
render('learners', ['title' => 'Learners', 'rows' => $rows, 'clcs' => $clcs, 'f' => ['clc' => $clc, 'grade' => $grade, 'q' => $find] + $pf, 'shown' => $shown,
  'muns' => $u['role'] === 'admin' ? municipalities() : [], 'dists' => $u['role'] === 'admin' ? districts() : [], 'scripts' => ['places.js']]);
