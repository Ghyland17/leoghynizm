<?php
declare(strict_types=1);
if (!defined('FES')) { http_response_code(404); exit; }

/* ================= config & database ================= */
function cfg(string $k, $def = null) {
    static $c = null;
    if ($c === null) {
        $f = getenv('FES_CONFIG') ?: __DIR__ . '/config.php';
        if (!is_file($f)) fail(500, 'not_configured', 'Server belum dikonfigurasi. Salin config.sample.php menjadi config.php.');
        $c = require $f;
    }
    return $c[$k] ?? $def;
}
function db(): PDO {
    static $p = null;
    if ($p) return $p;
    $p = new PDO(cfg('db_dsn'), cfg('db_user'), cfg('db_pass'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ]);
    if (str_starts_with(cfg('db_dsn'), 'mysql')) $p->exec("SET NAMES utf8mb4");
    return $p;
}
function q(string $sql, array $a = []): PDOStatement { $s = db()->prepare($sql); $s->execute($a); return $s; }
function row(string $sql, array $a = []): ?array { $r = q($sql, $a)->fetch(); return $r ?: null; }
function rows(string $sql, array $a = []): array { return q($sql, $a)->fetchAll(); }
function ms(): int { return (int) floor(microtime(true) * 1000); }

/* ================= request & response ================= */
function out($data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function fail(int $code, string $err, string $msg = '', array $extra = []): never {
    out(['ok' => false, 'error' => $err, 'message' => $msg] + $extra, $code);
}
function body(): array {
    static $b = null;
    if ($b !== null) return $b;
    $raw = file_get_contents('php://input') ?: '';
    if (strlen($raw) > 8 * 1024 * 1024) fail(413, 'too_large', 'Data terlalu besar.');
    $b = $raw === '' ? [] : json_decode($raw, true);
    if (!is_array($b)) fail(400, 'bad_json', 'Format data tidak valid.');
    return $b;
}
function str_in(array $b, string $k, int $max, bool $req = false): string {
    $v = $b[$k] ?? '';
    if (!is_string($v) && !is_int($v)) fail(422, 'invalid', "Isian $k tidak valid.");
    $v = trim((string) $v);
    if ($req && $v === '') fail(422, 'required', "Isian $k wajib diisi.", ['field' => $k]);
    if (mb_strlen($v) > $max) fail(422, 'too_long', "Isian $k terlalu panjang (maks $max karakter).", ['field' => $k]);
    return $v;
}
function client_ip(): string { return substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45); }
function ua(): string { return substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 200); }

/* ================= security helpers ================= */
function token(int $bytes = 32): string { return rtrim(strtr(base64_encode(random_bytes($bytes)), '+/', '-_'), '='); }
function th(string $t): string { return hash('sha256', $t); }
function rate(string $key, int $max, int $windowSec): void {
    $now = ms();
    q('DELETE FROM rate_hits WHERE at < ?', [$now - 86400000]);
    $n = (int) row('SELECT COUNT(*) n FROM rate_hits WHERE k=? AND at>?', [$key, $now - $windowSec * 1000])['n'];
    if ($n >= $max) fail(429, 'rate_limited', 'Terlalu banyak permintaan. Coba lagi beberapa menit lagi.');
    q('INSERT INTO rate_hits (k, at) VALUES (?, ?)', [$key, $now]);
}
function audit(?array $u, string $action): void {
    q('INSERT INTO audit (at, actor_id, actor_name, action, ip) VALUES (?,?,?,?,?)',
      [ms(), $u['id'] ?? null, $u ? ($u['name'] . ' (' . role_label($u) . ')') : 'Sistem', mb_substr($action, 0, 300), client_ip()]);
}
function setting(string $k, $def = null) { $r = row('SELECT v FROM settings WHERE k=?', [$k]); return $r ? json_decode($r['v'], true) : $def; }
function set_setting(string $k, $v): void {
    $j = json_encode($v, JSON_UNESCAPED_UNICODE);
    if (row('SELECT k FROM settings WHERE k=?', [$k])) q('UPDATE settings SET v=? WHERE k=?', [$j, $k]);
    else q('INSERT INTO settings (k, v) VALUES (?, ?)', [$k, $j]);
}

