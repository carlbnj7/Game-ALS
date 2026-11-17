<?php
need('admin');
$keys = ['division_name' => 'Division name', 'region_name' => 'Region', 'sds_name' => 'Name of the Schools Division Superintendent', 'sds_title' => 'Title under the name'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($keys as $k => $_) q('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)', [$k, trim((string)($_POST[$k] ?? ''))]);
    flash('Settings saved.'); redirect('settings');
}
render('settings', ['title' => 'Settings', 'keys' => $keys]);
