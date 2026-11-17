<?php
$u = me();
$bare = $bare ?? !$u;
$nav = [];
if ($u) {
    if ($u['role'] === 'learner') $nav = [['home', 'Home', 'home'], ['certs', 'Certificates', 'award'], ['board', 'Leaderboard', 'trophy'], ['account', 'Account', 'user']];
    if ($u['role'] === 'teacher') $nav = [['home', 'Home', 'home'], ['learners', 'Learners', 'users'], ['reports', 'Reports', 'chart'], ['board', 'Leaderboard', 'trophy'], ['passes', 'QR passes', 'qr'], ['account', 'Account', 'user']];
    if ($u['role'] === 'admin') $nav = [['home', 'Home', 'home'], ['teachers', 'Teachers', 'school'], ['places', 'Municipalities', 'city'], ['clcs', 'CLCs', 'map'], ['learners', 'Learners', 'users'], ['passes', 'QR passes', 'qr'], ['questions', 'Questions', 'list'], ['reports', 'Reports', 'chart'], ['board', 'Leaderboard', 'trophy'], ['account', 'Account', 'user']];
}
$cur = $_GET['p'] ?? 'home';
?><!doctype html>
<html lang="en" data-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#262a6b">
<title><?= e(($title ?? 'Home') . ' · Game ALS') ?></title>
<link rel="icon" href="assets/img/icons/favicon-64.png?v=3" type="image/png">
<link rel="apple-touch-icon" href="assets/img/icons/apple-touch-icon.png?v=3">
<link rel="manifest" href="manifest.webmanifest">
<meta name="apple-mobile-web-app-title" content="Game ALS">
<link rel="stylesheet" href="assets/css/app.css?v=3">
<script src="assets/js/theme.js?v=1"></script>
</head>
<body class="<?= e($bodyClass ?? '') ?>" data-csrf="<?= e($_SESSION['csrf'] ?? '') ?>">
<?php if ($bare): require $view_file; else: ?>
<div class="shell">
  <aside class="side">
    <a class="brand" href="<?= url('home') ?>"><img class="mark" src="assets/img/logo.svg" alt="" width="40" height="43"><span><b>Game <i>ALS</i></b><small>Alternative Learning System</small></span></a>
    <nav>
      <?php foreach ($nav as [$p, $label, $ic]): ?>
        <a href="<?= url($p) ?>" class="<?= $cur === $p ? 'on' : '' ?>"><?= icon($ic) ?><span><?= e($label) ?></span></a>
      <?php endforeach; ?>
    </nav>
    <div class="side-foot">
      <button type="button" class="theme-btn" data-theme-toggle><?= icon('moon', 18) ?><span>Dark mode</span></button>
      <form method="post" action="<?= url('logout') ?>"><?= csrf_field() ?><button class="linkish"><?= icon('logout', 18) ?><span>Sign out</span></button></form>
    </div>
  </aside>
  <div class="main">
    <header class="top">
      <a class="brand sm" href="<?= url('home') ?>"><img class="mark" src="assets/img/logo.svg" alt="" width="32" height="35"><b>Game <i>ALS</i></b></a>
      <div class="top-title"><?= e($title ?? '') ?></div>
      <button type="button" class="icon-btn" data-theme-toggle aria-label="Switch light or dark"><?= icon('moon', 18) ?></button>
      <div class="who"><span class="av"><?= e(mb_strtoupper(mb_substr($u['first_name'] ?? '?', 0, 1))) ?></span><span class="who-t"><b><?= e(full_name($u)) ?></b><small><?= e(ucfirst($u['role'])) ?></small></span></div>
    </header>
    <main class="content">
      <?php foreach (flash() as [$t, $m]): ?><div class="flash <?= e($t) ?>"><?= e($m) ?></div><?php endforeach; ?>
      <?php require $view_file; ?>
    </main>
  </div>
  <nav class="tabbar">
    <?php foreach (array_slice($nav, 0, 5) as [$p, $label, $ic]): ?>
      <a href="<?= url($p) ?>" class="<?= $cur === $p ? 'on' : '' ?>"><?= icon($ic, 22) ?><span><?= e($label) ?></span></a>
    <?php endforeach; ?>
  </nav>
</div>
<?php endif; ?>
<?php foreach ($scripts ?? [] as $js): ?><script src="assets/js/<?= e($js) ?>?v=3"></script><?php endforeach; ?>
</body>
</html>
