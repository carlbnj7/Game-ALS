<?php
$u = need('admin');
$it = one('SELECT * FROM items WHERE id = ?', [(int)($_GET['id'] ?? 0)]);
if (!$it) { render('error', ['title' => 'Not found', 'msg' => 'That question does not exist.']); return; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ans = strtoupper((string)($_POST['answer'] ?? ''));
    if (!in_array($ans, ['A', 'B', 'C', 'D'], true)) flash('Choose the correct answer A, B, C, or D.', 'err');
    else {
        $p = trim((string)$_POST['passage']);
        q('UPDATE items SET passage = ?, question = ?, opt_a = ?, opt_b = ?, opt_c = ?, opt_d = ?, answer = ?, explanation = ?, active = ?, image_only = ?, updated_by = ?, updated_at = NOW() WHERE id = ?', [
            $p === '' ? null : $p, trim((string)$_POST['question']), trim((string)$_POST['opt_a']), trim((string)$_POST['opt_b']), trim((string)$_POST['opt_c']), trim((string)$_POST['opt_d']),
            $ans, trim((string)$_POST['explanation']), isset($_POST['active']) ? 1 : 0, isset($_POST['image_only']) && $it['image'] ? 1 : 0, $u['id'], $it['id']]);
        flash('Question saved. Learners who start this activity from now on get the change.');
    }
    redirect('question', ['id' => $it['id']]);
}
$prev = val('SELECT id FROM items WHERE grade = ? AND strand = ? AND level = ? AND num < ? ORDER BY num DESC LIMIT 1', [$it['grade'], $it['strand'], $it['level'], $it['num']]);
$next = val('SELECT id FROM items WHERE grade = ? AND strand = ? AND level = ? AND num > ? ORDER BY num LIMIT 1', [$it['grade'], $it['strand'], $it['level'], $it['num']]);
render('question', ['title' => 'Edit question', 'it' => $it, 'prev' => $prev, 'next' => $next]);
