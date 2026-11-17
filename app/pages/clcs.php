<?php
need('admin');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $do = $_POST['do'] ?? '';
    $name = trim((string)($_POST['name'] ?? '')); $dist = (int)($_POST['district_id'] ?? 0);
    $dist = $dist && val('SELECT 1 FROM districts WHERE id = ?', [$dist]) ? $dist : null;
    if ($do === 'add' && $name !== '') { q('INSERT INTO clcs (name, district_id) VALUES (?, ?)', [$name, $dist]); sync_clc_places(); flash('CLC added.'); }
    elseif ($do === 'save' && $name !== '') { q('UPDATE clcs SET name = ?, district_id = ? WHERE id = ?', [$name, $dist, (int)$_POST['id']]); sync_clc_places(); flash('CLC saved.'); }
    elseif ($do === 'del') {
        $id = (int)$_POST['id'];
        if (val('SELECT COUNT(*) FROM users WHERE clc_id = ?', [$id])) flash('This CLC still has learners. Move them first.', 'err');
        else { q('DELETE FROM teacher_clc WHERE clc_id = ?', [$id]); q('DELETE FROM clcs WHERE id = ?', [$id]); flash('CLC removed.'); }
    }
    redirect('clcs', array_filter(['mun' => (int)($_POST['mun'] ?? 0), 'dist' => (int)($_POST['fdist'] ?? 0)]));
}
$f = place_filter(); $where = ['1']; $args = [];
place_where($f, $where, $args, 'c');
$rows = all('SELECT c.*, (SELECT COUNT(*) FROM users WHERE clc_id = c.id AND role = "learner") learners,
  (SELECT GROUP_CONCAT(CONCAT(u.first_name, " ", u.last_name) SEPARATOR ", ") FROM teacher_clc t JOIN users u ON u.id = t.teacher_id WHERE t.clc_id = c.id) teachers
  FROM clcs c WHERE ' . implode(' AND ', $where) . ' ORDER BY municipality, district, name', $args);
render('clcs', ['title' => 'CLCs', 'rows' => $rows, 'f' => $f, 'muns' => municipalities(), 'dists' => districts(), 'scripts' => ['places.js']]);
