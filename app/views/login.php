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
      <div class="wordmark"><b>Game <span class="als">ALS</span></b><small>ALTERNATIVE LEARNING SYSTEM</small></div>
    </div>
    <p class="fullname"><?= e(APP_FULL_NAME) ?></p>
    <h2 class="hero-h">Master every <em>strand.</em></h2>
    <p class="hero-p">Seven Learning Strands. Three levels each. Answer, level up, and earn your Certificate of Readiness.</p>
    <div class="strand-chips"><?= $chips ?></div>
    <svg class="hero-art" viewBox="0 0 560 300" preserveAspectRatio="xMidYMax slice" aria-hidden="true">
      <path d="M0 210C120 170 230 190 330 160S500 110 560 100V300H0z" class="h-green"/>
      <path d="M0 250C140 222 260 238 380 214S520 180 560 176V300H0z" class="h-red"/>
      <g transform="translate(330 92)"><rect x="0" y="72" width="44" height="40" rx="8" fill="#fff" opacity=".95"/><rect x="50" y="44" width="44" height="68" rx="8" fill="#fff"/><rect x="100" y="14" width="44" height="98" rx="8" fill="#fff"/>
        <text x="22" y="98" text-anchor="middle" font-weight="800" font-size="16" fill="#2e7d32">1</text><text x="72" y="84" text-anchor="middle" font-weight="800" font-size="16" fill="#262a6b">2</text><text x="122" y="70" text-anchor="middle" font-weight="800" font-size="16" fill="#c8232c">3</text>
        <g transform="translate(103 -46)"><path d="M8 4h22v14a11 11 0 0 1-22 0z" fill="#f5b301"/><path d="M8 8H2a7 7 0 0 0 8 9M30 8h6a7 7 0 0 1-8 9" stroke="#e09b00" stroke-width="3" fill="none"/><rect x="15" y="29" width="8" height="6" fill="#e09b00"/><rect x="9" y="35" width="20" height="6" rx="2" fill="#c98a00"/></g></g>
      <g transform="translate(206 128) rotate(-8)"><rect width="104" height="74" rx="8" fill="#fff"/><rect x="6" y="6" width="92" height="62" rx="5" fill="none" stroke="#f5b301" stroke-width="2"/><path d="M22 24h60M30 34h44M26 44h52" stroke="#c9cdee" stroke-width="4" stroke-linecap="round"/><circle cx="78" cy="56" r="9" fill="#c8232c"/></g>
      <path d="M120 70l3 6.5 7 1-5 5 1.2 7-6.2-3.3-6.2 3.3 1.2-7-5-5 7-1z" fill="#ffd54f"/>
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
    <p class="muted center small">No account yet? Your ALS teacher makes one for you.<br>Forgot your password? Ask your teacher for a new one.</p>
    <div class="dep-line"><span>DEPARTMENT OF EDUCATION</span></div>
    <div class="auth-foot"><img src="assets/img/als-logo.png" alt="" class="alsimg"><img src="assets/img/division.webp" alt="" width="44" height="44"><span><?= e(setting('division_name', 'Division of Bukidnon')) ?><br><small>Alternative Learning System</small></span></div>
  </section>
</div>
<script src="assets/js/auth.js?v=2"></script>
