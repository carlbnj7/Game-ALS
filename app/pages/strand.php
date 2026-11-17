<?php
$u = need('learner');
$s = (string)($_GET['s'] ?? '');
if (!isset(STRANDS[$s])) redirect('home');
$pm = progress_map((int)$u['id']);
if (!strand_open($pm, $s)) { $pv = previous_strand($s); flash('Pass every activity of ' . STRANDS[$pv][0] . ' ' . STRANDS[$pv][1] . ' with 75% or more to open ' . STRANDS[$s][0] . ' ' . STRANDS[$s][1] . '.', 'err'); redirect('home'); }
$counts = [];
foreach (all('SELECT level, activity, COUNT(*) n FROM items WHERE grade = ? AND strand = ? AND active = 1 GROUP BY level, activity', [$u['grade'] ?: 'ELEM', $s]) as $r) $counts[$r['level'] . '|' . $r['activity']] = (int)$r['n'];
$playing = [];
foreach (all('SELECT level, activity, pos FROM attempts WHERE user_id = ? AND strand = ? AND status = "playing"', [$u['id'], $s]) as $r) $playing[$r['level'] . '|' . $r['activity']] = (int)$r['pos'];
render('strand', ['title' => STRANDS[$s][0] . ' ' . STRANDS[$s][1], 's' => $s, 'pm' => $pm, 'counts' => $counts, 'playing' => $playing]);
