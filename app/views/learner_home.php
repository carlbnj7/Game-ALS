<?php $u = me(); $lockedShown = false; ?>
<section class="home-head">
  <div class="greet"><small>Kumusta,</small><h1><?= e(full_name($u)) ?></h1>
    <p class="muted"><?= e(GRADES[$u['grade'] ?: 'ELEM']) ?><?= $clc ? ' · ' . e($clc['name']) : '' ?></p></div>
  <img class="home-als" src="assets/img/als-logo.png" alt="DepEd ALS logo">
</section>
<div class="stat-row">
  <div class="stat s-navy"><?= icon('star', 40) ?><b><?= number_format($points) ?></b><span>Points</span></div>
  <div class="stat s-green"><?= icon('check', 40) ?><b><?= $passed ?><small>/63</small></b><span>Activities passed</span></div>
  <div class="stat s-red"><?= icon('award', 40) ?><b><?= $certs ?></b><span>Certificates</span></div>
</div>

<div class="sec-row"><h2 class="sec">Learning Strands</h2><span class="muted small">Pass Level 1 of every strand first, then Level 2, then Level 3 (75% or more).</span></div>
<div class="tiles-grid">
<?php $first = true; foreach (STRANDS as $code => [$ls, $short, $name, $col, $ic]):
    $open = strand_open($pm, $code); $done = strand_done_count($pm, $code); $C = 2 * M_PI * 15;
    $isNext = $next && $next[0] === $code;
    $href = $open ? url('strand', ['s' => $code]) : null;
    $lockText = $open ? '' : level_lock_text($pm, $code, 1); ?>
  <<?= $href ? 'a href="' . e($href) . '"' : 'div' ?> class="stile c-<?= e(strtolower($code)) ?> <?= $isNext ? 'wide' : '' ?> <?= $open ? '' : 'locked' ?>">
    <span class="glow"></span>
    <?= strand_art($ic) ?>
    <div class="st-head">
      <div><small><?= e($ls) ?></small><b><?= e($isNext ? $name : $short) ?></b></div>
      <?php if (!$isNext): ?><span class="ring"><svg viewBox="0 0 38 38"><circle cx="19" cy="19" r="15" class="tr"/><circle cx="19" cy="19" r="15" class="vl" stroke-dasharray="<?= round($C, 1) ?>" stroke-dashoffset="<?= round($C * (1 - $done / 9), 1) ?>"/></svg><em><?= $done ?></em></span><?php endif; ?>
    </div>
    <?php if ($isNext): ?>
      <p class="st-sub"><?= $done ?> of 9 activities passed</p>
      <span class="st-go"><?= icon('play', 13) ?> <?= $playing && $playing['strand'] === $code ? 'Continue' : 'Start' ?></span>
    <?php endif; ?>
    <div class="lvls">
      <?php for ($l = 1; $l <= 3; $l++): $lo = level_open($pm, $code, $l); $ld = level_done($pm, $code, $l); ?>
        <span class="<?= $ld ? 'done' : ($lo ? 'open' : '') ?>"><?= $ld ? icon('check', 10) : ($lo ? '' : icon('lock', 9)) ?>L<?= $l ?></span>
      <?php endfor; ?>
    </div>
    <?php if (!$open): ?><span class="lock-note"><?= icon('lock', 13) ?> <?= e($lockText) ?></span><?php endif; ?>
    <?php if (empty($counts[$code])): ?><span class="soon">Questions coming soon</span><?php endif; ?>
  </<?= $href ? 'a' : 'div' ?>>
<?php endforeach; ?>
</div>
<?php if ($playing): $st = STRANDS[$playing['strand']]; ?>
<a class="resume" href="<?= url('play', ['s' => $playing['strand'], 'l' => $playing['level'], 'a' => $playing['activity']]) ?>">
  <span class="sq c-<?= e(strtolower($playing['strand'])) ?>"><?= icon($st[4], 22) ?></span>
  <span class="grow"><small>Continue where you stopped</small><b><?= e($st[0] . ' ' . $st[1]) ?> · Level <?= (int)$playing['level'] ?> · Activity <?= (int)$playing['activity'] ?></b><em>Item <?= (int)$playing['pos'] + 1 ?> of <?= count(explode(',', $playing['item_ids'])) ?></em></span>
  <span class="btn primary"><?= icon('play', 16) ?> Resume</span>
</a>
<?php endif; ?>
