<div class="page-head"><div><h1>Questions</h1><p class="muted">Fix a question, its choices, or its answer here. Hidden questions are not shown to learners.</p></div></div>
<form class="filters" method="get"><input type="hidden" name="p" value="questions">
  <select name="grade"><?php foreach (GRADES as $k => $g): ?><option value="<?= $k ?>" <?= $f['grade'] === $k ? 'selected' : '' ?>><?= e($g) ?></option><?php endforeach; ?></select>
  <select name="s"><?php foreach (STRANDS as $k => $st): ?><option value="<?= $k ?>" <?= $f['s'] === $k ? 'selected' : '' ?>><?= e($st[0] . ' ' . $st[1]) ?></option><?php endforeach; ?></select>
  <select name="l"><?php foreach (LEVELS as $k => $n): ?><option value="<?= $k ?>" <?= $f['l'] === $k ? 'selected' : '' ?>>Level <?= $k ?> · <?= $n ?></option><?php endforeach; ?></select>
  <select name="a"><option value="0">All activities</option><?php for ($a = 1; $a <= 3; $a++): ?><option value="<?= $a ?>" <?= $f['a'] === $a ? 'selected' : '' ?>>Activity <?= $a ?> (<?= activity_range($a) ?>)</option><?php endfor; ?></select>
  <select name="show"><option value="">All</option><option value="hidden" <?= $f['show'] === 'hidden' ? 'selected' : '' ?>>Hidden only</option></select>
  <input type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Search text">
  <button class="btn">Show</button>
</form>
<div class="card"><table class="tbl">
<thead><tr><th>#</th><th>Question</th><th>Key</th><th>Right so far</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $r): ?>
<tr class="<?= $r['active'] ? '' : 'off' ?>"><td><?= (int)$r['num'] ?></td>
<td><?= e(mb_strimwidth($r['question'], 0, 150, '…')) ?> <?= $r['image'] ? '<span class="pill">Picture</span>' : '' ?><?= $r['has_p'] ? '<span class="pill">Passage</span>' : '' ?><?= $r['active'] ? '' : '<span class="pill warn">Hidden</span>' ?></td>
<td><b><?= e($r['answer']) ?></b></td><td class="small"><?= $r['tries'] ? (int)$r['pct'] . '% of ' . (int)$r['tries'] : '—' ?></td>
<td><a class="btn sm" href="<?= url('question', ['id' => $r['id']]) ?>">Edit</a></td></tr>
<?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="5" class="muted">No questions match.</td></tr><?php endif; ?>
</tbody></table></div>
