<?php
// Admin: teacher and admin accounts. ALS IDs and starter passwords are made by the app.
$me = need('admin');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $do = $_POST['do'] ?? '';
    if ($do === 'add') {
        $role = ($_POST['role'] ?? '') === 'admin' ? 'admin' : 'teacher';
        $d = ['first_name' => proper((string)$_POST['first_name']), 'middle_name' => proper((string)($_POST['middle_name'] ?? '')), 'last_name' => proper((string)$_POST['last_name']), 'email' => trim((string)($_POST['email'] ?? ''))];
        if ($d['first_name'] === '' || $d['last_name'] === '') flash('Type the first and last name.', 'err');
        else {
            [$tid, $aid, $pw] = add_staff($me, $role, $d);
            if ($role === 'teacher') foreach ((array)($_POST['clcs'] ?? []) as $c) q('INSERT IGNORE INTO teacher_clc VALUES (?, ?)', [$tid, (int)$c]);
            $_SESSION['shown_pw'] = [full_name($d), $aid, $pw, $role];
            flash(($role === 'admin' ? 'Admin' : 'Teacher') . ' account made. Give them the ALS ID and password below.');
        }
        redirect('teachers');
    }
    $t = one('SELECT * FROM users WHERE id = ? AND role IN ("teacher", "admin")', [(int)($_POST['id'] ?? 0)]);
    if ($t) {
        $self = (int)$t['id'] === (int)$me['id'];
        if ($do === 'clcs' && $t['role'] === 'teacher') {
            q('DELETE FROM teacher_clc WHERE teacher_id = ?', [$t['id']]);
            foreach ((array)($_POST['clcs'] ?? []) as $c) q('INSERT IGNORE INTO teacher_clc VALUES (?, ?)', [$t['id'], (int)$c]);
            flash('CLCs updated for ' . full_name($t) . '.');
        } elseif ($do === 'reset' && !$self) {
            $pw = make_password();
            q('UPDATE users SET password_hash = ?, must_change = 1, pin_fails = 0 WHERE id = ?', [password_hash($pw, PASSWORD_DEFAULT), $t['id']]);
            $_SESSION['shown_pw'] = [full_name($t), $t['als_id'], $pw, $t['role']];
            flash('New password made for ' . full_name($t) . '.');
        } elseif ($do === 'pin' && !$self) {
            q('UPDATE users SET pin_hash = NULL, pin_fails = 0 WHERE id = ?', [$t['id']]);
            flash(full_name($t) . ' will make a new PIN on their next sign-in.');
        } elseif ($do === 'toggle' && !$self) {
            q('UPDATE users SET active = 1 - active WHERE id = ?', [$t['id']]);
            flash(full_name($t) . ($t['active'] ? ' can no longer sign in.' : ' can sign in again.'));
        }
    }
    redirect('teachers');
}
$shown = $_SESSION['shown_pw'] ?? null; unset($_SESSION['shown_pw']);
$f = place_filter() + ['role' => in_array($_GET['role'] ?? '', ['teacher', 'admin'], true) ? $_GET['role'] : ''];
$where = ['u.role IN ("teacher", "admin")']; $args = [];
if ($f['role']) { $where[] = 'u.role = ?'; $args[] = $f['role']; }
$w2 = []; $a2 = []; place_where($f, $w2, $a2, 'c');
if ($w2) { $where[] = 'u.id IN (SELECT t.teacher_id FROM teacher_clc t JOIN clcs c ON c.id = t.clc_id WHERE ' . implode(' AND ', $w2) . ')'; $args = array_merge($args, $a2); }
$teachers = all('SELECT u.*, (SELECT GROUP_CONCAT(clc_id) FROM teacher_clc WHERE teacher_id = u.id) clc_ids FROM users u WHERE ' . implode(' AND ', $where) . ' ORDER BY u.role DESC, u.active DESC, u.last_name, u.first_name', $args);
render('teachers', ['title' => 'Teachers and admins', 'teachers' => $teachers, 'clcs' => all('SELECT * FROM clcs ORDER BY name'), 'shown' => $shown, 'me' => $me,
  'f' => $f, 'muns' => municipalities(), 'dists' => districts(), 'scripts' => ['places.js']]);
