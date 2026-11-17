<div class="page-head">
  <div><h1>Learners</h1><p class="muted"><?= count($rows) ?> shown</p></div>
  <div class="row-btns"><a class="btn primary" href="<?= url('learner_new') ?>"><?= icon('plus', 16) ?> Add learner</a><a class="btn" href="<?= url('import') ?>"><?= icon('upload', 16) ?> Upload AF3 / CSV</a><a class="btn" href="<?= url('passes') ?>"><?= icon('qr', 16) ?> QR passes</a></div>
</div>
<?php if ($shown): ?><div class="pw-slip"><b><?= count($shown) ?> new account<?= count($shown) == 1 ? '' : 's' ?> made.</b> <a href="<?= url('slips') ?>">Print the sign-in slips</a><small>The passwords show only on the slips page, until you sign out.</small></div><?php endif; ?>
<form class="filters" method="get"><input type="hidden" name="p" value="learners">
  <input type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Search name, ALS ID, username, LRN">
  <?php if (me()['role'] === 'admin') require ROOT . '/app/views/_places.php'; ?>
  <select name="clc"><option value="0">All my CLCs</option><?php foreach ($clcs as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $f['clc'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select>
  <select name="grade"><option value="">Elem and JHS</option><?php foreach (GRADES as $k => $g): ?><option value="<?= $k ?>" <?= $f['grade'] === $k ? 'selected' : '' ?>><?= e($g) ?></option><?php endforeach; ?></select>
  <button class="btn">Show</button>
</form>
<div class="card"><table class="tbl">
<thead><tr><th>Learner</th><th>ALS ID · Username</th><th>Grade</th><th>CLC</th><th>Passed</th><th>Avg best</th><th>Last played</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?>
<tr class="<?= $r['active'] ? '' : 'off' ?>"><td><a href="<?= url('learner', ['id' => $r['id']]) ?>"><b><?= e($r['last_name'] . ', ' . $r['first_name'] . ($r['middle_name'] ? ' ' . mb_substr($r['middle_name'], 0, 1) . '.' : '')) ?></b></a><?= $r['active'] ? '' : ' <span class="pill">Off</span>' ?></td>
<td><code><?= e($r['als_id']) ?></code><br><small class="muted"><?= $r['setup_done'] ? e($r['username']) : 'Not set up yet' ?></small></td><td><?= e($r['grade'] === 'JHS' ? 'JHS' : 'Elem') ?></td><td><?= e($r['clc'] ?? '—') ?></td><td><?= (int)$r['passed'] ?>/63</td><td><?= $r['avg'] !== null ? (int)$r['avg'] . '%' : '—' ?></td><td class="small muted"><?= $r['last_play'] ? e(date('M j', strtotime($r['last_play']))) : '—' ?></td></tr>
<?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="7" class="muted">No learners yet.</td></tr><?php endif; ?>
</tbody></table></div>
