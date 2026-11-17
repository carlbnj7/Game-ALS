document.addEventListener('click', function (e) {
  var b = e.target.closest('[data-peek]'); if (!b) return;
  var i = document.getElementById(b.getAttribute('data-peek'));
  var show = i.type === 'password'; i.type = show ? 'text' : 'password'; b.textContent = show ? 'Hide' : 'Show';
});

// QR pass: numbers only, dots fill up, and the PIN is sent as soon as 4 numbers are in.
document.querySelectorAll('[data-autosubmit]').forEach(function (i) {
  var dots = document.querySelectorAll('[data-pin-dots] i'), sent = false;
  function sync() {
    i.value = i.value.replace(/\D/g, '').slice(0, 4);
    for (var k = 0; k < dots.length; k++) dots[k].classList.toggle('on', k < i.value.length);
    if (i.value.length === 4 && i.form && !sent) { sent = true; i.form.requestSubmit ? i.form.requestSubmit() : i.form.submit(); }
  }
  i.addEventListener('input', sync);
  var pad = document.querySelector('[data-keypad="' + i.id + '"]');
  if (pad) pad.addEventListener('click', function (e) {
    var b = e.target.closest('[data-k]'); if (!b || sent) return;
    var k = b.getAttribute('data-k');
    if (k === 'clear') i.value = ''; else if (k === 'back') i.value = i.value.slice(0, -1); else if (i.value.length < 4) i.value += k;
    sync();
  });
  sync();
});

// Scan a QR pass with this device's camera (works where the browser can read QR codes).
(function () {
  var box = document.querySelector('[data-scanner]'); if (!box) return;
  var btn = box.querySelector('[data-scan-start]'), msg = box.querySelector('[data-scan-msg]'), wrap = box.querySelector('[data-scan-box]'), video = box.querySelector('[data-scan-video]');
  if (!('BarcodeDetector' in window) || !navigator.mediaDevices) { msg.textContent = 'Tip: use your phone camera app to scan the pass.'; return; }
  btn.hidden = false;
  btn.addEventListener('click', function () {
    var det = new window.BarcodeDetector({ formats: ['qr_code'] });
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } }).then(function (stream) {
      wrap.hidden = false; btn.hidden = true; video.srcObject = stream; video.play(); msg.textContent = 'Point the camera at the QR code.';
      var stop = function () { stream.getTracks().forEach(function (t) { t.stop(); }); };
      var tick = function () {
        det.detect(video).then(function (codes) {
          for (var k = 0; k < codes.length; k++) {
            try {
              var u = new URL(codes[k].rawValue, location.href);
              if (u.origin === location.origin && u.searchParams.get('p') === 'qr' && /^[a-f0-9]{40}$/.test(u.searchParams.get('t') || '')) { stop(); location.href = u.href; return; }
            } catch (e) {}
          }
          msg.textContent = codes.length ? 'That QR code is not a Game ALS pass.' : 'Point the camera at the QR code.';
          requestAnimationFrame(tick);
        }).catch(function () { requestAnimationFrame(tick); });
      };
      tick();
    }).catch(function () { msg.textContent = 'The camera could not open. Use your phone camera app to scan the pass.'; });
  });
})();
