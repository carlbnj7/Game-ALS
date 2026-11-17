<?php $chips = ''; foreach (STRANDS as $code => [$ls, $short, $name, $col, $ic]) $chips .= '<span class="schip c-' . e(strtolower($code)) . '" title="' . e($ls . ' ' . $short) . '">' . icon($ic, 17) . '</span>'; ?>
<div class="auth-bg" aria-hidden="true"><i class="b1"></i><i class="b2"></i><i class="b3"></i></div>
<button type="button" class="seg-toggle corner" data-theme-toggle aria-label="Switch light or dark"><span class="s-sun"><?= icon('sun', 15) ?></span><span class="s-moon"><?= icon('moon', 14) ?></span></button>
<div class="auth-panel">
  <section class="auth-hero">
    <div class="hero-top">
      <span class="dep-badge"><img src="assets/img/als-logo.png" alt="DepEd ALS logo"><img src="assets/img/division.webp" alt="Division of Bukidnon logo"><span>DepEd ALS<br><small><?= e(setting('division_name', 'Division of Bukidnon')) ?></small></span></span>
      <button type="button" class="seg-toggle" data-theme-toggle aria-label="Switch light or dark"><span class="s-sun"><?= icon('sun', 15) ?></span><span class="s-moon"><?= icon('moon', 14) ?></span></button>
    </div>
    <div class="hero-brand">
      <img class="hero-logo" src="assets/img/logo-ribbon.svg" alt="Game ALS logo" width="132" height="143">
      <div class="wordmark"><b>Game <span class="als">ALS</span></b><small class="wm-full"><?= e(APP_FULL_NAME) ?></small></div>
    </div>
    <h2 class="hero-h">Master every <em>strand.</em></h2>
    <p class="hero-p">Seven Learning Strands. Three levels each. Answer, level up, and earn your Certificate of Readiness.</p>
    <div class="strand-chips"><?= $chips ?></div>
    <svg class="hero-art" viewBox="0 0 560 300" preserveAspectRatio="xMidYMax slice" aria-hidden="true">
      <path d="M0 210C120 170 230 190 330 160S500 110 560 100V300H0z" class="h-green"/>
      <path d="M0 250C140 222 260 238 380 214S520 180 560 176V300H0z" class="h-red"/>
    </svg>
  </section>
  <section class="auth-sheet">
    <div class="grab"></div>
    <span class="welcome-pill"><i></i>WELCOME BACK</span>
    <h1>Ready to play and learn?</h1>
    <p class="muted sub">One sign-in for learners, teachers, and admin.</p>
    <?php if ($err): ?><div class="flash err"><?= e($err) ?></div><?php endif; ?>
    <form method="post" action="<?= url('login') ?>" class="auth-form" autocomplete="on">
      <?= csrf_field() ?>
      <label for="u">Username or ALS ID</label>
      <div class="field"><?= icon('user', 18) ?><input id="u" name="username" value="<?= e($old) ?>" required autocomplete="username" autocapitalize="none" spellcheck="false"></div>
      <label for="pw">Password</label>
      <div class="field"><?= icon('key', 18) ?><input id="pw" type="password" name="password" required autocomplete="current-password"><button type="button" class="peek" data-peek="pw">Show</button></div>
      <button class="btn primary big"><span>Sign in</span><?= icon('arrow', 18) ?></button>
    </form>
    <a class="btn qr-btn" href="<?= url('qr') ?>"><?= icon('qr', 18) ?><span>I have a QR pass</span></a>
    <button type="button" class="btn install-btn" data-install hidden><?= icon('download', 18) ?><span>Install Game ALS on this device</span></button>
    <p class="install-ios" data-install-ios hidden>On iPhone or iPad: tap <b>Share</b> at the bottom of Safari, then <b>Add to Home Screen</b>.</p>
    <p class="muted center small">No account yet? Your ALS teacher makes one for you.<br>Forgot your password? Ask your teacher for a new one.</p>
    <div class="dep-line"><span>DEPARTMENT OF EDUCATION</span></div>
    <div class="auth-foot"><img src="assets/img/als-logo.png" alt="" class="alsimg"><img src="assets/img/division.webp" alt="" width="44" height="44"><span><?= e(setting('division_name', 'Division of Bukidnon')) ?><br><small>Alternative Learning System</small></span></div>
  </section>
</div>
<script src="assets/js/auth.js?v=4"></script>
