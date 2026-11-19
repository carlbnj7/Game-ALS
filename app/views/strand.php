<?php [$ls, $short, $name, $col, $ic] = STRANDS[$s]; ?>
<a class="back" href="<?= url('home') ?>"><?= icon('back', 16) ?> All strands</a>
<section class="strand-head c-<?= e(strtolower($s)) ?>">
  <span class="sq big"><?= icon($ic, 30) ?></span>
  <div><small><?= e($ls) ?></small><h1><?= e($name) ?></h1><p><?= strand_done_count($pm, $s) ?> of 9 activities passed</p></div>
</section>
<div class="path">
<?php for ($l = 1; $l <= 3; $l++): $open = level_open($pm, $s, $l); $ld = level_done($pm, $s, $l); ?>
  <section class="station <?= $ld ? 'done' : ($open ? 'open' : 'locked') ?>">
    <div class="node"><?= $ld ? icon('check', 20) : ($open ? $l : icon('lock', 18)) ?></div>
    <div class="station-body">
      <header><h2>Level <?= $l ?> <span class="muted">· <?= LEVELS[$l] ?></span></h2>
        <?php if ($ld): ?><a class="chip ok" href="<?= url('certs') ?>"><?= icon('award', 14) ?> Certificate earned</a>
        <?php elseif (!$open): ?><span class="chip"><?= icon('lock', 13) ?> <?= e(level_lock_text($pm, $s, $l)) ?></span><?php endif; ?>
      </header>
      <div class="acts">
      <?php for ($a = 1; $a <= 3; $a++): $p = $pm["$s|$l|$a"] ?? null; $n = $counts["$l|$a"] ?? 0; $k = "$l|$a"; $ao = $open && activity_open($pm, $s, $l, $a, me()['grade'] ?: 'ELEM'); ?>
        <div class="act <?= !empty($p['passed']) ? 'passed' : '' ?>">
          <div class="act-t"><b>Activity <?= $a ?></b><small>Items <?= activity_range($a) ?> · <?= $n ?> questions</small></div>
          <div class="act-score">
            <?php if ($p): ?><span class="score <?= $p['passed'] ? 'ok' : 'warn' ?>"><?= (int)round((float)$p['best_score']) ?>%</span><small>best of <?= (int)$p['tries'] ?> tr<?= $p['tries'] == 1 ? 'y' : 'ies' ?></small>
            <?php else: ?><small class="muted">Not started</small><?php endif; ?>
          </div>
          <?php if (!$open || !$n): ?>
            <span class="btn ghost disabled"><?= icon('lock', 15) ?> <?= $n ? 'Locked' : 'Soon' ?></span>
          <?php elseif (!$ao): ?>
            <span class="btn ghost disabled" title="Pass Activity <?= $a - 1 ?> first"><?= icon('lock', 15) ?> Pass Activity <?= $a - 1 ?> first</span>
          <?php elseif (isset($playing[$k])): ?>
            <a class="btn primary" href="<?= url('play', ['s' => $s, 'l' => $l, 'a' => $a]) ?>"><?= icon('play', 15) ?> Resume <?= $playing[$k] + 1 ?>/<?= $n ?></a>
          <?php else: ?>
            <a class="btn <?= !empty($p['passed']) ? 'ghost' : 'primary' ?>" href="<?= url('play', ['s' => $s, 'l' => $l, 'a' => $a]) ?>"><?= icon(!empty($p['passed']) ? 'refresh' : 'play', 15) ?> <?= !empty($p['passed']) ? 'Play again' : ($p ? 'Try again' : 'Start') ?></a>
          <?php endif; ?>
        </div>
      <?php endfor; ?>
      </div>
    </div>
  </section>
<?php endfor; ?>
</div>
<div class="rules card">
  <h3>How it works</h3>
  <ul class="rule-list">
    <li><?= icon('heart', 18) ?><span>You start with <b>3 lives</b>. A wrong answer takes one away. With no lives left, the activity starts over.</span></li>
    <li><?= icon('flame', 18) ?><span><b>5 correct in a row</b> gives you one more life (up to 5).</span></li>
    <li><?= icon('bulb', 18) ?><span><b>3 hints</b> per activity. A hint takes away two wrong choices. Hints do not carry over.</span></li>
    <li><?= icon('star', 18) ?><span>Get <b>75% or more</b> to pass. Pass all 3 activities to finish a level and earn its certificate.</span></li>
  </ul>
</div>
