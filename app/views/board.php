<?php $u = me(); ?>
<div class="page-head"><div><h1>Leaderboard</h1><p class="muted"><?= e($scope) ?> · 10 points per correct answer (best try) + 50 per activity passed</p></div></div>
<?php if ($u['role'] !== 'learner'): ?>
<form class="filters" method="get">
  <input type="hidden" name="p" value="board">
  <?php if ($u['role'] === 'admin'): ?>
  <?php require ROOT . '/app/views/_places.php'; ?>
  <?php endif; ?>
  <select name="clc"><option value="0">All CLCs</option><?php foreach ($clcs as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $f['clc'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select>
  <select name="grade"><option value="">Elem and JHS</option><?php foreach (GRADES as $k => $g): ?><option value="<?= $k ?>" <?= $f['grade'] === $k ? 'selected' : '' ?>><?= e($g) ?></option><?php endforeach; ?></select>
  <button class="btn">Show</button>
</form>
<?php endif; ?>
<?php if (!$rows): ?><div class="card empty"><?= icon('trophy', 28) ?><p>No scores yet. Points show up after the first activity is finished.</p></div>
<?php else: ?>
<ol class="board">
<?php foreach ($rows as $i => $r): $mine = $u['id'] == $r['id']; ?>
  <li class="<?= $i < 3 ? 'top top' . ($i + 1) : '' ?> <?= $mine ? 'me' : '' ?>">
    <span class="rank"><?= $i + 1 ?></span>
    <span class="av"><?= e(mb_strtoupper(mb_substr($r['first_name'], 0, 1))) ?></span>
    <span class="grow"><b><?= e(full_name($r)) ?><?= $mine ? ' (you)' : '' ?></b><small><?= e(($r['clc'] ?? '') . ($r['municipality'] ? ' · ' . $r['municipality'] : '')) ?> · <?= (int)$r['passed'] ?> passed · <?= (int)$r['certs'] ?> cert<?= $r['certs'] == 1 ? '' : 's' ?></small></span>
    <span class="pts"><?= number_format((int)$r['pts']) ?><small>pts</small></span>
  </li>
<?php endforeach; ?>
</ol>
<?php endif; ?>
