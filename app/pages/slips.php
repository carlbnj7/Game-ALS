<?php
need('teacher', 'admin');
if ($_SERVER['REQUEST_METHOD'] === 'POST') { unset($_SESSION['new_accounts']); flash('The list of new passwords was cleared.'); redirect('learners'); }
render('slips', ['title' => 'Sign-in slips', 'rows' => $_SESSION['new_accounts'] ?? []]);
