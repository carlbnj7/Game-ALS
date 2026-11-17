<?php
need('admin');
$f = ['grade' => isset(GRADES[$_GET['grade'] ?? '']) ? $_GET['grade'] : 'ELEM', 's' => isset(STRANDS[$_GET['s'] ?? '']) ? $_GET['s'] : 'LS1-EN', 'l' => max(1, min(3, (int)($_GET['l'] ?? 1))), 'a' => (int)($_GET['a'] ?? 0), 'show' => (string)($_GET['show'] ?? ''), 'q' => trim((string)($_GET['q'] ?? ''))];
$where = 'grade = ? AND strand = ? AND level = ?'; $args = [$f['grade'], $f['s'], $f['l']];
if ($f['a'] >= 1 && $f['a'] <= 3) { $where .= ' AND activity = ?'; $args[] = $f['a']; }
if ($f['show'] === 'hidden') $where .= ' AND active = 0';
if ($f['q'] !== '') { $where .= ' AND (question LIKE ? OR passage LIKE ?)'; $args[] = '%' . $f['q'] . '%'; $args[] = '%' . $f['q'] . '%'; }
$rows = all("SELECT id, activity, num, question, answer, active, image, (passage IS NOT NULL) has_p, (SELECT COUNT(*) FROM answers a WHERE a.item_id = items.id) tries, (SELECT ROUND(AVG(correct) * 100) FROM answers a WHERE a.item_id = items.id) pct FROM items WHERE $where ORDER BY num", $args);
render('questions', ['title' => 'Questions', 'rows' => $rows, 'f' => $f]);
