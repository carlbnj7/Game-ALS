<?php
$u = need();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $do = $_POST['do'] ?? '';
    if ($do === 'preset' && preg_match('/^a([1-9])$/', (string)($_POST['preset'] ?? ''), $m) && (int)$m[1] <= AVATAR_PRESETS) {
        remove_old_avatar($u['avatar']); q('UPDATE users SET avatar = ? WHERE id = ?', ['preset:a' . $m[1], $u['id']]); flash('Profile picture saved.');
    } elseif ($do === 'upload' && !empty($_FILES['photo'])) {
        [$name, $err] = save_avatar_upload($_FILES['photo'], (int)$u['id']);
        if ($err) flash($err, 'err'); else { remove_old_avatar($u['avatar']); q('UPDATE users SET avatar = ? WHERE id = ?', ['upload:' . $name, $u['id']]); flash('Profile picture saved.'); }
    } elseif ($do === 'remove') {
        remove_old_avatar($u['avatar']); q('UPDATE users SET avatar = NULL WHERE id = ?', [$u['id']]); flash('Profile picture removed.');
    }
    redirect('account');
}
render('account', ['title' => 'Account', 'clc' => $u['clc_id'] ? one('SELECT * FROM clcs WHERE id = ?', [$u['clc_id']]) : null]);
