<?php
$isReady = $c['type'] === 'readiness';
$date = date('F j, Y', strtotime($c['issued_at']));
?>
<div class="cert-tools noprint">
  <a class="btn ghost" href="<?= url(me()['role'] === 'learner' ? 'certs' : 'learner', me()['role'] === 'learner' ? [] : ['id' => $c['user_id']]) ?>"><?= icon('back', 16) ?> Back</a>
  <button type="button" class="btn primary" data-print><?= icon('print', 16) ?> Print or save as PDF</button>
</div>
<div class="cert-sheet <?= $isReady ? 'ready' : '' ?>">
  <div class="cert-border">
    <div class="cert-head">
      <img src="assets/img/division.webp" alt="" width="74" height="74">
      <div><small>Republic of the Philippines · Department of Education</small><b><?= e(setting('region_name', 'Region X - Northern Mindanao')) ?></b><b><?= e(mb_strtoupper(setting('division_name', 'Division of Bukidnon'))) ?></b><small>Alternative Learning System</small></div>
      <img src="assets/img/als-logo.png" alt="" class="cert-als">
    </div>
    <h1><?= $isReady ? 'Certificate of Readiness' : 'Certificate of Passing' ?></h1>
    <p class="given">is proudly given to</p>
    <h2 class="cname"><?= e(mb_strtoupper($name)) ?></h2>
    <p class="cbody">
      <?php if ($isReady): ?>
        for completing all three levels of the seven ALS Learning Strands in Game ALS (<?= e(GRADES[$c['grade'] ?: 'ELEM']) ?>), showing readiness for the next step in learning.
      <?php else: $st = STRANDS[$c['strand']]; ?>
        for passing <b>Level <?= (int)$c['level'] ?> (<?= LEVELS[(int)$c['level']] ?>)</b> of <b><?= e($st[0] . ': ' . $st[2]) ?></b> in Game ALS, scoring at least 75% in all three activities.
      <?php endif; ?>
    </p>
    <p class="cwhen">Given on <?= e($date) ?><?= $clc ? ' at ' . e($clc['name']) . ($clc['municipality'] ? ', ' . e($clc['municipality']) : '') : '' ?>.</p>
    <div class="signs">
      <div><span class="line"></span><b>ALS Teacher</b></div>
      <div><span class="line"><?= e(setting('sds_name')) ?></span><b><?= e(setting('sds_title', 'Schools Division Superintendent')) ?></b></div>
    </div>
    <p class="ccode"><img src="assets/img/logo.svg" alt="" width="18"> Game ALS · <?= e(APP_FULL_NAME) ?> · Certificate No. <?= e($c['code']) ?></p>
  </div>
</div>
