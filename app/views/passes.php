<?php $roleName = ['learner' => 'Learners', 'teacher' => 'Teachers', 'admin' => 'Admins']; $keep = array_filter(['clc' => $f['clc'], 'grade' => $f['grade'], 'mun' => $f['mun'], 'dist' => $f['dist'], 'q' => $f['q']]); ?>
<div class="page-head noprint">
  <div><h1>QR sign-in passes</h1><p class="muted">Print and give each person their pass. They scan it, then type their own 4-digit PIN.</p></div>
  <div class="row-btns"><button class="btn primary" data-print><?= icon('print', 16) ?> Print passes</button></div>
</div>
<?php if ($isAdmin): ?>
<nav class="seg-tabs noprint" aria-label="Whose passes">
  <?php foreach ($roleName as $r => $n): ?><a class="<?= $f['role'] === $r ? 'on' : '' ?>" href="<?= url('passes', ['role' => $r] + $keep) ?>"><?= e($n) ?></a><?php endforeach; ?>
</nav>
<?php endif; ?>
<form class="filters noprint" method="get"><input type="hidden" name="p" value="passes"><input type="hidden" name="role" value="<?= e($f['role']) ?>">
  <input type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Search name, username, ALS ID">
  <?php if ($isAdmin && $f['role'] !== 'admin') require ROOT . '/app/views/_places.php'; ?>
  <?php if ($f['role'] !== 'admin'): ?><select name="clc"><option value="0"><?= $isAdmin ? 'All CLCs' : 'All my CLCs' ?></option><?php foreach ($clcs as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $f['clc'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select><?php endif; ?>
  <?php if ($f['role'] === 'learner'): ?><select name="grade"><option value="">Elem and JHS</option><?php foreach (GRADES as $k => $g): ?><option value="<?= $k ?>" <?= $f['grade'] === $k ? 'selected' : '' ?>><?= e($g) ?></option><?php endforeach; ?></select><?php endif; ?>
  <button class="btn">Show</button>
</form>
<div class="pick-bar noprint"><span data-pass-count></span><button type="button" class="btn sm ghost" data-pick-all>Select all</button><button type="button" class="btn sm ghost" data-pick-none>Select none</button></div>
<?php if (!$rows): ?><div class="card empty"><p>No one to show. Try other filters.</p></div><?php endif; ?>
<div class="passes" data-passes data-base="<?= e($base) ?>">
<?php foreach ($rows as $r):
  $place = $r['role'] === 'learner' ? trim(($r['clc'] ?? '') . ($r['municipality'] ? ' · ' . $r['municipality'] : ''), ' ·') : ($r['role'] === 'teacher' ? ($r['tclcs'] ?: 'ALS Teacher') : setting('division_name', 'Division of Bukidnon'));
  $locked = (int)$r['pin_fails'] >= 5; ?>
  <div class="pass-wrap" data-pass>
    <article class="pass r-<?= e($r['role']) ?>">
      <header><img src="assets/img/logo.svg" alt="" width="30" height="32"><span><b>Game <i>ALS</i></b><small>QR SIGN-IN PASS</small></span><em><?= e($r['role'] === 'learner' ? (GRADES[$r['grade'] ?: 'ELEM'] === 'Elementary' ? 'ELEM' : 'JHS') : strtoupper($r['role'])) ?></em></header>
      <div class="pass-body">
        <div class="pass-qr" data-qr="<?= e($r['qr_token']) ?>"></div>
        <div class="pass-info">
          <p class="pass-name"><?= e(full_name($r)) ?></p>
          <dl><dt>ALS ID</dt><dd><?= e($r['als_id']) ?></dd><?php if ($r['setup_done'] && $r['username'] !== $r['als_id']): ?><dt>Username</dt><dd><?= e($r['username']) ?></dd><?php endif; ?></dl>
          <p class="pass-place"><?= e($place) ?></p>
        </div>
      </div>
      <footer><?= icon('pin', 14) ?> Scan, then type your 4-digit PIN. Keep your PIN secret.</footer>
    </article>
    <div class="pass-tools noprint">
      <label class="ck"><input type="checkbox" data-pick checked> Print</label>
      <?php if (!$r['setup_done']): ?><span class="pill">Not set up yet</span><?php endif; ?>
      <?php if ($locked): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="unlock"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><?php foreach (['role' => $f['role']] + $keep as $k => $v): ?><input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>"><?php endforeach; ?><button class="btn sm"><?= icon('lock', 14) ?> Unlock</button></form><?php endif; ?>
      <form method="post" data-confirm="Make a new QR code? The old printed pass will stop working."><?= csrf_field() ?><input type="hidden" name="do" value="renew"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><?php foreach (['role' => $f['role']] + $keep as $k => $v): ?><input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>"><?php endforeach; ?><button class="btn sm ghost"><?= icon('refresh', 14) ?> New QR</button></form>
    </div>
  </div>
<?php endforeach; ?>
</div>
