<?php
$u = need('teacher', 'admin');
$clcs = my_clcs($u); $ids = array_map('intval', array_column($clcs, 'id')) ?: [0];
$pf = place_filter(); $clc = (int)($_GET['clc'] ?? 0); $grade = isset(GRADES[$_GET['grade'] ?? '']) ? $_GET['grade'] : 'ELEM';
$where = "l.role = 'learner' AND l.active = 1 AND l.grade = ?"; $args = [$grade];
if ($clc && in_array($clc, $ids, true)) { $where .= ' AND l.clc_id = ?'; $args[] = $clc; }
elseif ($u['role'] === 'teacher') $where .= ' AND l.clc_id IN (' . implode(',', $ids) . ')';
if ($u['role'] === 'admin') { $pw = []; place_where($pf, $pw, $args, 'c'); if ($pw) $where .= ' AND ' . implode(' AND ', $pw); }
$learners = all("SELECT l.id, l.first_name, l.middle_name, l.last_name, l.lrn, l.sex, c.name clc FROM users l LEFT JOIN clcs c ON c.id = l.clc_id WHERE $where ORDER BY c.name, l.last_name, l.first_name", $args);
$lids = array_column($learners, 'id') ?: [0];
$prog = [];
foreach (all('SELECT * FROM progress WHERE user_id IN (' . implode(',', array_map('intval', $lids)) . ')') as $p) $prog[$p['user_id']][$p['strand'] . '|' . $p['level'] . '|' . $p['activity']] = $p;
// per activity summary
$summary = [];
foreach (STRANDS as $s => $_) for ($lv = 1; $lv <= 3; $lv++) for ($a = 1; $a <= 3; $a++) {
    $k = "$s|$lv|$a"; $tried = 0; $passed = 0; $sum = 0;
    foreach ($learners as $L) if (isset($prog[$L['id']][$k])) { $tried++; $sum += $prog[$L['id']][$k]['best_score']; $passed += $prog[$L['id']][$k]['passed']; }
    $summary[$k] = ['tried' => $tried, 'passed' => $passed, 'avg' => $tried ? $sum / $tried : null];
}
if (($_GET['csv'] ?? '') !== '') {
    $type = $_GET['csv'];
    header('Content-Type: text/csv; charset=utf-8');
    $cn = $clc ? (array_column($clcs, 'name', 'id')[$clc] ?? 'clc') : 'all-clcs';
    header('Content-Disposition: attachment; filename="game-als-' . ($type === 'summary' ? 'mastery-summary' : 'learner-mastery') . '-' . preg_replace('/[^a-z0-9]+/i', '-', $cn) . '-' . strtolower($grade) . '-' . date('Y-m-d') . '.csv"');
    $o = fopen('php://output', 'w'); fwrite($o, "\xEF\xBB\xBF");
    if ($type === 'summary') {
        fputcsv($o, ['Strand', 'Level', 'Activity', 'Items', 'Learners who tried', 'Learners who mastered (75%+)', 'Average best score', 'Mastery'], ',', '"', '\\');
        foreach ($summary as $k => $r) { [$s, $lv, $a] = explode('|', $k); fputcsv($o, [STRANDS[$s][0] . ' ' . STRANDS[$s][2], 'Level ' . $lv . ' (' . LEVELS[$lv] . ')', 'Activity ' . $a, activity_range((int)$a), $r['tried'], $r['passed'], $r['avg'] === null ? '' : round($r['avg']) . '%', $r['tried'] ? ($r['passed'] / $r['tried'] >= PASS_MARK ? 'Mastered' : 'Least mastered') : 'Not yet taken'], ',', '"', '\\'); }
    } else {
        $head = ['CLC', 'LRN', 'Last name', 'First name', 'Middle name', 'Sex'];
        foreach (STRANDS as $s => $st) for ($lv = 1; $lv <= 3; $lv++) for ($a = 1; $a <= 3; $a++) $head[] = $st[0] . ' ' . $st[1] . " L$lv A$a";
        array_push($head, 'Activities mastered', 'Least mastered (below 75%)');
        fputcsv($o, $head, ',', '"', '\\');
        foreach ($learners as $L) {
            $row = [$L['clc'], $L['lrn'], $L['last_name'], $L['first_name'], $L['middle_name'], $L['sex']]; $m = 0; $least = [];
            foreach (STRANDS as $s => $st) for ($lv = 1; $lv <= 3; $lv++) for ($a = 1; $a <= 3; $a++) {
                $p = $prog[$L['id']]["$s|$lv|$a"] ?? null;
                $row[] = $p ? round((float)$p['best_score']) . '%' : '';
                if ($p && $p['passed']) $m++; elseif ($p) $least[] = $st[0] . ' ' . $st[1] . " L$lv A$a";
            }
            $row[] = $m; $row[] = implode('; ', $least);
            fputcsv($o, $row, ',', '"', '\\');
        }
    }
    fclose($o); exit;
}
render('reports', ['title' => 'Reports', 'clcs' => $clcs, 'learners' => $learners, 'prog' => $prog, 'summary' => $summary, 'f' => ['clc' => $clc, 'grade' => $grade] + $pf,
  'muns' => $u['role'] === 'admin' ? municipalities() : [], 'dists' => $u['role'] === 'admin' ? districts() : [], 'scripts' => ['places.js']]);