/* ================= users & roles ================= */
const ROLES = ['crew', 'tl', 'koord', 'admin', 'super'];
const SQUADS = ['Alpha', 'Bravo', 'Charlie', 'Delta'];
const ACCESS = ['app' => ['crew', 'tl', 'koord', 'admin', 'super'], 'dash' => ['tl', 'koord', 'admin', 'super'], 'admin' => ['admin', 'super']];
const RESPONSE_ROLES = ['crew', 'tl'];
const TWO_STEP = ['admin', 'super'];
const APPROVERS = ['koord', 'super'];

function role_label(array $u): string {
    $sq = $u['squad'] ? ' · Regu ' . $u['squad'] : '';
    return match ($u['rk']) {
        'crew' => 'Tim FES' . $sq, 'tl' => 'Team Leader' . $sq, 'koord' => 'Koordinator FES',
        'admin' => 'Admin FES', 'super' => 'Super Admin', default => $u['rk'],
    };
}
function find_user(string $v): ?array {
    $v = strtolower(trim($v));
    if ($v === '') return null;
    return row('SELECT * FROM users WHERE LOWER(id)=? OR LOWER(email)=?', [$v, $v]);
}
function public_user(array $u, bool $full = false): array {
    $o = ['id' => $u['id'], 'name' => $u['name'], 'role' => role_label($u), 'rk' => $u['rk'], 'squad' => $u['squad'],
          'station' => $u['station'], 'photo' => $u['photo'], 'must_change' => (bool) $u['must_change']];
    if ($full) $o += ['email' => $u['email'], 'phone' => $u['phone'], 'blood' => $u['blood'], 'birth' => $u['birth'] ?? null, 'address' => $u['address'],
                      'ec_name' => $u['ec_name'], 'ec_phone' => $u['ec_phone'], 'status' => $u['status'],
                      'hours' => $u['hours'] ? json_decode($u['hours'], true) : null, 'last_login_at' => $u['last_login_at'],
                      'last_device' => $u['last_device']];
    return $o;
}
function mask_email(string $e): string { [$a, $b] = array_pad(explode('@', $e, 2), 2, ''); return mb_substr($a, 0, 2) . '•••' . mb_substr($a, -1) . '@' . $b; }
function password_problems(string $p, string $id): array {
    $m = [];
    if (strlen($p) < 8) $m[] = 'minimal 8 karakter';
    if (!preg_match('/[a-z]/i', $p) || !preg_match('/\d/', $p)) $m[] = 'gabungan huruf dan angka';
    if (!preg_match('/[A-Z]/', $p)) $m[] = 'minimal satu huruf besar';
    if (stripos($p, $id) !== false) $m[] = 'tidak boleh memuat ID karyawan';
    if (strlen($p) > 128) $m[] = 'maksimal 128 karakter';
    return $m;
}
function hash_pw(string $p): string { return password_hash($p, defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT); }

/* ================= sessions ================= */
const WEB_IDLE_MS = 3600000;          // web: keluar otomatis setelah 1 jam tidak aktif
const APP_TTL_MS  = 180 * 86400000;   // aplikasi HP: tetap masuk sampai Keluar (diperbarui saat dipakai)

