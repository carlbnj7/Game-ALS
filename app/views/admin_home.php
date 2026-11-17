<section class="hello">
  <div><p class="eyebrow">Admin</p><h1>Game ALS overview</h1><p class="muted">Accounts, CLCs, questions, and results for the whole division.</p></div>
</section>
<div class="tiles">
  <a class="tile" href="<?= url('teachers') ?>"><?= icon('school', 22) ?><b><?= $n['teachers'] ?></b><span>Teachers</span></a>
  <a class="tile" href="<?= url('learners') ?>"><?= icon('users', 22) ?><b><?= $n['learners'] ?></b><span>Learners</span></a>
  <a class="tile" href="<?= url('clcs') ?>"><?= icon('map', 22) ?><b><?= $n['clcs'] ?></b><span>CLCs</span></a>
  <a class="tile" href="<?= url('questions') ?>"><?= icon('list', 22) ?><b><?= number_format($n['items']) ?></b><span>Questions in use<?= $n['hidden'] ? ' · ' . $n['hidden'] . ' hidden' : '' ?></span></a>
  <a class="tile" href="<?= url('reports') ?>"><?= icon('award', 22) ?><b><?= $n['certs'] ?></b><span>Certificates given</span></a>
  <a class="tile" href="<?= url('settings') ?>"><?= icon('edit', 22) ?><b>Settings</b><span>Names on certificates</span></a>
</div>
<h2 class="sec">Results by strand</h2>
<div class="card">
<table class="tbl">
<thead><tr><th>Strand</th><th>Activities tried</th><th>Passed</th><th>Average best score</th></tr></thead>
<tbody>
<?php $bs = array_column($byStrand, null, 'strand'); foreach (STRANDS as $s => $st): $r = $bs[$s] ?? null; ?>
<tr><td><span class="pill c-<?= e(strtolower($s)) ?>"><?= e($st[0]) ?></span> <?= e($st[2]) ?></td><td><?= (int)($r['tries'] ?? 0) ?></td><td><?= (int)($r['passed'] ?? 0) ?></td><td><?= $r ? (int)$r['avg'] . '%' : '—' ?></td></tr>
<?php endforeach; ?>
</tbody></table>
</div>
