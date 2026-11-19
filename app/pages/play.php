<?php
$u = need('learner');
$s = (string)($_GET['s'] ?? ''); $l = (int)($_GET['l'] ?? 0); $a = (int)($_GET['a'] ?? 0);
if (!isset(STRANDS[$s]) || $l < 1 || $l > 3 || $a < 1 || $a > 3) redirect('home');
$pm = progress_map((int)$u['id']);
if (!strand_open($pm, $s)) { flash(level_lock_text($pm, $s, 1) . ' to open this strand.', 'err'); redirect('home'); }
if (!level_open($pm, $s, $l)) { flash(level_lock_text($pm, $s, $l) . ' to open Level ' . $l . '.', 'err'); redirect('strand', ['s' => $s]); }
if (!activity_open($pm, $s, $l, $a, $u['grade'] ?: 'ELEM')) { flash('Pass Activity ' . ($a - 1) . ' with 75% or more first to open Activity ' . $a . '.', 'err'); redirect('strand', ['s' => $s]); }
if (!activity_items($u['grade'] ?: 'ELEM', $s, $l, $a)) { flash('This activity has no questions yet.', 'err'); redirect('strand', ['s' => $s]); }
render('play', ['title' => STRANDS[$s][0] . ' ' . STRANDS[$s][1] . ' · Level ' . $l, 'bare' => true, 'bodyClass' => 'playing', 's' => $s, 'l' => $l, 'a' => $a, 'scripts' => ['play.js']]);
