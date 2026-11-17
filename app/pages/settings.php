<?php
need('admin');
$keys = ['division_name' => 'Division name', 'region_name' => 'Region', 'sds_name' => 'Name of the Schools Division Superintendent', 'sds_title' => 'Title under the name'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($keys as $k => $_) q('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)', [$k, trim((string)($_POST[$k] ?? ''))]);
    $sec = (int)($_POST['item_seconds'] ?? 60); $sec = isset($_POST['timer_on']) ? max(10, min(600, $sec)) : 0;
    q('INSERT INTO settings (k, v) VALUES ("item_seconds", ?) ON DUPLICATE KEY UPDATE v = VALUES(v)', [(string)$sec]);
    flash('Settings saved.'); redirect('settings');
}
render('settings', ['title' => 'Settings', 'keys' => $keys]);
