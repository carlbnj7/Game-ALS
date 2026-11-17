<?php
$u = need('teacher', 'admin');
require ROOT . '/app/xlsx.php';
$clcs = my_clcs($u); $ids = array_map('intval', array_column($clcs, 'id'));
$result = null; $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clc = (int)($_POST['clc_id'] ?? 0); $defGrade = isset(GRADES[$_POST['grade'] ?? '']) ? $_POST['grade'] : 'ELEM';
    $f = $_FILES['file'] ?? null;
    if (!in_array($clc, $ids, true)) $err = 'Choose one of your CLCs.';
    elseif (!$f || $f['error'] !== UPLOAD_ERR_OK) $err = 'Choose a file to upload.';
    elseif ($f['size'] > 5 * 1024 * 1024) $err = 'The file is too big (5 MB at most).';
    else {
        try {
            $rows = read_table($f['tmp_name'], $f['name']);
            [$hi, $map] = map_columns($rows);
            if ($hi < 0) throw new RuntimeException('No header row was found. The file needs columns like LRN, Last Name, First Name, Middle Name, Sex, Birthdate.');
            $made = []; $skipped = [];
            foreach (array_slice($rows, $hi + 1) as $n => $r) {
                $get = fn($k) => isset($map[$k]) ? trim((string)($r[$map[$k]] ?? '')) : '';
                $last = $get('last'); $first = $get('first'); $mid = $get('middle');
                if ($last === '' && $get('name') !== '') {
                    $nm = $get('name');
                    if (str_contains($nm, ',')) { [$last, $rest] = array_map('trim', explode(',', $nm, 2)); $parts = preg_split('/\s+/', $rest); if (count($parts) > 1 && preg_match('/^[A-Z]\.?$/i', end($parts))) { $mid = array_pop($parts); } $first = implode(' ', $parts); }
                    else { $parts = preg_split('/\s+/', $nm); $last = array_pop($parts); $first = implode(' ', $parts); }
                }
                if ($last === '' || $first === '' || preg_match('/^(total|male|female|prepared|noted|\d+)$/i', $last)) continue;
                $lrn = preg_replace('/\D/', '', $get('lrn'));
                $sx = strtoupper(substr($get('sex'), 0, 1)); $sx = in_array($sx, ['M', 'F'], true) ? $sx : '';
                $gv = $get('grade'); $grade = $gv === '' ? $defGrade : (preg_match('/jhs|junior|secondary|\b(7|8|9|10)\b/i', $gv) ? 'JHS' : (preg_match('/elem|primary|basic|\b[1-6]\b/i', $gv) ? 'ELEM' : $defGrade));
                $d = ['first_name' => proper($first), 'middle_name' => proper($mid), 'last_name' => proper($last), 'sex' => $sx, 'birthdate' => excel_date($get('birth')), 'lrn' => $lrn, 'grade' => $grade, 'clc_id' => $clc];
                $dup = $lrn !== '' ? val('SELECT 1 FROM users WHERE lrn = ?', [$lrn]) : val('SELECT 1 FROM users WHERE role = "learner" AND clc_id = ? AND first_name = ? AND last_name = ?', [$clc, $d['first_name'], $d['last_name']]);
                if ($dup) { $skipped[] = full_name($d) . ' (already has an account)'; continue; }
                [$un, $pw] = add_learner($u, $d);
                $made[] = [full_name($d), $un, $pw, $grade];
            }
            $_SESSION['new_accounts'] = array_merge($_SESSION['new_accounts'] ?? [], $made);
            $result = ['made' => $made, 'skipped' => $skipped, 'cols' => array_keys($map)];
        } catch (Throwable $ex) { $err = $ex->getMessage(); }
    }
}
render('import', ['title' => 'Upload learners', 'clcs' => $clcs, 'err' => $err, 'result' => $result]);
