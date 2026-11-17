// Draw each QR pass code as an SVG (no inline styles, so it works with the page's security rules).
(function () {
  var NS = 'http://www.w3.org/2000/svg', sheet = document.querySelector('[data-passes]'); if (!sheet) return;
  var base = sheet.getAttribute('data-base');
  document.querySelectorAll('[data-qr]').forEach(function (box) {
    if (typeof qrcode !== 'function') return;
    var q = qrcode(0, 'M'); q.addData(base + box.getAttribute('data-qr')); q.make();
    var n = q.getModuleCount(), m = 2, svg = document.createElementNS(NS, 'svg'), d = '';
    svg.setAttribute('viewBox', '0 0 ' + (n + m * 2) + ' ' + (n + m * 2)); svg.setAttribute('shape-rendering', 'crispEdges'); svg.setAttribute('role', 'img'); svg.setAttribute('aria-label', 'QR code to sign in');
    for (var r = 0; r < n; r++) for (var c = 0; c < n; c++) if (q.isDark(r, c)) d += 'M' + (c + m) + ' ' + (r + m) + 'h1v1h-1z';
    var bg = document.createElementNS(NS, 'rect'); bg.setAttribute('width', '100%'); bg.setAttribute('height', '100%'); bg.setAttribute('fill', '#fff');
    var p = document.createElementNS(NS, 'path'); p.setAttribute('d', d); p.setAttribute('fill', '#1b1f5e');
    svg.append(bg, p); box.replaceChildren(svg);
  });
  // Pick which passes to print.
  var wraps = [].slice.call(sheet.querySelectorAll('[data-pass]')), count = document.querySelector('[data-pass-count]');
  function sync() { var n = 0; wraps.forEach(function (w) { var on = w.querySelector('[data-pick]').checked; w.classList.toggle('skip', !on); if (on) n++; }); if (count) count.textContent = n + ' of ' + wraps.length + ' selected'; }
  wraps.forEach(function (w) { w.querySelector('[data-pick]').addEventListener('change', sync); });
  function all(on) { wraps.forEach(function (w) { w.querySelector('[data-pick]').checked = on; }); sync(); }
  var a = document.querySelector('[data-pick-all]'), z = document.querySelector('[data-pick-none]');
  if (a) a.addEventListener('click', function () { all(true); }); if (z) z.addEventListener('click', function () { all(false); });
  sync();
})();
