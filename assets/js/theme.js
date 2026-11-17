(function () {
  var k = 'gameals-theme', t = null;
  try { t = localStorage.getItem(k); } catch (e) {}
  if (!t) t = (window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
  document.documentElement.setAttribute('data-theme', t);
  document.addEventListener('click', function (ev) {
    var b = ev.target.closest('[data-theme-toggle]'); if (!b) return;
    var n = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', n);
    try { localStorage.setItem(k, n); } catch (e) {}
  });
  document.addEventListener('submit', function (ev) {
    var m = ev.target.getAttribute('data-confirm');
    if (m && !confirm(m)) ev.preventDefault();
  });
  document.addEventListener('click', function (ev) {
    var p = ev.target.closest('[data-print]'); if (p) { ev.preventDefault(); window.print(); }
  });
})();
