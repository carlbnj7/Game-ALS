<?php
$u = need();
if ($u['role'] === 'learner') {
    $pm = progress_map((int)$u['id']);
    $grade = $u['grade'] ?: 'ELEM';
    $counts = [];
    foreach (all('SELECT strand, COUNT(*) n FROM items WHERE grade = ? AND active = 1 GROUP BY strand', [$grade]) as $r) $counts[$r['strand']] = (int)$r['n'];
    $playing = one('SELECT * FROM attempts WHERE user_id = ? AND status = "playing" ORDER BY id DESC LIMIT 1', [$u['id']]);
    // next thing to do: first open, not passed activity
    $next = null;
    foreach (STRANDS as $s => $_) { if (!strand_open($pm, $s)) break; for ($l = 1; $l <= 3 && !$next; $l++) { if (!level_open($pm, $s, $l)) break; for ($a = 1; $a <= 3; $a++) if (empty($pm["$s|$l|$a"]['passed'])) { $next = [$s, $l, $a]; break; } } if ($next) break; }
    render('learner_home', [
        'title' => 'Home', 'pm' => $pm, 'counts' => $counts, 'playing' => $playing, 'next' => $next,
        'points' => points_of((int)$u['id']),
        'certs' => (int)val('SELECT COUNT(*) FROM certificates WHERE user_id = ?', [$u['id']]),
        'passed' => (int)val('SELECT COUNT(*) FROM progress WHERE user_id = ? AND passed = 1', [$u['id']]),
        'clc' => $u['clc_id'] ? one('SELECT * FROM clcs WHERE id = ?', [$u['clc_id']]) : null,
    ]);
} elseif ($u['role'] === 'teacher') {
    $ids = teacher_clc_ids((int)$u['id']);
    $clcs = $ids ? all('SELECT c.*, (SELECT COUNT(*) FROM users l WHERE l.clc_id = c.id AND l.role = "learner" AND l.active = 1) learners FROM clcs c WHERE c.id IN (' . implode(',', $ids) . ') ORDER BY name') : [];
    $in = $ids ? implode(',', $ids) : '0';
    render('teacher_home', [
        'title' => 'Home', 'clcs' => $clcs,
        'learners' => (int)val("SELECT COUNT(*) FROM users WHERE role = 'learner' AND active = 1 AND clc_id IN ($in)"),
        'passedToday' => (int)val("SELECT COUNT(*) FROM attempts a JOIN users l ON l.id = a.user_id WHERE l.clc_id IN ($in) AND a.status = 'passed' AND a.finished_at >= CURDATE()"),
        'certs' => (int)val("SELECT COUNT(*) FROM certificates c JOIN users l ON l.id = c.user_id WHERE l.clc_id IN ($in)"),
        'recent' => all("SELECT a.*, l.first_name, l.last_name, l.middle_name FROM attempts a JOIN users l ON l.id = a.user_id WHERE l.clc_id IN ($in) AND a.status IN ('passed','failed','lost') ORDER BY a.finished_at DESC LIMIT 8"),
    ]);
} else {
    render('admin_home', [
        'title' => 'Home',
        'n' => [
            'teachers' => (int)val("SELECT COUNT(*) FROM users WHERE role = 'teacher' AND active = 1"),
            'learners' => (int)val("SELECT COUNT(*) FROM users WHERE role = 'learner' AND active = 1"),
            'clcs' => (int)val('SELECT COUNT(*) FROM clcs'),
            'items' => (int)val('SELECT COUNT(*) FROM items WHERE active = 1'),
            'hidden' => (int)val('SELECT COUNT(*) FROM items WHERE active = 0'),
            'certs' => (int)val('SELECT COUNT(*) FROM certificates'),
        ],
        'byStrand' => all("SELECT p.strand, COUNT(*) tries, SUM(p.passed) passed, ROUND(AVG(p.best_score)) avg FROM progress p GROUP BY p.strand"),
    ]);
}
