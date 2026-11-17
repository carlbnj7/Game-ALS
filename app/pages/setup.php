<?php
$u = need();
$needPw = (bool)$u['must_change'];
$needPin = !$u['setup_done'] || $u['pin_hash'] === null;
if (!$needPw && !$needPin) redirect('home');
$err = '';
$oldUn = preg_match('/^(ALST|ALS|ADM|TCH)\d/i', $u['username']) ? '' : $u['username'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $un = strtolower(trim((string)($_POST['username'] ?? '')));
    $cur = (string)($_POST['current'] ?? ''); $n1 = (string)($_POST['new1'] ?? ''); $n2 = (string)($_POST['new2'] ?? '');
    $p1 = (string)($_POST['pin1'] ?? ''); $p2 = (string)($_POST['pin2'] ?? '');
    $oldUn = $un;
    if (!password_verify($cur, $u['password_hash'])) $err = $needPw ? 'The password you were given is wrong.' : 'Your password is wrong.';
    elseif ($needPin && ($e = username_problem($un, (int)$u['id'])) !== '') $err = $e;
    elseif ($needPw && mb_strlen($n1) < 8) $err = 'Your new password needs at least 8 characters.';
    elseif ($needPw && $n1 !== $n2) $err = 'The two new passwords are not the same.';
    elseif ($needPw && $n1 === $cur) $err = 'Pick a new password that is different from the one you were given.';
    elseif ($needPin && !pin_ok($p1)) $err = 'Your PIN must be 4 numbers. Do not use 0000, 1111 or 1234.';
    elseif ($needPin && $p1 !== $p2) $err = 'The two PINs are not the same.';
    else {
        if ($needPw) q('UPDATE users SET password_hash = ?, must_change = 0 WHERE id = ?', [password_hash($n1, PASSWORD_DEFAULT), $u['id']]);
        if ($needPin) q('UPDATE users SET username = ?, pin_hash = ?, pin_fails = 0, setup_done = 1 WHERE id = ?', [$un, password_hash($p1, PASSWORD_DEFAULT), $u['id']]);
        session_regenerate_id(true);
        flash('All set! Sign in next time with your username ' . ($needPin ? $un : $u['username']) . ' or your ALS ID ' . $u['als_id'] . '.');
        redirect('home');
    }
}
render('setup', ['title' => 'Set up your account', 'err' => $err, 'needPw' => $needPw, 'needPin' => $needPin, 'oldUn' => $oldUn]);