function bearer(): string {
    $h = $_SERVER['HTTP_X_FES_TOKEN'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if (stripos($h, 'Bearer ') === 0) $h = substr($h, 7);
    return preg_match('/^[A-Za-z0-9_-]{30,80}$/', $h) ? $h : '';
}
function new_session(array $u, string $client): string {
    $t = token(); $now = ms();
    $exp = $client === 'app' ? $now + APP_TTL_MS : $now + WEB_IDLE_MS;
    q('INSERT INTO sessions (token_hash,user_id,client,created_at,last_seen,expires_at,ip,ua) VALUES (?,?,?,?,?,?,?,?)',
      [th($t), $u['id'], $client, $now, $now, $exp, client_ip(), ua()]);
    q('DELETE FROM sessions WHERE expires_at < ?', [$now]);
    q('UPDATE users SET last_login_at=?, last_device=?, failed_count=0, locked_until=0 WHERE id=?', [$now, mb_substr(ua(), 0, 120), $u['id']]);
    return $t;
}
/** Returns the signed-in user, or stops with 401. Also slides the idle window. */
function auth(array $roles = []): array {
    static $cur = null;
    if ($cur === null) {
        $t = bearer();
        if ($t === '') fail(401, 'no_session', 'Silakan masuk terlebih dahulu.');
        $s = row('SELECT * FROM sessions WHERE token_hash=?', [th($t)]);
        $now = ms();
        if (!$s || $s['expires_at'] < $now) { if ($s) q('DELETE FROM sessions WHERE token_hash=?', [$s['token_hash']]); fail(401, 'session_expired', 'Sesi berakhir. Silakan masuk kembali.'); }
        $u = row('SELECT * FROM users WHERE id=?', [$s['user_id']]);
        if (!$u || $u['status'] !== 'active') { q('DELETE FROM sessions WHERE token_hash=?', [$s['token_hash']]); fail(401, 'account_disabled', 'Akun tidak aktif. Hubungi Super Admin IT.'); }
        if ($now - $s['last_seen'] > 30000) {
            $exp = $s['client'] === 'app' ? $now + APP_TTL_MS : $now + WEB_IDLE_MS;
            q('UPDATE sessions SET last_seen=?, expires_at=? WHERE token_hash=?', [$now, $exp, $s['token_hash']]);
        }
        global $path;
        if ($u['must_change'] && !in_array($path ?? '', ['/auth/me', '/auth/logout', '/me/password', '/me/profile', '/me/photo'], true))
            fail(403, 'must_change', 'Ganti kata sandi awal Anda dulu di Pengaturan akun.');
        $u['_session'] = $s;
        $cur = $u;
    }
    if ($roles && !in_array($cur['rk'], $roles, true)) fail(403, 'forbidden', 'Peran Anda tidak memiliki akses ke fitur ini.');
    return $cur;
}

/* ================= email ================= */
function send_mail(string $to, string $subject, string $text): bool {
    $driver = cfg('mail_driver', 'mail');
    $from = cfg('mail_from', 'no-reply@localhost'); $fromName = cfg('mail_from_name', 'FES Emergency Report');
    if ($driver === 'log') {   // hanya untuk pengujian lokal
        $f = cfg('mail_log', sys_get_temp_dir() . '/fes-mail.log');
        file_put_contents($f, json_encode(['to' => $to, 'subject' => $subject, 'text' => $text, 'at' => ms()]) . "\n", FILE_APPEND | LOCK_EX);
        return true;
    }
    $subjEnc = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $headers = "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <$from>\r\nMIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n";
    if ($driver === 'smtp') return smtp_send($to, $subjEnc, $headers, chunk_split(base64_encode($text)));
    return mail($to, $subjEnc, chunk_split(base64_encode($text)), $headers);
}
function smtp_send(string $to, string $subj, string $headers, string $body): bool {
    $host = cfg('smtp_host'); $port = (int) cfg('smtp_port', 465); $secure = cfg('smtp_secure', 'ssl');
    $fp = @stream_socket_client(($secure === 'ssl' ? 'ssl://' : 'tcp://') . "$host:$port", $en, $es, 15);
    if (!$fp) { error_log("FES smtp connect: $es"); return false; }
    stream_set_timeout($fp, 15);
    $read = function () use ($fp) { $d = ''; while (($l = fgets($fp, 515)) !== false) { $d .= $l; if (isset($l[3]) && $l[3] === ' ') break; } return $d; };
    $cmd = function (string $c, array $okCodes) use ($fp, $read) { fwrite($fp, $c . "\r\n"); $r = $read(); if (!in_array(substr($r, 0, 3), $okCodes, true)) throw new RuntimeException('SMTP ' . trim($r)); return $r; };
    try {
        $read();
        $cmd('EHLO fes.local', ['250']);
        if ($secure === 'tls') { $cmd('STARTTLS', ['220']); stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT); $cmd('EHLO fes.local', ['250']); }
        $cmd('AUTH LOGIN', ['334']); $cmd(base64_encode((string) cfg('smtp_user')), ['334']); $cmd(base64_encode((string) cfg('smtp_pass')), ['235']);
        $cmd('MAIL FROM:<' . cfg('mail_from') . '>', ['250']); $cmd("RCPT TO:<$to>", ['250', '251']); $cmd('DATA', ['354']);
        $msg = "To: <$to>\r\nSubject: $subj\r\nDate: " . date('r') . "\r\n" . $headers . "\r\n" . str_replace("\n.", "\n..", $body);
        $cmd($msg . "\r\n.", ['250']); $cmd('QUIT', ['221']);
        fclose($fp); return true;
    } catch (Throwable $e) { error_log('FES smtp: ' . $e->getMessage()); fclose($fp); return false; }
}

