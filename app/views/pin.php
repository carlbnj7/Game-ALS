<?php $u = me(); ?>
<div class="card narrow">
  <h2>Username and PIN</h2>
  <p class="muted">Your ALS ID <b><?= e($u['als_id']) ?></b> never changes. You can sign in with it or with your username.</p>
  <?php if ($err): ?><div class="flash err"><?= e($err) ?></div><?php endif; ?>
  <form method="post" action="<?= url('pin') ?>" class="form" autocomplete="off">
    <?= csrf_field() ?>
    <label>Username<input name="username" value="<?= e($_POST['username'] ?? $u['username']) ?>" required minlength="3" maxlength="30" autocapitalize="none" spellcheck="false"></label>
    <div class="grid2">
      <label>New 4-digit PIN (leave blank to keep)<input class="pin-in" type="password" name="pin1" inputmode="numeric" pattern="\d{4}" maxlength="4" autocomplete="off"></label>
      <label>Type the new PIN again<input class="pin-in" type="password" name="pin2" inputmode="numeric" pattern="\d{4}" maxlength="4" autocomplete="off"></label>
    </div>
    <label>Your password (to confirm)<input type="password" name="current" required autocomplete="current-password"></label>
    <button class="btn primary">Save</button>
  </form>
</div>
