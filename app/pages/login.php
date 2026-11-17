<?php
if (me()) redirect('home');
$err = '';
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    q('DELETE FROM login_tries WHERE tried_at < NOW() - INTERVAL 1 DAY');
    $tries = (int)val('SELECT COUNT(*) FROM login_tries WHERE ip = ? AND tried_at > NOW() - INTERVAL 15 MINUTE', [$ip]);
    $user = trim((string)($_POST['username'] ?? ''));
    $pass = (string)($_POST['password'] ?? '');
    if ($tries >= 10) {
        $err = 'Too many tries. Please wait 15 minutes and try again.';
    } else {
        $u = one('SELECT * FROM users WHERE (username = ? OR als_id = ?) AND active = 1', [$user, $user]);
        if ($u && password_verify($pass, $u['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['uid'] = (int)$u['id'];
            q('UPDATE users SET last_login = NOW(), pin_fails = 0 WHERE id = ?', [$u['id']]);
            q('DELETE FROM login_tries WHERE ip = ?', [$ip]);
            if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($pass, PASSWORD_DEFAULT), $u['id']]);
            redirect('home');
        }
        q('INSERT INTO login_tries (ip) VALUES (?)', [$ip]);
        $err = 'Wrong ALS ID, username or password.';
    }
}
render('login', ['title' => 'Sign in', 'bare' => true, 'bodyClass' => 'auth', 'err' => $err, 'old' => $_POST['username'] ?? '']);