/* ================= defaults & upgrades ================= */
const DEFAULT_UNITS = [['k' => 'FS R6', 'type' => 'Rescue Tender', 'st' => 'FOX-5', 'status' => 'Siap'], ['k' => 'FS E8', 'type' => 'Fire Engine', 'st' => 'FOX-5', 'status' => 'Siap'],
                       ['k' => 'EMS6', 'type' => 'Ambulans', 'st' => 'FOX-4', 'status' => 'Siap'], ['k' => 'FS R4', 'type' => 'Rescue Tender', 'st' => 'FOX-3', 'status' => 'Siap']];
const DEFAULT_MASTER = [
    'station' => ['FIRE STATION FAP FOX-5', 'FIRST AID STATION FOX-4', 'OPS. CAMP FOX-3', 'GROUND TRAINING DOJO', 'CONSTRUCTION OFFICE FOX-2', 'ON CALL', 'CO/MGH', 'NAR'],
    'lokasi' => ['Camp Tetenggala', 'Camp Manado', 'MCO', 'Main Gate', 'Dormi / FOX-4', 'Camp Pad PAMA', 'Ground Training DOJO', 'Gate NAR', 'Construction Office', 'Desa Watalara', 'Desa Silea', 'Puulemo'],
    'sumber' => ['ERT Coordinator', 'Radio', 'Telepon', 'Security', 'Masyarakat'],
    'instansi' => ['BPBD Kolaka', 'Damkar Kolaka', 'Puskesmas', 'Polsek']];
const DEFAULT_MIN = ['FOX-5' => 3, 'FOX-4' => 3, 'FOX-3' => 3, 'FOX-2' => 2];
const DEFAULT_SYSTEM = ['otp_minutes' => 5, 'otp_tries' => 5, 'min_app_version' => '2.0.0'];
const SCHEMA_VERSION = 2;
/** Brings an older database up to date (new tables only; never drops data). */
function migrate(): void {
    $v = (int) (setting('schema_version', 1));
    if ($v >= SCHEMA_VERSION) return;
    $sql = preg_replace('/--[^\n]*/', '', file_get_contents(__DIR__ . '/schema.sql'));
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) if (stripos($stmt, 'CREATE TABLE IF NOT EXISTS') === 0) db()->exec($stmt);
    $cols = array_column(rows('SHOW COLUMNS FROM users'), 'Field');
    if (!in_array('birth', $cols, true)) db()->exec('ALTER TABLE users ADD COLUMN birth VARCHAR(10) NULL AFTER blood');
    set_setting('schema_version', SCHEMA_VERSION);
}
function sys_setting(string $k): int { $s = setting('system', []) ?: []; $v = (int) ($s[$k] ?? DEFAULT_SYSTEM[$k]);
    return $k === 'otp_minutes' ? max(3, min(15, $v)) : max(3, min(10, $v)); }
