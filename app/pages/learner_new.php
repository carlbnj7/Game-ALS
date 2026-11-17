<?php
$u = need('teacher', 'admin');
$clcs = my_clcs($u); $ids = array_map('intval', array_column($clcs, 'id'));
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = [
        'first_name' => proper((string)$_POST['first_name']), 'middle_name' => proper((string)($_POST['middle_name'] ?? '')), 'last_name' => proper((string)$_POST['last_name']),
        'sex' => in_array($_POST['sex'] ?? '', ['M', 'F'], true) ? $_POST['sex'] : '', 'birthdate' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($_POST['birthdate'] ?? '')) ? $_POST['birthdate'] : null,
        'lrn' => preg_replace('/\D/', '', (string)($_POST['lrn'] ?? '')), 'grade' => isset(GRADES[$_POST['grade'] ?? '']) ? $_POST['grade'] : 'ELEM', 'clc_id' => (int)($_POST['clc_id'] ?? 0),
    ];
    if ($d['first_name'] === '' || $d['last_name'] === '') $err = 'Type the first and last name.';
    elseif (!in_array($d['clc_id'], $ids, true)) $err = 'Choose one of your CLCs.';
    elseif ($d['lrn'] !== '' && val('SELECT 1 FROM users WHERE lrn = ?', [$d['lrn']])) $err = 'A learner with this LRN already has an account.';
    else {
        [$un, $pw] = add_learner($u, $d);
        $_SESSION['new_accounts'][] = [full_name($d), $un, $pw, $d['grade']];
        flash('Account made for ' . full_name($d) . '.');
        redirect(isset($_POST['again']) ? 'learner_new' : 'learners');
    }
}
render('learner_new', ['title' => 'Add learner', 'clcs' => $clcs, 'err' => $err]);
