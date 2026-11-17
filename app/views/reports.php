<div class="page-head"><div><h1>Mastery reports</h1><p class="muted">Mastered = average best score of 75% or more. Least mastered = below 75%.</p></div>
<div class="row-btns">
  <a class="btn" href="<?= url('reports', ['clc' => $f['clc'], 'grade' => $f['grade'], 'csv' => 'summary']) ?>"><?= icon('download', 16) ?> Consolidated (CSV)</a>
  <a class="btn" href="<?= url('reports', ['clc' => $f['clc'], 'grade' => $f['grade'], 'csv' => 'learners']) ?>"><?= icon('download', 16) ?> Per learner (CSV)</a>
</div></div>
<form class="filters" method="get"><input type="hidden" name="p" value="reports">
  <?php if (me()['role'] === 'admin') require ROOT . '/app/views/_places.php'; ?>
  <select name="clc"><option value="0"><?= me()['role'] === 'admin' ? 'All CLCs' : 'All my CLCs' ?></option><?php foreach ($clcs as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $f['clc'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select>
  <select name="grade"><?php foreach (GRADES as $k => $g): ?><option value="<?= $k ?>" <?= $f['grade'] === $k ? 'selected' : '' ?>><?= e($g) ?></option><?php endforeach; ?></select>
  <button class="btn">Show</button>
</form>
<div class="card scroll-x">
<h3>Consolidated · <?= count($learners) ?> learner<?= count($learners) == 1 ? '' : 's' ?></h3>
<table class="tbl grid-tbl"><thead><tr><th>Strand</th><?php for ($lv = 1; $lv <= 3; $lv++) for ($a = 1; $a <= 3; $a++): ?><th>L<?= $lv ?>·A<?= $a ?></th><?php endfor; ?></tr></thead><tbody>
<?php foreach (STRANDS as $s => $st): ?><tr><td><span class="pill c-<?= e(strtolower($s)) ?>"><?= e($st[0]) ?></span> <?= e($st[1]) ?></td>
<?php for ($lv = 1; $lv <= 3; $lv++) for ($a = 1; $a <= 3; $a++): $r = $summary["$s|$lv|$a"]; ?>
<td class="sc <?= $r['avg'] === null ? '' : ($r['avg'] >= 75 ? 'ok' : 'bad') ?>" title="<?= $r['tried'] ?> tried, <?= $r['passed'] ?> passed"><?= $r['avg'] === null ? '·' : (int)round($r['avg']) . '%' ?></td>
<?php endfor; ?></tr><?php endforeach; ?>
</tbody></table>
<p class="muted small">Each cell is the average best score of learners who tried that activity.</p>
</div>
<div class="card scroll-x"><h3>Per learner</h3>
<table class="tbl"><thead><tr><th>Learner</th><th>CLC</th><?php foreach (STRANDS as $s => $st): ?><th><?= e($st[0] . ' ' . $st[1]) ?></th><?php endforeach; ?><th>Mastered</th></tr></thead><tbody>
<?php foreach ($learners as $L): $m = 0; ?>
<tr><td><a href="<?= url('learner', ['id' => $L['id']]) ?>"><?= e($L['last_name'] . ', ' . $L['first_name']) ?></a></td><td class="small"><?= e($L['clc']) ?></td>
<?php foreach (STRANDS as $s => $st): $n = 0; for ($lv = 1; $lv <= 3; $lv++) for ($a = 1; $a <= 3; $a++) if (!empty($prog[$L['id']]["$s|$lv|$a"]['passed'])) $n++; $m += $n; ?>
<td><span class="mini-bar"><progress max="9" value="<?= $n ?>"></progress><small><?= $n ?>/9</small></span></td>
<?php endforeach; ?><td><b><?= $m ?></b>/63</td></tr>
<?php endforeach; ?>
<?php if (!$learners): ?><tr><td colspan="10" class="muted">No learners here.</td></tr><?php endif; ?>
</tbody></table></div>
