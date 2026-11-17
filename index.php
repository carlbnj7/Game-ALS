<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/game.php';
require __DIR__ . '/app/art.php';

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: same-origin');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; font-src 'self'; frame-ancestors 'self'; form-action 'self'; base-uri 'self'");

start_session();
renumber_ids_once();
$page = preg_replace('/[^a-z_]/', '', (string)($_GET['p'] ?? ''));
if ($page === '') $page = me() ? 'home' : 'login';
$file = ROOT . '/app/pages/' . $page . '.php';
if (!is_file($file)) { http_response_code(404); render('error', ['title' => 'Page not found', 'msg' => 'That page does not exist.']); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') check_csrf();
// First sign-in (or after a reset): make a password, a username and a 4-digit PIN before anything else.
$u = me();
if ($u && ($u['must_change'] || !$u['setup_done'] || $u['pin_hash'] === null) && !in_array($page, ['setup', 'logout', 'api'], true)) redirect('setup');
try {
    require $file;
} catch (Throwable $ex) {
    error_log('[Game ALS] ' . $ex->getMessage() . ' @ ' . $ex->getFile() . ':' . $ex->getLine());
    http_response_code(500);
    if ($page === 'api') { header('Content-Type: application/json'); echo json_encode(['error' => 'Something went wrong. Please try again.']); exit; }
    render('error', ['title' => 'Something went wrong', 'msg' => !empty($CFG['debug']) ? $ex->getMessage() : 'Please try again. If it keeps happening, tell your teacher or the admin.']);
}
