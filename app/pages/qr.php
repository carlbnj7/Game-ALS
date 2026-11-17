<?php
// Sign in with a QR pass: scan the pass, then type the 4-digit PIN.
$t = preg_replace('/[^a-f0-9]/', '', strtolower((string)($_GET['t'] ?? $_POST['t'] ?? '')));
if ($t === '') { render('qr_scan', ['title' => 'Scan your QR pass', 'bare' => true, 'bodyClass' => 'auth']); return; }
$who = strlen($t) === 40 ? one('SELECT * FROM users WHERE qr_token = ? AND active = 1', [$t]) : null;
if (!$who) { render('qr_pin', ['title' => 'QR pass', 'bare' => true, 'bodyClass' => 'auth', 'who' => null, 'err' => '', 't' => '']); return; }
// A shared computer: a different person was still signed in.
$cur = me();
if ($cur && (int)$cur['id'] !== (int)$who['id']) { unset($_SESSION['uid']); session_regenerate_id(true); }
elseif ($cur) redirect('home');
$usePin = $who['setup_done'] && $who['pin_hash'] !== null;
$err = '';
$locked = (int)$who['pin_fails'] >= 5;
if ($locked) $err = 'This pass is locked after 5 wrong tries. Sign in with your password, or ask your teacher.';
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    q('DELETE FROM login_tries WHERE tried_at < NOW() - INTERVAL 1 DAY');
    $tries = (int)val('SELECT COUNT(*) FROM login_tries WHERE ip = ? AND tried_at > NOW() - INTERVAL 15 MINUTE', [$ip]);
    $code = (string)($_POST['code'] ?? '');
    if ($tries >= 10) $err = 'Too many tries. Please wait 15 minutes and try again.';
    elseif (password_verify($code, (string)($usePin ? $who['pin_hash'] : $who['password_hash']))) {
        session_regenerate_id(true);
        $_SESSION['uid'] = (int)$who['id'];
        q('UPDATE users SET last_login = NOW(), pin_fails = 0 WHERE id = ?', [$who['id']]);
        q('DELETE FROM login_tries WHERE ip = ?', [$ip]);
        redirect('home');
    } else {
        q('INSERT INTO login_tries (ip) VALUES (?)', [$ip]);
        q('UPDATE users SET pin_fails = pin_fails + 1 WHERE id = ?', [$who['id']]);
        $left = 4 - (int)$who['pin_fails'];
        $err = ($usePin ? 'Wrong PIN.' : 'Wrong password.') . ($left > 0 ? ' ' . $left . ' tr' . ($left === 1 ? 'y' : 'ies') . ' left.' : ' This pass is now locked.');
        $locked = $left <= 0;
    }
}
render('qr_pin', ['title' => 'Type your PIN', 'bare' => true, 'bodyClass' => 'auth', 'who' => $who, 'err' => $err, 't' => $t, 'usePin' => $usePin, 'locked' => $locked]);
