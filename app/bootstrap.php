<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
$cfgFile = ROOT . '/config/config.php';
if (!is_file($cfgFile)) {
    http_response_code(500);
    exit('Game ALS is not set up yet. Copy config/config.sample.php to config/config.php and add your database details.');
}
$CFG = require $cfgFile;
foreach (['db_host' => 'GAMEALS_DB_HOST', 'db_name' => 'GAMEALS_DB_NAME', 'db_user' => 'GAMEALS_DB_USER', 'db_pass' => 'GAMEALS_DB_PASS'] as $k => $env) {
    if (getenv($env) !== false) $CFG[$k] = getenv($env);
}
date_default_timezone_set($CFG['timezone'] ?? 'Asia/Manila');
ini_set('display_errors', !empty($CFG['debug']) ? '1' : '0');
error_reporting(E_ALL);

const STRANDS = [
    'LS1-EN'  => ['LS1', 'English', 'Communication Skills – English', '#2b4acb', 'book'],
    'LS1-FIL' => ['LS1', 'Filipino', 'Communication Skills – Filipino', '#0f8b8d', 'chat'],
    'LS2'     => ['LS2', 'Science', 'Scientific and Critical Thinking Skills', '#2e8b3a', 'flask'],
    'LS3'     => ['LS3', 'Math', 'Mathematical and Problem Solving Skills', '#d98e04', 'calc'],
    'LS4'     => ['LS4', 'Life & Career', 'Life and Career Skills', '#c62828', 'brief'],
    'LS5'     => ['LS5', 'Sarili at Lipunan', 'Pag-unawa sa Sarili at Lipunan', '#7b3fa0', 'people'],
    'LS6'     => ['LS6', 'Digital', 'Digital Citizenship', '#0277bd', 'laptop'],
];
const APP_FULL_NAME = 'Gamified Assessment for Mastery and Excellence in ALS';
const LEVELS = [1 => 'Easy', 2 => 'Medium', 3 => 'Difficult'];
const GRADES = ['ELEM' => 'Elementary', 'JHS' => 'Junior High School'];
const PASS_MARK = 0.75;
const START_LIVES = 3;
const MAX_LIVES = 5;
const HINTS_PER_ACTIVITY = 3;
const STREAK_FOR_LIFE = 5;

