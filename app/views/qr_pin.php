<div class="auth-bg" aria-hidden="true"><i class="b1"></i><i class="b2"></i><i class="b3"></i></div>
<div class="qr-wrap">
  <a class="qr-brand" href="<?= url('login') ?>"><img src="assets/img/logo.svg" alt="" width="40" height="43"><b>Game <i>ALS</i></b></a>
  <div class="card qr-card">
  <?php if (!$who): ?>
    <span class="qr-ic bad"><?= icon('x', 30) ?></span>
    <h1>This pass does not work</h1>
    <p class="muted">It may be an old pass. Ask your teacher for a new one, or sign in with your username and password.</p>
    <a class="btn primary big" href="<?= url('login') ?>"><span>Go to sign in</span><?= icon('arrow', 18) ?></a>
  <?php else: ?>
    <span class="av huge"><?= e(mb_strtoupper(mb_substr($who['first_name'], 0, 1))) ?></span>
    <p class="qr-hi">Hello,</p>
    <h1><?= e(full_name($who)) ?></h1>
    <p class="muted small"><?= e(ucfirst($who['role'])) ?> · <?= e($who['als_id']) ?></p>
    <?php if ($err): ?><div class="flash err"><?= e($err) ?></div><?php endif; ?>
    <?php if (!$locked): ?>
    <form method="post" action="<?= url('qr') ?>" class="auth-form" autocomplete="off">
      <?= csrf_field() ?><input type="hidden" name="t" value="<?= e($t) ?>">
      <?php if ($usePin): ?>
        <label for="code">Type your 4-digit PIN</label>
        <input id="code" class="pin-big" type="password" name="code" required inputmode="numeric" pattern="\d{4}" minlength="4" maxlength="4" autofocus autocomplete="off" data-autosubmit>
      <?php else: ?>
        <label for="code">First time? Type the password on your slip</label>
        <div class="field"><?= icon('key', 18) ?><input id="code" type="password" name="code" required autofocus autocomplete="current-password"></div>
      <?php endif; ?>
      <button class="btn primary big"><span>Sign in</span><?= icon('arrow', 18) ?></button>
    </form>
    <?php endif; ?>
    <a class="linkish small" href="<?= url('login') ?>">Not you? Sign in another way</a>
  <?php endif; ?>
  </div>
</div>
<script src="assets/js/auth.js?v=3"></script>
