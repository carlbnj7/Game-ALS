<div class="page-head noprint"><div><h1>Sign-in slips</h1><p class="muted">Print, cut, and give each learner their slip. Each learner makes a new password on first sign-in.</p></div>
<div class="row-btns"><button class="btn primary" data-print><?= icon('print', 16) ?> Print</button>
<form method="post" data-confirm="Clear this list? You will not see these passwords again."><?= csrf_field() ?><button class="btn ghost">Clear list</button></form></div></div>
<?php if (!$rows): ?><div class="card empty"><p>No new accounts to show.</p></div><?php endif; ?>
<div class="slips">
<?php foreach ($rows as [$name, $un, $pw, $g]): ?>
  <div class="slip"><div class="slip-h"><b>Game<i>ALS</i></b><small><?= e(GRADES[$g] ?? '') ?></small></div><p class="slip-n"><?= e($name) ?></p>
  <dl><dt>ALS ID</dt><dd><?= e($un) ?></dd><dt>Password</dt><dd><?= e($pw) ?></dd></dl><small>Keep this secret. On first sign-in you pick your own username, password and 4-digit PIN.</small></div>
<?php endforeach; ?>
</div>
