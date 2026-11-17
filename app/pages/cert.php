<?php
$u = need();
$c = one('SELECT c.*, l.first_name, l.middle_name, l.last_name, l.clc_id, l.grade, l.role FROM certificates c JOIN users l ON l.id = c.user_id WHERE c.code = ?', [(string)($_GET['code'] ?? '')]);
if (!$c) { render('error', ['title' => 'Not found', 'msg' => 'That certificate does not exist.']); return; }
$owner = one('SELECT * FROM users WHERE id = ?', [$c['user_id']]);
if (!($u['id'] == $c['user_id'] || can_manage_learner($u, $owner))) { render('error', ['title' => 'Not allowed', 'msg' => 'You cannot open this certificate.']); return; }
$clc = $c['clc_id'] ? one('SELECT * FROM clcs WHERE id = ?', [$c['clc_id']]) : null;
render('cert', ['title' => 'Certificate', 'bare' => true, 'bodyClass' => 'certpage', 'c' => $c, 'clc' => $clc, 'name' => full_name($owner)]);
