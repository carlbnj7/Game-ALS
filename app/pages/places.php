<?php
// Admin: municipalities and their districts. CLCs pick a district from these lists.
need('admin');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $do = $_POST['do'] ?? ''; $name = proper((string)($_POST['name'] ?? '')); $id = (int)($_POST['id'] ?? 0);
    $name = preg_replace_callback('/\b(Ii|Iii|Iv|Vi|Vii|Viii|Ix|Xi|Xii)\b/', fn($m) => strtoupper($m[0]), $name);
    try {
        if ($do === 'add_mun' && $name !== '') { q('INSERT INTO municipalities (name) VALUES (?)', [$name]); flash($name . ' added. Now add its districts.'); }
        elseif ($do === 'ren_mun' && $name !== '') { q('UPDATE municipalities SET name = ? WHERE id = ?', [$name, $id]); sync_clc_places(); flash('Municipality renamed.'); }
        elseif ($do === 'del_mun') {
            if (val('SELECT COUNT(*) FROM districts WHERE municipality_id = ?', [$id])) flash('Remove its districts first.', 'err');
            else { q('DELETE FROM municipalities WHERE id = ?', [$id]); flash('Municipality removed.'); }
        }
        elseif ($do === 'add_dist' && $name !== '' && val('SELECT 1 FROM municipalities WHERE id = ?', [(int)$_POST['mun']])) { q('INSERT INTO districts (municipality_id, name) VALUES (?, ?)', [(int)$_POST['mun'], $name]); flash('District added.'); }
        elseif ($do === 'ren_dist' && $name !== '') { q('UPDATE districts SET name = ? WHERE id = ?', [$name, $id]); sync_clc_places(); flash('District renamed.'); }
        elseif ($do === 'del_dist') {
            if (val('SELECT COUNT(*) FROM clcs WHERE district_id = ?', [$id])) flash('Some CLCs are in this district. Move them first.', 'err');
            else { q('DELETE FROM districts WHERE id = ?', [$id]); flash('District removed.'); }
        }
    } catch (PDOException $ex) {
        if ($ex->getCode() !== '23000') throw $ex;
        flash('That name is already on the list.', 'err');
    }
    redirect('places');
}
$muns = all('SELECT m.*, (SELECT COUNT(*) FROM clcs c JOIN districts d ON d.id = c.district_id WHERE d.municipality_id = m.id) clcs,
  (SELECT COUNT(*) FROM users l JOIN clcs c ON c.id = l.clc_id JOIN districts d ON d.id = c.district_id WHERE d.municipality_id = m.id AND l.role = "learner" AND l.active = 1) learners
  FROM municipalities m ORDER BY m.name');
$dists = [];
foreach (all('SELECT d.*, (SELECT COUNT(*) FROM clcs c WHERE c.district_id = d.id) clcs,
  (SELECT COUNT(*) FROM users l JOIN clcs c ON c.id = l.clc_id WHERE c.district_id = d.id AND l.role = "learner" AND l.active = 1) learners
  FROM districts d ORDER BY d.name') as $d) $dists[$d['municipality_id']][] = $d;
render('places', ['title' => 'Municipalities', 'muns' => $muns, 'dists' => $dists]);
