<?php $u = me(); ?>
<div class="card narrow">
  <div class="acct"><span class="av big"><?= e(mb_strtoupper(mb_substr($u['first_name'], 0, 1))) ?></span><div><h2><?= e(full_name($u)) ?></h2><p class="muted"><?= e(ucfirst($u['role'])) ?> · <?= e($u['username']) ?></p></div></div>
  <dl class="kv">
    <dt>ALS ID</dt><dd><code><?= e($u['als_id']) ?></code></dd>
    <dt>Username</dt><dd><?= e($u['username']) ?></dd>
    <?php if ($u['role'] === 'learner'): ?>
      <dt>Grade level</dt><dd><?= e(GRADES[$u['grade'] ?: 'ELEM']) ?></dd>
      <dt>CLC</dt><dd><?= e($clc['name'] ?? '—') ?></dd>
      <?php if ($u['lrn']): ?><dt>LRN</dt><dd><?= e($u['lrn']) ?></dd><?php endif; ?>
    <?php endif; ?>
    <dt>Last sign-in</dt><dd><?= $u['last_login'] ? e(date('M j, Y g:i A', strtotime($u['last_login']))) : '—' ?></dd>
  </dl>
  <div class="row-btns">
    <a class="btn" href="<?= url('password') ?>"><?= icon('key', 16) ?> Change password</a>
    <a class="btn" href="<?= url('pin') ?>"><?= icon('pin', 16) ?> Username and PIN</a>
    <button type="button" class="btn ghost" data-theme-toggle><?= icon('moon', 16) ?> Light / dark</button>
    <form method="post" action="<?= url('logout') ?>"><?= csrf_field() ?><button class="btn ghost"><?= icon('logout', 16) ?> Sign out</button></form>
  </div>
</div>
