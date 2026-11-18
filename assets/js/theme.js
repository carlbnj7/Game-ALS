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
  // PIN boxes: numbers only, 4 at most.
  document.addEventListener('input', function (ev) {
    var i = ev.target; if (!i.matches || !i.matches('[data-pin]')) return;
    var v = i.value.replace(/\D/g, '').slice(0, 4); if (v !== i.value) i.value = v;
  });
  // Offline helper (needs https, or localhost while testing).
  if ('serviceWorker' in navigator && (location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1')) {
    window.addEventListener('load', function () { navigator.serviceWorker.register('sw.js').catch(function () {}); });
  }
  // Install Game ALS like an app.
  var standalone = (window.matchMedia && matchMedia('(display-mode: standalone)').matches) || navigator.standalone === true;
  var ios = /iphone|ipad|ipod/i.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
  var deferred = null;
  function showInstall() {
    if (standalone) return;
    document.querySelectorAll('[data-install]').forEach(function (b) { if (deferred || ios) b.hidden = false; });
  }
  window.addEventListener('beforeinstallprompt', function (ev) { ev.preventDefault(); deferred = ev; showInstall(); });
  window.addEventListener('appinstalled', function () { deferred = null; document.querySelectorAll('[data-install]').forEach(function (b) { b.hidden = true; }); });
  document.addEventListener('DOMContentLoaded', showInstall);
  document.addEventListener('click', function (ev) {
    var b = ev.target.closest('[data-install]'); if (!b) return;
    if (deferred) { deferred.prompt(); deferred.userChoice.then(function () { deferred = null; }); return; }
    var h = document.querySelector('[data-install-ios]'); if (h) h.hidden = !h.hidden;
  });
  document.addEventListener('click', function (ev) {
    var p = ev.target.closest('[data-print]'); if (p) { ev.preventDefault(); window.print(); }
  });
})();
// Menu: search box, and the slide-out menu on phones.
document.addEventListener('input', function (e) {
  if (!e.target.matches || !e.target.matches('[data-navsearch]')) return;
  var q = e.target.value.trim().toLowerCase();
  document.querySelectorAll('[data-navitem]').forEach(function (a) { a.hidden = q !== '' && a.textContent.toLowerCase().indexOf(q) < 0; });
  document.querySelectorAll('.nav-h').forEach(function (h) { h.hidden = q !== ''; });
});
document.addEventListener('click', function (e) {
  if (e.target.closest('[data-menu]')) document.body.classList.add('menu-open');
  else if (e.target.closest('[data-menu-close]')) document.body.classList.remove('menu-open');
});
document.addEventListener('keydown', function (e) { if (e.key === 'Escape') document.body.classList.remove('menu-open'); });
// A select marked data-autosave sends its form as soon as it changes.
document.addEventListener('change', function (e) { if (e.target.matches && e.target.matches('select[data-autosave]')) e.target.form.submit(); });
// Teachers page: admins manage all districts, so hide the District box when "Admin" is chosen.
document.addEventListener('DOMContentLoaded', function () {
  var r = document.querySelector('details.add select[name=role]'), d = document.querySelector('[data-teacher-only]'); if (!r || !d) return;
  function sync() { d.hidden = r.value === 'admin'; d.querySelector('select').disabled = r.value === 'admin'; }
  r.addEventListener('change', sync); sync();
});
// Account page: send the photo as soon as one is chosen.
document.addEventListener('change', function (e) { if (e.target.matches && e.target.matches('[data-autosubmit-file]') && e.target.files.length) e.target.form.submit(); });
