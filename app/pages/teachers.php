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
            $dist = (int)($_POST['district_id'] ?? 0);
            if ($dist && val('SELECT 1 FROM districts WHERE id = ?', [$dist])) q('UPDATE users SET district_id = ? WHERE id = ?', [$dist, $tid]);
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
        } elseif ($do === 'district') {
            $dist = (int)($_POST['district_id'] ?? 0);
            q('UPDATE users SET district_id = ? WHERE id = ?', [$dist && val('SELECT 1 FROM districts WHERE id = ?', [$dist]) ? $dist : null, $t['id']]);
            flash('District saved for ' . full_name($t) . '.');
        } elseif ($do === 'reset' && !$self) {
            $pw = make_password();
            q('UPDATE users SET password_hash = ?, starter_pw = ?, must_change = 1, pin_fails = 0 WHERE id = ?', [password_hash($pw, PASSWORD_DEFAULT), $pw, $t['id']]);
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
if (!empty($f['dist'])) { $where[] = '(u.district_id = ? OR u.id IN (SELECT t.teacher_id FROM teacher_clc t JOIN clcs c ON c.id = t.clc_id WHERE c.district_id = ?))'; array_push($args, $f['dist'], $f['dist']); $w2 = []; }
elseif (!empty($f['mun'])) { $where[] = '(u.district_id IN (SELECT id FROM districts WHERE municipality_id = ?) OR u.id IN (SELECT t.teacher_id FROM teacher_clc t JOIN clcs c ON c.id = t.clc_id JOIN districts d ON d.id = c.district_id WHERE d.municipality_id = ?))'; array_push($args, $f['mun'], $f['mun']); $w2 = []; }
if ($w2) { $where[] = 'u.id IN (SELECT t.teacher_id FROM teacher_clc t JOIN clcs c ON c.id = t.clc_id WHERE ' . implode(' AND ', $w2) . ')'; $args = array_merge($args, $a2); }
$teachers = all('SELECT u.*, (SELECT GROUP_CONCAT(clc_id) FROM teacher_clc WHERE teacher_id = u.id) clc_ids, (SELECT CONCAT(d.name, " · ", m.name) FROM districts d JOIN municipalities m ON m.id = d.municipality_id WHERE d.id = u.district_id) district FROM users u WHERE ' . implode(' AND ', $where) . ' ORDER BY u.role DESC, u.active DESC, u.last_name, u.first_name', $args);
render('teachers', ['title' => 'Teachers and admins', 'teachers' => $teachers, 'clcs' => all('SELECT * FROM clcs ORDER BY name'), 'shown' => $shown, 'me' => $me,
  'f' => $f, 'muns' => municipalities(), 'dists' => districts(), 'scripts' => ['places.js']]);
