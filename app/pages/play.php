<?php
$u = need('learner');
$s = (string)($_GET['s'] ?? ''); $l = (int)($_GET['l'] ?? 0); $a = (int)($_GET['a'] ?? 0);
if (!isset(STRANDS[$s]) || $l < 1 || $l > 3 || $a < 1 || $a > 3) redirect('home');
$pm = progress_map((int)$u['id']);
if (!strand_open($pm, $s)) { $pv = previous_strand($s); flash('Pass every activity of ' . STRANDS[$pv][0] . ' ' . STRANDS[$pv][1] . ' first to open this strand.', 'err'); redirect('home'); }
if (!level_open($pm, $s, $l)) { flash('Pass all of Level 1 first to open this level.', 'err'); redirect('strand', ['s' => $s]); }
if (!activity_open($pm, $s, $l, $a, $u['grade'] ?: 'ELEM')) { flash('Pass Activity ' . ($a - 1) . ' with 75% or more first to open Activity ' . $a . '.', 'err'); redirect('strand', ['s' => $s]); }
if (!activity_items($u['grade'] ?: 'ELEM', $s, $l, $a)) { flash('This activity has no questions yet.', 'err'); redirect('strand', ['s' => $s]); }
render('play', ['title' => STRANDS[$s][0] . ' ' . STRANDS[$s][1] . ' · Level ' . $l, 'bare' => true, 'bodyClass' => 'playing', 's' => $s, 'l' => $l, 'a' => $a, 'scripts' => ['play.js']]);
