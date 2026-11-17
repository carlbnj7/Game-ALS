<?php
$u = need('learner');
$pm = progress_map((int)$u['id']);
award_certs((int)$u['id']);
$certs = all('SELECT * FROM certificates WHERE user_id = ? ORDER BY type = "readiness" DESC, issued_at', [$u['id']]);
$have = [];
foreach ($certs as $c) $have[$c['type'] . '|' . $c['strand'] . '|' . $c['level']] = $c;
render('certs', ['title' => 'Certificates', 'pm' => $pm, 'have' => $have]);
