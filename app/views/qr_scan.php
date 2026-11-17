<div class="auth-bg" aria-hidden="true"><i class="b1"></i><i class="b2"></i><i class="b3"></i></div>
<div class="qr-wrap">
  <a class="qr-brand" href="<?= url('login') ?>"><img src="assets/img/logo.svg" alt="" width="40" height="43"><b>Game <i>ALS</i></b></a>
  <div class="card qr-card" data-scanner>
    <span class="qr-ic"><?= icon('qr', 30) ?></span>
    <h1>Scan your QR pass</h1>
    <p class="muted">Open your phone camera and point it at the QR code on your pass. Then type your 4-digit PIN.</p>
    <div class="scan-box" hidden data-scan-box><video playsinline muted data-scan-video></video><i class="scan-frame"></i></div>
    <button type="button" class="btn primary big" hidden data-scan-start><?= icon('camera', 18) ?><span>Scan with this device</span></button>
    <p class="small muted" data-scan-msg></p>
    <a class="linkish small" href="<?= url('login') ?>">Sign in with username and password</a>
  </div>
</div>
<script src="assets/js/auth.js?v=4"></script>
