<?php
$u = need();
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cur = (string)($_POST['current'] ?? ''); $n1 = (string)($_POST['new1'] ?? ''); $n2 = (string)($_POST['new2'] ?? '');
    if (!password_verify($cur, $u['password_hash'])) $err = 'Your current password is wrong.';
    elseif (mb_strlen($n1) < 8) $err = 'Use at least 8 characters.';
    elseif ($n1 !== $n2) $err = 'The two new passwords are not the same.';
    elseif ($n1 === $cur) $err = 'Pick a password that is different from the old one.';
    else {
        q('UPDATE users SET password_hash = ?, must_change = 0 WHERE id = ?', [password_hash($n1, PASSWORD_DEFAULT), $u['id']]);
        session_regenerate_id(true);
        flash('Your new password is saved.');
        redirect('home');
    }
}
render('password', ['title' => 'New password', 'err' => $err, 'forced' => (bool)$u['must_change']]);
