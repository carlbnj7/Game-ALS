<div class="card narrow">
  <h2><?= $forced ? 'Make your own password' : 'Change password' ?></h2>
  <?php if ($forced): ?><p class="muted">Your account still uses the password you were given. Make a new one that only you know.</p><?php endif; ?>
  <?php if ($err): ?><div class="flash err"><?= e($err) ?></div><?php endif; ?>
  <form method="post" action="<?= url('password') ?>" class="form">
    <?= csrf_field() ?>
    <label>Current password<input type="password" name="current" required autocomplete="current-password"></label>
    <label>New password (at least 8 characters)<input type="password" name="new1" required minlength="8" autocomplete="new-password"></label>
    <label>Type the new password again<input type="password" name="new2" required minlength="8" autocomplete="new-password"></label>
    <button class="btn primary">Save new password</button>
  </form>
</div>
