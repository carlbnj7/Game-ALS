<?php $ready = $have['readiness||0'] ?? null; $levelsDone = count($have) - ($ready ? 1 : 0); ?>
<section class="ready-card <?= $ready ? 'got' : '' ?>">
  <span class="sq big gold"><?= icon('award', 30) ?></span>
  <div class="grow">
    <h2>Certificate of Readiness</h2>
    <p><?= $ready ? 'You finished all 7 Learning Strands. Congratulations!' : 'Finish all 3 levels of all 7 Learning Strands to earn this.' ?></p>
    <progress max="21" value="<?= $levelsDone ?>"></progress><small class="muted"><?= $levelsDone ?> of 21 levels done</small>
  </div>
  <?php if ($ready): ?><a class="btn primary" href="<?= url('cert', ['code' => $ready['code']]) ?>"><?= icon('print', 16) ?> View &amp; print</a><?php endif; ?>
</section>
<h2 class="sec">Certificates of Passing</h2>
<div class="cert-grid">
<?php foreach (STRANDS as $s => [$ls, $short, $name, $col, $ic]): for ($l = 1; $l <= 3; $l++): $c = $have["level|$s|$l"] ?? null; ?>
  <div class="cert-tile <?= $c ? 'got' : '' ?>">
    <span class="sq c-<?= e(strtolower($s)) ?>"><?= icon($c ? 'award' : $ic, 20) ?></span>
    <div class="grow"><small><?= e($ls . ' ' . $short) ?></small><b>Level <?= $l ?> · <?= LEVELS[$l] ?></b></div>
    <?php if ($c): ?><a class="btn sm" href="<?= url('cert', ['code' => $c['code']]) ?>">View</a><?php else: ?><span class="muted small"><?= icon('lock', 13) ?></span><?php endif; ?>
  </div>
<?php endfor; endforeach; ?>
</div>
