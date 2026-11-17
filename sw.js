/* Game ALS offline helper. Keeps the app's look (styles, scripts, fonts, icons) on this device,
   and shows a friendly page when there is no internet. Pages with learner data are never stored. */
var CACHE = 'gameals-v4';
var CORE = ['offline.html', 'assets/css/app.css?v=4', 'assets/js/theme.js?v=4', 'assets/fonts/sora.woff2', 'assets/img/logo.svg', 'assets/img/icons/icon-192.png', 'manifest.webmanifest'];
self.addEventListener('install', function (e) {
  e.waitUntil(caches.open(CACHE).then(function (c) { return c.addAll(CORE); }).then(function () { return self.skipWaiting(); }));
});
self.addEventListener('activate', function (e) {
  e.waitUntil(caches.keys().then(function (ks) { return Promise.all(ks.filter(function (k) { return k !== CACHE; }).map(function (k) { return caches.delete(k); })); }).then(function () { return self.clients.claim(); }));
});
self.addEventListener('fetch', function (e) {
  var r = e.request; if (r.method !== 'GET') return;
  var u = new URL(r.url); if (u.origin !== location.origin) return;
  if (r.mode === 'navigate') { e.respondWith(fetch(r).catch(function () { return caches.match('offline.html'); })); return; }
  if (/\/assets\//.test(u.pathname)) {
    e.respondWith(caches.match(r).then(function (hit) {
      var net = fetch(r).then(function (res) { if (res.ok) { var copy = res.clone(); caches.open(CACHE).then(function (c) { c.put(r, copy); }); } return res; });
      return hit || net;
    }));
  }
});
