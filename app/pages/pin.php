<?php
// Change your own username or 4-digit PIN. Needs your password.
$u = need();
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $un = strtolower(trim((string)($_POST['username'] ?? ''))); $p1 = (string)($_POST['pin1'] ?? ''); $p2 = (string)($_POST['pin2'] ?? '');
    if (!password_verify((string)($_POST['current'] ?? ''), $u['password_hash'])) $err = 'Your password is wrong.';
    elseif ($un !== $u['username'] && ($e = username_problem($un, (int)$u['id'])) !== '') $err = $e;
    elseif ($p1 !== '' && !pin_ok($p1)) $err = 'Your PIN must be 4 numbers. Do not use 0000, 1111 or 1234.';
    elseif ($p1 !== $p2) $err = 'The two PINs are not the same.';
    else {
        q('UPDATE users SET username = ? WHERE id = ?', [$un, $u['id']]);
        if ($p1 !== '') q('UPDATE users SET pin_hash = ?, pin_fails = 0 WHERE id = ?', [password_hash($p1, PASSWORD_DEFAULT), $u['id']]);
        flash('Saved.' . ($p1 !== '' ? ' Your new PIN works on your QR pass now.' : ''));
        redirect('account');
    }
}
render('pin', ['title' => 'Username and PIN', 'err' => $err]);
