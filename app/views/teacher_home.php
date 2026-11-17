<?php $u = me(); ?>
<section class="hello">
  <div><p class="eyebrow">ALS Teacher</p><h1>Good day, <?= e($u['first_name']) ?>.</h1><p class="muted">Here is how your learners are doing.</p></div>
  <div class="stats3"><div><b><?= $learners ?></b><span>Learners</span></div><div><b><?= $passedToday ?></b><span>Passed today</span></div><div><b><?= $certs ?></b><span>Certificates</span></div></div>
</section>
<div class="quick">
  <a class="qa" href="<?= url('learner_new') ?>"><?= icon('plus', 20) ?><span>Add a learner</span></a>
  <a class="qa" href="<?= url('import') ?>"><?= icon('upload', 20) ?><span>Upload AF3 / CSV</span></a>
  <a class="qa" href="<?= url('reports') ?>"><?= icon('chart', 20) ?><span>Mastery reports</span></a>
  <a class="qa" href="<?= url('board') ?>"><?= icon('trophy', 20) ?><span>Leaderboard</span></a>
</div>
<h2 class="sec">My CLCs</h2>
<?php if (!$clcs): ?><div class="card empty"><?= icon('map', 28) ?><p>No CLC is assigned to you yet. Ask the admin to assign your Community Learning Centers.</p></div><?php endif; ?>
<div class="cards">
<?php foreach ($clcs as $c): ?>
  <a class="card link" href="<?= url('learners', ['clc' => $c['id']]) ?>"><b><?= e($c['name']) ?></b><small class="muted"><?= e(trim($c['municipality'] . ' · ' . $c['district'], ' ·')) ?></small><span class="big-n"><?= (int)$c['learners'] ?> <small>learners</small></span></a>
<?php endforeach; ?>
</div>
<h2 class="sec">Latest results</h2>
<div class="card">
<?php if (!$recent): ?><p class="muted">No finished activities yet.</p><?php endif; ?>
<ul class="feed">
<?php foreach ($recent as $r): $res = $r['status']; $n = count(explode(',', $r['item_ids'])); ?>
  <li><span class="dot <?= $res === 'passed' ? 'ok' : 'bad' ?>"></span><span class="grow"><b><?= e(full_name($r)) ?></b> <?= $res === 'passed' ? 'passed' : ($res === 'lost' ? 'ran out of lives in' : 'did not pass') ?> <?= e(STRANDS[$r['strand']][0] . ' ' . STRANDS[$r['strand']][1]) ?> L<?= (int)$r['level'] ?> A<?= (int)$r['activity'] ?></span><span class="muted small"><?= (int)$r['correct'] ?>/<?= $n ?> · <?= e(date('M j, g:i A', strtotime($r['finished_at']))) ?></span></li>
<?php endforeach; ?>
</ul>
</div>