function db(): PDO {
    static $pdo = null;
    global $CFG;
    if ($pdo === null) {
        $pdo = new PDO('mysql:host=' . $CFG['db_host'] . ';dbname=' . $CFG['db_name'] . ';charset=utf8mb4', $CFG['db_user'], $CFG['db_pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $pdo->exec("SET time_zone = '+08:00'");
    }
    return $pdo;
}
function q(string $sql, array $p = []): PDOStatement { $s = db()->prepare($sql); $s->execute($p); return $s; }
function one(string $sql, array $p = []) { return q($sql, $p)->fetch(); }
function all(string $sql, array $p = []): array { return q($sql, $p)->fetchAll(); }
function val(string $sql, array $p = []) { return q($sql, $p)->fetchColumn(); }

function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function url(string $page, array $args = []): string {
    return 'index.php?' . http_build_query(['p' => $page] + $args);
}
function redirect(string $page, array $args = []): void { header('Location: ' . url($page, $args)); exit; }
function flash(?string $msg = null, string $type = 'ok') {
    if ($msg !== null) { $_SESSION['flash'][] = [$type, $msg]; return null; }
    $f = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $f;
}
function setting(string $k, string $def = ''): string {
    static $c = null;
    if ($c === null) { $c = []; foreach (all('SELECT k, v FROM settings') as $r) $c[$r['k']] = $r['v']; }
    return $c[$k] ?? $def;
}

// ---------- session, CSRF ----------
function start_session(): void {
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_name('gameals');
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $secure, 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">'; }
function check_csrf(): void {
    $t = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF'] ?? '');
    if (!is_string($t) || !hash_equals($_SESSION['csrf'], $t)) {
        http_response_code(419);
        exit('Your session expired. Please go back and try again.');
    }
}

// ---------- auth ----------
function me(): ?array {
    static $u = false;
    if ($u === false) {
        $u = null;
        if (!empty($_SESSION['uid'])) {
            $u = one('SELECT * FROM users WHERE id = ? AND active = 1', [$_SESSION['uid']]) ?: null;
            if (!$u) unset($_SESSION['uid']);
        }
    }
    return $u;
}
function need(string ...$roles): array {
    $u = me();
    if (!$u) redirect('login');
    if ($roles && !in_array($u['role'], $roles, true)) { http_response_code(403); render('error', ['title' => 'Not allowed', 'msg' => 'This page is not for your account.']); exit; }
    return $u;
}
function full_name(array $u): string {
    $m = trim($u['middle_name'] ?? '');
    return trim($u['first_name'] . ' ' . ($m !== '' ? mb_substr($m, 0, 1) . '. ' : '') . $u['last_name']);
}
function teacher_clc_ids(int $tid): array {
    return array_map('intval', array_column(all('SELECT clc_id FROM teacher_clc WHERE teacher_id = ?', [$tid]), 'clc_id'));
}
function can_manage_learner(array $u, array $learner): bool {
    if ($learner['role'] !== 'learner') return false;
    if ($u['role'] === 'admin') return true;
    return $u['role'] === 'teacher' && in_array((int)$learner['clc_id'], teacher_clc_ids((int)$u['id']), true);
}

// ---------- views ----------
function render(string $view, array $vars = []): void {
    extract($vars);
    $view_file = ROOT . '/app/views/' . $view . '.php';
    require ROOT . '/app/views/layout.php';
}
function icon(string $n, int $s = 20): string {
    $p = [
        'book' => '<path d="M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2z"/><path d="M4 21V5"/><path d="M8 7h7"/>',
        'chat' => '<path d="M4 5h16v11H9l-5 4z"/><path d="M8 10h8"/>',
        'flask' => '<path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 2 3h10a2 2 0 0 0 2-3l-5-9V3"/><path d="M7.5 15h9"/>',
        'calc' => '<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M8 7h8M8 12h2M14 12h2M8 16h2M14 16h2"/>',
        'brief' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2M3 13h18"/>',
        'people' => '<circle cx="9" cy="8" r="3"/><path d="M3 20a6 6 0 0 1 12 0"/><circle cx="17" cy="9" r="2.5"/><path d="M16 14a5 5 0 0 1 5 6"/>',
        'laptop' => '<rect x="4" y="5" width="16" height="11" rx="1.5"/><path d="M2 19h20"/>',
        'home' => '<path d="M3 11l9-7 9 7"/><path d="M5 10v10h14V10"/><path d="M10 20v-6h4v6"/>',
        'trophy' => '<path d="M8 4h8v5a4 4 0 0 1-8 0z"/><path d="M8 6H5a3 3 0 0 0 3 4M16 6h3a3 3 0 0 1-3 4M12 13v4M8 21h8M9 17h6"/>',
        'award' => '<circle cx="12" cy="9" r="6"/><path d="M8.5 14L7 22l5-3 5 3-1.5-8"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'users' => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 14a6 6 0 0 1 3.5 6"/>',
        'school' => '<path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11v5c0 1.5 3 3 6 3s6-1.5 6-3v-5"/>',
        'map' => '<path d="M9 4L3 6v14l6-2 6 2 6-2V4l-6 2z"/><path d="M9 4v14M15 6v14"/>',
        'chart' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        'list' => '<path d="M8 6h13M8 12h13M8 18h13"/><circle cx="4" cy="6" r="1"/><circle cx="4" cy="12" r="1"/><circle cx="4" cy="18" r="1"/>',
        'edit' => '<path d="M4 20h4L19 9l-4-4L4 16z"/><path d="M13 7l4 4"/>',
        'upload' => '<path d="M12 16V4M7 9l5-5 5 5"/><path d="M4 16v4h16v-4"/>',
        'download' => '<path d="M12 4v12M7 11l5 5 5-5"/><path d="M4 20h16"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'logout' => '<path d="M15 4h4v16h-4"/><path d="M10 17l-5-5 5-5M5 12h11"/>',
        'key' => '<circle cx="8" cy="15" r="4"/><path d="M11 12l9-9M16 7l3 3"/>',
        'lock' => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
        'check' => '<path d="M5 12l5 5L20 7"/>',
        'x' => '<path d="M18 6L6 18M6 6l12 12"/>',
        'heart' => '<path d="M12 20s-7-4.5-9-9a4.5 4.5 0 0 1 9-2 4.5 4.5 0 0 1 9 2c-2 4.5-9 9-9 9z"/>',
        'bulb' => '<path d="M9 18h6M10 21h4"/><path d="M12 3a6 6 0 0 0-4 10.5c.7.7 1 1.5 1 2.5h6c0-1 .3-1.8 1-2.5A6 6 0 0 0 12 3z"/>',
        'flame' => '<path d="M12 3c1 4 5 5.5 5 10a5 5 0 0 1-10 0c0-2 1-3.5 2-4.5 0 2 1 3 2 3 0-3-1-5.5 1-8.5z"/>',
        'star' => '<path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9z"/>',
        'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
        'moon' => '<path d="M21 13A9 9 0 1 1 11 3a7 7 0 0 0 10 10z"/>',
        'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'back' => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
        'play' => '<path d="M7 4l13 8-13 8z"/>',
        'print' => '<path d="M6 9V3h12v6"/><rect x="3" y="9" width="18" height="8" rx="2"/><path d="M7 14h10v7H7z"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/>',
        'game' => '<rect x="2.5" y="7" width="19" height="11" rx="5"/><path d="M7.5 10.5v4M5.5 12.5h4"/><circle cx="15.5" cy="11" r=".6"/><circle cx="17.5" cy="14" r=".6"/>',
        'refresh' => '<path d="M20 11a8 8 0 1 0-2.3 5.7"/><path d="M20 4v7h-7"/>',
        'qr' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3M21 14v.01M14 21h3M21 18v3h-1M17 17h.01"/>',
        'pin' => '<circle cx="6" cy="6" r="1.4"/><circle cx="12" cy="6" r="1.4"/><circle cx="18" cy="6" r="1.4"/><circle cx="6" cy="12" r="1.4"/><circle cx="12" cy="12" r="1.4"/><circle cx="18" cy="12" r="1.4"/><circle cx="12" cy="18" r="1.4"/>',
        'city' => '<path d="M3 21h18M5 21V8l5-3v16M10 21V3h9v18"/><path d="M13 7h3M13 11h3M13 15h3M7 11h1M7 15h1"/>',
        'camera' => '<path d="M4 8h3l2-3h6l2 3h3v11H4z"/><circle cx="12" cy="13" r="3.5"/>',
        'zoom' => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4M11 8v6M8 11h6"/>',
    ][$n] ?? '';
    return '<svg class="ic" width="' . $s . '" height="' . $s . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
}

function make_password(): string {
    $w = ['Mango', 'River', 'Sunny', 'Tiger', 'Eagle', 'Lemon', 'Cloud', 'Star', 'Coral', 'Maple', 'Pearl', 'Ocean', 'Bamboo', 'Falcon'];
    return $w[random_int(0, count($w) - 1)] . random_int(1000, 9999) . '!';
}

/** CLCs this user may manage learners in. */
function my_clcs(array $u): array {
    if ($u['role'] === 'admin') return all('SELECT * FROM clcs ORDER BY name');
    $ids = teacher_clc_ids((int)$u['id']);
    return $ids ? all('SELECT * FROM clcs WHERE id IN (' . implode(',', $ids) . ') ORDER BY name') : [];
}
function slug(string $s): string {
    $s = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s);
    return preg_replace('/[^a-z0-9]/', '', $s);
}
const ID_PREFIX = ['learner' => 'ALS', 'teacher' => 'TCH', 'admin' => 'ADM'];
/** Give a new account its ALS ID (e.g. ALS202600012). The ALS ID is also its first username. */
function assign_als_id(int $id, string $role): string {
    $aid = ID_PREFIX[$role] . date('Y') . str_pad((string)$id, 5, '0', STR_PAD_LEFT);
    q('UPDATE users SET als_id = ?, username = ? WHERE id = ?', [$aid, $aid, $id]);
    return $aid;
}
/** Add a learner. Returns [ALS ID, starter password]. */
function add_learner(array $by, array $d): array {
    $pw = make_password();
    q('INSERT INTO users (role, username, password_hash, first_name, middle_name, last_name, sex, birthdate, lrn, grade, clc_id, created_by) VALUES ("learner", ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
      ['new-' . bin2hex(random_bytes(8)), password_hash($pw, PASSWORD_DEFAULT), $d['first_name'], $d['middle_name'] ?? '', $d['last_name'], $d['sex'] ?? '', $d['birthdate'] ?: null, $d['lrn'] ?? '', $d['grade'], $d['clc_id'], $by['id']]);
    return [assign_als_id((int)db()->lastInsertId(), 'learner'), $pw];
}
/** Add a teacher or admin. Returns [id, ALS ID, starter password]. */
function add_staff(array $by, string $role, array $d): array {
    $pw = make_password();
    q('INSERT INTO users (role, username, password_hash, first_name, middle_name, last_name, email, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
      [$role, 'new-' . bin2hex(random_bytes(8)), password_hash($pw, PASSWORD_DEFAULT), $d['first_name'], $d['middle_name'] ?? '', $d['last_name'], $d['email'] ?? '', $by['id']]);
    $id = (int)db()->lastInsertId();
    return [$id, assign_als_id($id, $role), $pw];
}
/** A user-chosen username: 3–30 letters, numbers, dot, dash or underscore, and not shaped like an ALS ID. */
function username_problem(string $un, int $self): string {
    if (!preg_match('/^[a-z0-9][a-z0-9._-]{2,29}$/', $un)) return 'Use 3 to 30 small letters, numbers, dot, dash or underscore. Start with a letter or number.';
    if (preg_match('/^(als|tch|adm)\d/', $un)) return 'That looks like an ALS ID. Pick a different username.';
    if (val('SELECT 1 FROM users WHERE (username = ? OR als_id = ?) AND id <> ?', [$un, $un, $self])) return 'That username is already taken. Try another one.';
    return '';
}
function pin_ok(string $pin): bool { return (bool)preg_match('/^\d{4}$/', $pin) && !in_array($pin, ['0000', '1234', '1111', '4321'], true); }
/** The sign-in token printed on a user's QR pass. Made the first time it is needed. */
function qr_token(int $uid, bool $fresh = false): string {
    $t = $fresh ? '' : (string)val('SELECT qr_token FROM users WHERE id = ?', [$uid]);
    if ($t === '') { $t = bin2hex(random_bytes(20)); q('UPDATE users SET qr_token = ? WHERE id = ?', [$t, $uid]); }
    return $t;
}
/** Full address of this app, for QR codes. */
function base_url(): string {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    return ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $dir . '/';
}

// ---------- municipalities and districts ----------
function municipalities(): array { return all('SELECT * FROM municipalities ORDER BY name'); }
function districts(): array { return all('SELECT d.*, m.name municipality FROM districts d JOIN municipalities m ON m.id = d.municipality_id ORDER BY m.name, d.name'); }
/** Read the municipality / district filter from the query string. */
function place_filter(): array { return ['mun' => (int)($_GET['mun'] ?? 0), 'dist' => (int)($_GET['dist'] ?? 0)]; }
/** Add SQL for a place filter on a CLC alias (e.g. "c"). */
function place_where(array $f, array &$where, array &$args, string $c = 'c'): void {
    if (!empty($f['dist'])) { $where[] = "$c.district_id = ?"; $args[] = $f['dist']; }
    elseif (!empty($f['mun'])) { $where[] = "$c.district_id IN (SELECT id FROM districts WHERE municipality_id = ?)"; $args[] = $f['mun']; }
}
/** Keep the plain-text municipality and district on each CLC in step with the lists. */
function sync_clc_places(): void {
    q('UPDATE clcs c LEFT JOIN districts d ON d.id = c.district_id LEFT JOIN municipalities m ON m.id = d.municipality_id
       SET c.municipality = COALESCE(m.name, ""), c.district = COALESCE(d.name, "")');
}
function proper(string $s): string { return mb_convert_case(mb_strtolower(trim(preg_replace('/\s+/', ' ', $s))), MB_CASE_TITLE, 'UTF-8'); }
