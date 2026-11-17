<?php
$u = me();
$bare = $bare ?? !$u;
$navGroups = [];
if ($u) {
    if ($u['role'] === 'learner') $navGroups = ['' => [['home', 'Home', 'home']], 'Play' => [['certs', 'Certificates', 'award'], ['board', 'Leaderboard', 'trophy']], 'Account' => [['account', 'My account', 'user']]];
    if ($u['role'] === 'teacher') $navGroups = ['' => [['home', 'Home', 'home']], 'My learners' => [['learners', 'Learners', 'users'], ['passes', 'QR passes', 'qr'], ['board', 'Leaderboard', 'trophy']], 'Reports' => [['reports', 'Reports', 'chart']], 'Account' => [['account', 'My account', 'user']]];
    if ($u['role'] === 'admin') $navGroups = ['' => [['home', 'Home', 'home']], 'People' => [['teachers', 'Teachers', 'school'], ['learners', 'Learners', 'users'], ['passes', 'QR passes', 'qr']], 'Places' => [['places', 'Municipalities', 'city'], ['clcs', 'CLCs', 'map']], 'Monitoring' => [['questions', 'Questions', 'list'], ['reports', 'Reports', 'chart'], ['board', 'Leaderboard', 'trophy']], 'Account' => [['account', 'My account', 'user']]];
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
<link rel="stylesheet" href="assets/css/app.css?v=17">
<script src="assets/js/theme.js?v=5"></script>
</head>
<body class="<?= e($bodyClass ?? '') ?>" data-csrf="<?= e($_SESSION['csrf'] ?? '') ?>">
<?php if ($bare): require $view_file; else: ?>
<div class="shell">
  <aside class="side">
    <a class="brand" href="<?= url('home') ?>"><img class="mark" src="assets/img/logo.svg" alt="" width="40" height="43"><span><b>Game <i>ALS</i></b><small>Alternative Learning System</small></span></a>
    <label class="side-search"><?= icon('search', 17) ?><input type="search" placeholder="Search" aria-label="Search the menu" data-navsearch></label>
    <nav>
      <?php foreach ($navGroups as $gname => $items): ?><?php if ($gname): ?><p class="nav-h"><?= e(strtoupper($gname)) ?></p><?php endif; ?>
        <?php foreach ($items as [$p, $label, $ic]): ?><a href="<?= url($p) ?>" class="<?= $cur === $p ? 'on' : '' ?>" data-navitem><span class="ni"><?= icon($ic, 18) ?></span><span><?= e($label) ?></span></a><?php endforeach; ?>
      <?php endforeach; ?>
    </nav>
    <div class="side-foot">
      <button type="button" class="theme-btn" data-theme-toggle><?= icon('moon', 18) ?><span>Dark mode</span></button>
      <form method="post" action="<?= url('logout') ?>"><?= csrf_field() ?><button class="linkish"><?= icon('logout', 18) ?><span>Sign out</span></button></form>
    </div>
  </aside>
  <div class="main">
    <header class="top">
      <button type="button" class="icon-btn menu-btn" data-menu aria-label="Open menu"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h10"/></svg></button>
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
  <div class="menu-shade" data-menu-close></div>
</div>
<?php endif; ?>
<?php foreach ($scripts ?? [] as $js): ?><script src="assets/js/<?= e($js) ?>?v=6"></script><?php endforeach; ?>
</body>
</html>
