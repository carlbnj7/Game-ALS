<?php
$u = need();
render('account', ['title' => 'Account', 'clc' => $u['clc_id'] ? one('SELECT * FROM clcs WHERE id = ?', [$u['clc_id']]) : null]);
