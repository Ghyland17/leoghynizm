<?php
/* FES Emergency Report — REST API (PHP 8.1+, MySQL/MariaDB). Semua permintaan masuk lewat file ini. */
declare(strict_types=1);
define('FES', true);
require __DIR__ . '/lib.php';

ini_set('display_errors', '0');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header('Cache-Control: no-store');
header("Content-Security-Policy: default-src 'none'; frame-ancestors 'none'");

/* CORS hanya untuk asal yang diizinkan (mis. aplikasi Android). Situs web sendiri tidak memerlukannya. */
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '' && in_array($origin, cfg('allowed_origins', []), true)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Vary: Origin');
    header('Access-Control-Allow-Headers: Content-Type, X-FES-Token, Authorization');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Max-Age: 600');
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') { http_response_code(204); exit; }

set_exception_handler(function (Throwable $e) {
    error_log('FES API: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    fail(500, 'server_error', 'Terjadi kesalahan di server. Coba lagi.');
});

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = $_GET['r'] ?? (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($base !== '' && str_starts_with($path, $base)) $path = substr($path, strlen($base));
$path = '/' . trim(preg_replace('#^/index\.php#', '', $path), '/');
if ($method !== 'GET' && $method !== 'DELETE' && !str_starts_with((string) ($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json'))
    fail(415, 'json_only', 'Gunakan Content-Type application/json.');

$R = [];
function route(string $m, string $p, callable $f): void { global $R; $R[] = [$m, '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $p) . '$#', $f]; }

/* =================================================================== AUTH */
route('GET', '/health', fn() => out(['ok' => true, 'time' => ms(), 'db' => (bool) row('SELECT 1 x')]));

route('POST', '/auth/login', function () {
    $b = body();
    $id = str_in($b, 'id', 120, true); $pw = (string) ($b['password'] ?? '');
    $client = ($b['client'] ?? 'web') === 'app' ? 'app' : 'web';
    $area = in_array($b['area'] ?? '', ['app', 'dash', 'admin'], true) ? $b['area'] : '';
    rate('login-ip:' . client_ip(), 30, 900);
    $u = find_user($id); $now = ms();
    if ($u && $u['locked_until'] > $now) fail(423, 'locked', 'Akun dikunci sementara karena terlalu banyak percobaan gagal.', ['until' => (int) $u['locked_until']]);
    if (!$u || $pw === '' || strlen($pw) > 128 || !password_verify($pw, $u['password_hash'])) {
        if (!$u) { password_verify($pw, hash_pw('fes-dummy-timing')); fail(401, 'bad_credentials', 'ID atau kata sandi salah.', ['left' => null]); }
        $f = $u['failed_count'] + 1;
        if ($f >= 5) { q('UPDATE users SET failed_count=0, locked_until=? WHERE id=?', [$now + 900000, $u['id']]); audit(null, "Akun {$u['id']} dikunci 15 menit (5x sandi salah)"); fail(423, 'locked', 'Akun dikunci 15 menit.', ['until' => $now + 900000]); }
        q('UPDATE users SET failed_count=? WHERE id=?', [$f, $u['id']]);
        fail(401, 'bad_credentials', 'ID atau kata sandi salah.', ['left' => 5 - $f]);
    }
    if ($u['status'] !== 'active') fail(403, 'inactive', 'Akun ini tidak aktif. Hubungi Super Admin IT.');
    if (password_needs_rehash($u['password_hash'], defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT)) q('UPDATE users SET password_hash=? WHERE id=?', [hash_pw($pw), $u['id']]);
    q('UPDATE users SET failed_count=0, locked_until=0 WHERE id=?', [$u['id']]);
    if ($area && !in_array($u['rk'], ACCESS[$area], true)) fail(403, 'denied', 'Peran Anda tidak memiliki akses ke halaman ini.', ['user' => ['name' => $u['name'], 'role' => role_label($u)]]);
    if (in_array($u['rk'], TWO_STEP, true)) {
        $trust = (string) ($b['trust'] ?? '');
        $ok = $trust !== '' && row('SELECT 1 x FROM trusted_devices WHERE token_hash=? AND user_id=? AND expires_at>?', [th($trust), $u['id'], $now]);
        if (!$ok) out(['ok' => true, 'otp' => start_otp($u, '2fa', $client), 'user' => ['id' => $u['id'], 'name' => $u['name'], 'role' => role_label($u)]]);
    }
    audit($u, "Masuk ($client)");
    out(['ok' => true, 'token' => new_session($u, $client), 'user' => public_user($u)]);
});

function start_otp(array $u, string $kind, string $client): array {
    $code = (string) random_int(100000, 999999); $id = bin2hex(random_bytes(16)); $now = ms();
    q('DELETE FROM otp_flows WHERE user_id=? AND kind=?', [$u['id'], $kind]);
    q('INSERT INTO otp_flows (id,user_id,kind,code_hash,expires_at,resend_at,client,created_at) VALUES (?,?,?,?,?,?,?,?)',
      [$id, $u['id'], $kind, password_hash($code, PASSWORD_DEFAULT), $now + 300000, $now + 60000, $client, $now]);
    otp_mail($u, $kind, $code);
    return ['flow' => $id, 'email' => mask_email($u['email']), 'expires_at' => $now + 300000, 'resend_at' => $now + 60000];
}
function otp_mail(array $u, string $kind, string $code): void {
    $what = $kind === '2fa' ? 'masuk (login dua langkah)' : 'mengatur ulang kata sandi';
    $ok = send_mail($u['email'], "Kode FES: $code", "Halo {$u['name']},\n\nKode untuk $what di FES Emergency Report:\n\n    $code\n\nKode berlaku 5 menit. Jangan berikan kode ini kepada siapa pun, termasuk petugas IT.\nJika Anda tidak meminta kode ini, abaikan email ini dan segera ganti kata sandi.\n\nFES Emergency Report");
    if (!$ok) fail(502, 'mail_failed', 'Kode tidak dapat dikirim ke email. Hubungi Super Admin IT.');
}
function load_flow(array $b): array {
    $id = (string) ($b['flow'] ?? '');
    if (!preg_match('/^[a-f0-9]{32}$/', $id)) fail(400, 'bad_flow', 'Sesi verifikasi tidak valid. Ulangi dari awal.');
    $f = row('SELECT * FROM otp_flows WHERE id=?', [$id]);
    if (!$f) fail(410, 'flow_gone', 'Sesi verifikasi sudah berakhir. Ulangi dari awal.');
    return $f;
}
route('POST', '/auth/otp/resend', function () {
    $f = load_flow(body()); $now = ms();
    if ($f['resend_at'] > $now) fail(429, 'wait', 'Tunggu sebentar sebelum kirim ulang.', ['resend_at' => (int) $f['resend_at']]);
    rate('otp-send:' . $f['user_id'], 6, 3600);
    $u = row('SELECT * FROM users WHERE id=?', [$f['user_id']]);
    $code = (string) random_int(100000, 999999);
    q('UPDATE otp_flows SET code_hash=?, tries=0, expires_at=?, resend_at=? WHERE id=?', [password_hash($code, PASSWORD_DEFAULT), $now + 300000, $now + 60000, $f['id']]);
    otp_mail($u, $f['kind'], $code);
    out(['ok' => true, 'expires_at' => $now + 300000, 'resend_at' => $now + 60000, 'email' => mask_email($u['email'])]);
});
route('POST', '/auth/otp/verify', function () {
    $b = body(); $f = load_flow($b); $code = preg_replace('/\D/', '', (string) ($b['code'] ?? '')); $now = ms();
    if ($f['expires_at'] < $now) fail(410, 'expired', 'Kode sudah kedaluwarsa. Tekan Kirim ulang.');
    if (!$f['code_hash']) fail(410, 'cancelled', 'Kode dibatalkan karena terlalu banyak salah. Tekan Kirim ulang.');
    if (strlen($code) !== 6 || !password_verify($code, $f['code_hash'])) {
        $t = $f['tries'] + 1;
        if ($t >= 5) { q('UPDATE otp_flows SET tries=?, code_hash=NULL, resend_at=0 WHERE id=?', [$t, $f['id']]); fail(401, 'cancelled', 'Kode salah 5 kali. Kode dibatalkan.', ['tries' => $t]); }
        q('UPDATE otp_flows SET tries=? WHERE id=?', [$t, $f['id']]);
        fail(401, 'bad_code', 'Kode salah.', ['tries' => $t]);
    }
    $u = row('SELECT * FROM users WHERE id=?', [$f['user_id']]);
    if (!$u || $u['status'] !== 'active') fail(403, 'inactive', 'Akun tidak aktif.');
    if ($f['kind'] === 'reset') { q('UPDATE otp_flows SET verified=1, code_hash=NULL, expires_at=? WHERE id=?', [$now + 600000, $f['id']]); out(['ok' => true, 'verified' => true]); }
    q('DELETE FROM otp_flows WHERE id=?', [$f['id']]);
    $res = ['ok' => true];
    if (!empty($b['trust_device'])) {
        $t = token(); q('INSERT INTO trusted_devices (token_hash,user_id,expires_at) VALUES (?,?,?)', [th($t), $u['id'], $now + 30 * 86400000]);
        q('DELETE FROM trusted_devices WHERE expires_at<?', [$now]); $res['trust'] = $t;
    }
    audit($u, "Masuk dengan kode dua langkah ({$f['client']})");
    out($res + ['token' => new_session($u, $f['client']), 'user' => public_user($u)]);
});
route('POST', '/auth/forgot', function () {
    $b = body(); $id = str_in($b, 'id', 120, true);
    rate('forgot-ip:' . client_ip(), 10, 3600);
    $u = find_user($id);
    if (!$u) fail(404, 'not_found', 'Akun tidak ditemukan. Periksa ID atau email.');
    if ($u['status'] !== 'active') fail(403, 'inactive', 'Akun ini belum aktif. Hubungi Super Admin IT.');
    rate('otp-send:' . $u['id'], 6, 3600);
    out(['ok' => true, 'otp' => start_otp($u, 'reset', ($b['client'] ?? '') === 'app' ? 'app' : 'web'), 'user' => ['id' => $u['id'], 'name' => $u['name']]]);
});
route('POST', '/auth/reset', function () {
    $b = body(); $f = load_flow($b);
    if ($f['kind'] !== 'reset' || !$f['verified'] || $f['expires_at'] < ms()) fail(403, 'not_verified', 'Verifikasi email dulu sebelum membuat kata sandi baru.');
    $pw = (string) ($b['password'] ?? ''); $p = password_problems($pw, $f['user_id']);
    if ($p) fail(422, 'weak_password', 'Kata sandi belum memenuhi syarat: ' . implode(', ', $p) . '.');
    q('UPDATE users SET password_hash=?, must_change=0, failed_count=0, locked_until=0, updated_at=? WHERE id=?', [hash_pw($pw), ms(), $f['user_id']]);
    q('DELETE FROM sessions WHERE user_id=?', [$f['user_id']]);
    q('DELETE FROM otp_flows WHERE id=?', [$f['id']]);
    audit(row('SELECT * FROM users WHERE id=?', [$f['user_id']]), 'Mengatur ulang kata sandi lewat email');
    out(['ok' => true]);
});
route('POST', '/auth/logout', function () { $u = auth(); q('DELETE FROM sessions WHERE token_hash=?', [$u['_session']['token_hash']]); out(['ok' => true]); });
route('GET', '/auth/me', function () { $u = auth(); out(['ok' => true, 'user' => public_user($u, true), 'client' => $u['_session']['client'], 'expires_at' => (int) $u['_session']['expires_at']]); });

/* =================================================================== AKUN SENDIRI */
route('PUT', '/me/profile', function () {
    $u = auth(); $b = body();
    $name = str_in($b, 'name', 80, true); $phone = str_in($b, 'phone', 20); $blood = str_in($b, 'blood', 4); $birth = str_in($b, 'birth', 10);
    if ($birth !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birth)) fail(422, 'invalid', 'Tanggal lahir tidak valid.', ['field' => 'birth']);
    $addr = str_in($b, 'address', 200); $ecn = str_in($b, 'ec_name', 80); $ecp = str_in($b, 'ec_phone', 20);
    $email = strtolower(str_in($b, 'email', 120, true));
    if (mb_strlen($name) < 3) fail(422, 'invalid', 'Nama minimal 3 huruf.', ['field' => 'name']);
    foreach (['phone' => $phone, 'ec_phone' => $ecp] as $k => $v) if ($v !== '' && !preg_match('/^(\+62|62|0)8\d{7,11}$/', preg_replace('/[\s-]/', '', $v))) fail(422, 'invalid', 'Nomor HP harus diawali 08 atau +628, 10–13 angka.', ['field' => $k]);
    if ($blood !== '' && !in_array($blood, ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], true)) fail(422, 'invalid', 'Golongan darah tidak valid.', ['field' => 'blood']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fail(422, 'invalid', 'Format email belum benar.', ['field' => 'email']);
    if ($email !== strtolower($u['email'])) {
        if (!password_verify((string) ($b['email_password'] ?? ''), $u['password_hash'])) fail(422, 'need_password', 'Masukkan kata sandi saat ini untuk mengganti email.', ['field' => 'email_password']);
        if (row('SELECT id FROM users WHERE LOWER(email)=? AND id<>?', [$email, $u['id']])) fail(409, 'email_taken', 'Email ini sudah dipakai akun lain.', ['field' => 'email']);
    }
    q('UPDATE users SET name=?, phone=?, blood=?, birth=?, address=?, ec_name=?, ec_phone=?, email=?, updated_at=? WHERE id=?',
      [$name, $phone ?: null, $blood ?: null, $birth ?: null, $addr ?: null, $ecn ?: null, $ecp ?: null, $email, ms(), $u['id']]);
    audit($u, 'Memperbarui data akun');
    out(['ok' => true, 'user' => public_user(row('SELECT * FROM users WHERE id=?', [$u['id']]), true)]);
});
function clean_image(string $d, int $maxBytes = 1500000): string {
    if (!preg_match('#^data:image/(jpeg|png|webp);base64,([A-Za-z0-9+/=]+)$#', $d, $m)) fail(422, 'bad_image', 'File harus gambar JPG, PNG, atau WebP.');
    $bin = base64_decode($m[2], true);
    if ($bin === false || strlen($bin) > $maxBytes) fail(422, 'bad_image', 'Ukuran gambar terlalu besar (maks ' . round($maxBytes / 1e6, 1) . ' MB).');
    $info = @getimagesizefromstring($bin);
    if (!$info || !in_array($info['mime'], ['image/jpeg', 'image/png', 'image/webp'], true)) fail(422, 'bad_image', 'File bukan gambar yang valid.');
    return 'data:' . $info['mime'] . ';base64,' . base64_encode($bin);
}
route('PUT', '/me/photo', function () {
    $u = auth(); $d = (string) (body()['photo'] ?? '');
    $photo = $d === '' ? null : clean_image($d, 400000);
    q('UPDATE users SET photo=?, updated_at=? WHERE id=?', [$photo, ms(), $u['id']]);
    out(['ok' => true, 'photo' => $photo]);
});
route('PUT', '/me/password', function () {
    $u = auth(); $b = body();
    if (!password_verify((string) ($b['current'] ?? ''), $u['password_hash'])) { rate('pwchange:' . $u['id'], 10, 900); fail(422, 'wrong_current', 'Kata sandi saat ini salah.', ['field' => 'current']); }
    $pw = (string) ($b['new'] ?? ''); $p = password_problems($pw, $u['id']);
    if ($p) fail(422, 'weak_password', 'Kata sandi baru belum memenuhi syarat: ' . implode(', ', $p) . '.', ['field' => 'new']);
    if (password_verify($pw, $u['password_hash'])) fail(422, 'same_password', 'Kata sandi baru harus berbeda dari yang sekarang.', ['field' => 'new']);
    q('UPDATE users SET password_hash=?, must_change=0, updated_at=? WHERE id=?', [hash_pw($pw), ms(), $u['id']]);
    q('DELETE FROM sessions WHERE user_id=? AND token_hash<>?', [$u['id'], $u['_session']['token_hash']]);
    audit($u, 'Mengganti kata sandi');
    out(['ok' => true]);
});

/* =================================================================== ANGGOTA (ADMIN) */
route('GET', '/users', function () {
    auth(['admin', 'super', 'koord', 'tl']);
    out(['ok' => true, 'users' => array_map(fn($u) => public_user($u, true), rows('SELECT * FROM users ORDER BY FIELD(rk,"super","admin","koord","tl","crew"), squad, name'))]);
});
function validate_member(array $b, array $me, ?array $target): array {
    $o = [];
    if (array_key_exists('name', $b)) { $o['name'] = str_in($b, 'name', 80, true); if (mb_strlen($o['name']) < 3) fail(422, 'invalid', 'Nama minimal 3 huruf.', ['field' => 'name']); }
    if (array_key_exists('email', $b)) { $o['email'] = strtolower(str_in($b, 'email', 120, true)); if (!filter_var($o['email'], FILTER_VALIDATE_EMAIL)) fail(422, 'invalid', 'Format email belum benar.', ['field' => 'email']);
        if (row('SELECT id FROM users WHERE LOWER(email)=? AND id<>?', [$o['email'], $target['id'] ?? ''])) fail(409, 'email_taken', 'Email ini sudah dipakai akun lain.', ['field' => 'email']); }
    if (array_key_exists('rk', $b)) { $o['rk'] = (string) $b['rk']; if (!in_array($o['rk'], ROLES, true)) fail(422, 'invalid', 'Peran tidak dikenal.', ['field' => 'rk']);
        if ($me['rk'] !== 'super' && in_array($o['rk'], ['admin', 'super'], true)) fail(403, 'forbidden', 'Hanya Super Admin yang dapat memberi peran Admin atau Super Admin.'); }
    if (array_key_exists('squad', $b)) { $o['squad'] = $b['squad'] === null || $b['squad'] === '' ? null : (string) $b['squad']; if ($o['squad'] !== null && !in_array($o['squad'], SQUADS, true)) fail(422, 'invalid', 'Regu tidak dikenal.', ['field' => 'squad']); }
    if (array_key_exists('station', $b)) { $o['station'] = str_in($b, 'station', 10, true); }
    if (array_key_exists('status', $b)) { $o['status'] = $b['status'] === 'disabled' ? 'disabled' : 'active'; }
    if (array_key_exists('hours', $b)) {
        $h = $b['hours'];
        if ($h === null) $o['hours'] = null;
        else { if (!is_array($h) || !preg_match('/^\d\d:\d\d$/', (string) ($h['start'] ?? '')) || !preg_match('/^\d\d:\d\d$/', (string) ($h['end'] ?? ''))) fail(422, 'invalid', 'Jam kerja tidak valid.', ['field' => 'hours']);
            $o['hours'] = json_encode(['start' => $h['start'], 'end' => $h['end'], 'days' => array_values(array_filter((array) ($h['days'] ?? []), 'is_int')), 'note' => mb_substr((string) ($h['note'] ?? ''), 0, 120)], JSON_UNESCAPED_UNICODE); }
    }
    return $o;
}
route('POST', '/users', function () {
    $me = auth(['super']); $b = body();
    $id = strtoupper(str_in($b, 'id', 7, true));
    if (!preg_match('/^C\d{6}$/', $id)) fail(422, 'invalid', 'Format ID: huruf C diikuti 6 angka.', ['field' => 'id']);
    if (row('SELECT id FROM users WHERE id=?', [$id])) fail(409, 'exists', 'ID ini sudah terdaftar.', ['field' => 'id']);
    $o = validate_member($b + ['rk' => 'crew', 'station' => 'FOX-5'], $me, null);
    if (empty($o['name']) || empty($o['email'])) fail(422, 'required', 'Nama dan email wajib diisi.');
    $temp = 'Fes' . random_int(10000, 99999) . chr(random_int(65, 90));
    $now = ms();
    q('INSERT INTO users (id,name,rk,squad,station,email,password_hash,must_change,status,hours,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
      [$id, $o['name'], $o['rk'], $o['squad'] ?? null, $o['station'], $o['email'], hash_pw($temp), 1, 'active', $o['hours'] ?? null, $now, $now]);
    audit($me, "Menambahkan akun $id ({$o['name']})");
    $sent = send_mail($o['email'], 'Akun FES Emergency Report Anda', "Halo {$o['name']},\n\nAkun FES Emergency Report Anda sudah dibuat.\nID: $id\nKata sandi sementara: $temp\n\nSegera ganti kata sandi di Pengaturan akun setelah masuk.\n\nFES Emergency Report");
    out(['ok' => true, 'user' => public_user(row('SELECT * FROM users WHERE id=?', [$id]), true), 'temp_password' => $temp, 'mailed' => $sent]);
});
route('PUT', '/users/{id}', function ($a) {
    $me = auth(['admin', 'super']); $t = row('SELECT * FROM users WHERE id=?', [$a['id']]);
    if (!$t) fail(404, 'not_found', 'Anggota tidak ditemukan.');
    if ($me['rk'] !== 'super' && in_array($t['rk'], ['admin', 'super'], true) && $t['id'] !== $me['id']) fail(403, 'forbidden', 'Hanya Super Admin yang dapat mengubah akun Admin.');
    $o = validate_member(body(), $me, $t);
    if ($t['id'] === $me['id']) unset($o['rk'], $o['status']);   // tidak bisa menurunkan/menonaktifkan diri sendiri
    if (!$o) out(['ok' => true, 'user' => public_user($t, true)]);
    $set = implode(',', array_map(fn($k) => "$k=?", array_keys($o)));
    q("UPDATE users SET $set, updated_at=? WHERE id=?", [...array_values($o), ms(), $t['id']]);
    if (($o['status'] ?? '') === 'disabled') q('DELETE FROM sessions WHERE user_id=?', [$t['id']]);
    audit($me, "Mengubah data anggota {$t['id']} (" . implode(', ', array_keys($o)) . ')');
    out(['ok' => true, 'user' => public_user(row('SELECT * FROM users WHERE id=?', [$t['id']]), true)]);
});
route('POST', '/users/{id}/reset-password', function ($a) {
    $me = auth(['super']); $t = row('SELECT * FROM users WHERE id=?', [$a['id']]);
    if (!$t) fail(404, 'not_found', 'Anggota tidak ditemukan.');
    $temp = 'Fes' . random_int(10000, 99999) . chr(random_int(65, 90));
    q('UPDATE users SET password_hash=?, must_change=1, failed_count=0, locked_until=0, updated_at=? WHERE id=?', [hash_pw($temp), ms(), $t['id']]);
    q('DELETE FROM sessions WHERE user_id=?', [$t['id']]);
    audit($me, "Mengatur ulang kata sandi {$t['id']}");
    out(['ok' => true, 'temp_password' => $temp]);
});

/* =================================================================== JADWAL & SHIFT */
const DEFAULT_SHIFTS = [['k' => 'P', 'name' => 'Pagi', 'start' => '07:00', 'end' => '15:00'], ['k' => 'S', 'name' => 'Sore', 'start' => '15:00', 'end' => '23:00'], ['k' => 'M', 'name' => 'Malam', 'start' => '23:00', 'end' => '07:00']];
route('GET', '/roster', function () {
    auth();
    out(['ok' => true, 'shifts' => setting('shifts', DEFAULT_SHIFTS), 'rotation' => setting('rotation', SQUADS), 'anchor' => setting('rotation_anchor', '2026-11-01'),
         'min' => setting('min_staff', ['FOX-5' => 3, 'FOX-4' => 3, 'FOX-3' => 3, 'FOX-2' => 2]), 'published' => setting('published', new stdClass()),
         'members' => array_map(fn($u) => ['id' => $u['id'], 'name' => $u['name'], 'rk' => $u['rk'], 'squad' => $u['squad'], 'station' => $u['station'], 'hours' => $u['hours'] ? json_decode($u['hours'], true) : null],
                                rows("SELECT id,name,rk,squad,station,hours FROM users WHERE status='active' AND rk IN ('crew','tl') ORDER BY squad, rk DESC, name"))]);
});
route('PUT', '/roster/shifts', function () {
    $me = auth(['admin', 'super']); $s = body()['shifts'] ?? null;
    if (!is_array($s) || count($s) < 1 || count($s) > 4) fail(422, 'invalid', 'Jumlah shift harus 1 sampai 4.');
    $clean = []; $mins = 0;
    foreach (array_values($s) as $i => $x) {
        $name = trim((string) ($x['name'] ?? '')); $st = (string) ($x['start'] ?? ''); $en = (string) ($x['end'] ?? '');
        if ($name === '' || mb_strlen($name) > 20 || !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $st) || !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $en)) fail(422, 'invalid', 'Shift ' . ($i + 1) . ': nama atau jam tidak valid.');
        $d = (intval(substr($en, 0, 2)) * 60 + intval(substr($en, 3))) - (intval(substr($st, 0, 2)) * 60 + intval(substr($st, 3))); if ($d <= 0) $d += 1440; $mins += $d;
        $clean[] = ['k' => mb_strtoupper(mb_substr($name, 0, 1)) . ($i ?: ''), 'name' => $name, 'start' => $st, 'end' => $en];
    }
    if ($mins !== 1440) fail(422, 'coverage', 'Total jam shift harus tepat 24 jam tanpa tumpang tindih (sekarang ' . round($mins / 60, 1) . ' jam).');
    foreach ($clean as $i => &$c) $c['k'] = ['P', 'S', 'M', 'X'][$i] ?? 'X';
    set_setting('shifts', $clean);
    if (isset(body()['rotation']) && is_array(body()['rotation'])) { $r = array_values(array_intersect(body()['rotation'], SQUADS)); if (count($r) === 4) set_setting('rotation', $r); }
    audit($me, 'Mengubah pengaturan shift: ' . implode(', ', array_map(fn($c) => "{$c['name']} {$c['start']}–{$c['end']}", $clean)));
    out(['ok' => true, 'shifts' => $clean]);
});
route('PUT', '/roster/published', function () {
    $me = auth(['admin', 'super']); $b = body(); $m = (string) ($b['month'] ?? '');
    if (!preg_match('/^\d{4}-\d{2}$/', $m)) fail(422, 'invalid', 'Bulan tidak valid.');
    $p = setting('published', []); $p[$m] = !empty($b['published']) ? 'Terbit' : 'Draf'; set_setting('published', $p);
    audit($me, ($p[$m] === 'Terbit' ? 'Menerbitkan' : 'Menarik') . " jadwal $m");
    out(['ok' => true, 'published' => $p]);
});
route('GET', '/swaps', function () {
    $u = auth();
    $mine = !in_array($u['rk'], ['admin', 'super', 'koord'], true);
    $sql = 'SELECT s.*, f.name from_name, f.squad from_squad, t.name to_name FROM swaps s JOIN users f ON f.id=s.from_id LEFT JOIN users t ON t.id=s.to_id';
    out(['ok' => true, 'swaps' => $mine ? rows("$sql WHERE s.from_id=? OR s.to_id=? ORDER BY s.created_at DESC LIMIT 200", [$u['id'], $u['id']]) : rows("$sql ORDER BY s.status='pending' DESC, s.created_at DESC LIMIT 300")]);
});
route('POST', '/swaps', function () {
    $u = auth(RESPONSE_ROLES); $b = body();
    $kind = in_array($b['kind'] ?? '', ['tukar', 'izin', 'cuti', 'sakit'], true) ? $b['kind'] : 'izin';
    $to = $kind === 'tukar' ? strtoupper(str_in($b, 'to', 7, true)) : null;
    if ($to && (!row("SELECT id FROM users WHERE id=? AND status='active'", [$to]) || $to === $u['id'])) fail(422, 'invalid', 'Rekan pengganti tidak valid.', ['field' => 'to']);
    $day = str_in($b, 'day', 20, true); $shift = str_in($b, 'shift', 20, true); $reason = str_in($b, 'reason', 300, true);
    rate('swap:' . $u['id'], 20, 86400);
    q('INSERT INTO swaps (from_id,to_id,kind,day,shift,reason,created_at) VALUES (?,?,?,?,?,?,?)', [$u['id'], $to, $kind, $day, $shift, $reason, ms()]);
    $id = (int) db()->lastInsertId();
    audit($u, "Mengajukan " . ($kind === 'izin' ? 'izin' : 'tukar jaga') . " $day $shift");
    out(['ok' => true, 'id' => $id]);
});
route('PUT', '/swaps/{id}', function ($a) {
    $me = auth(['admin', 'super']); $s = row('SELECT * FROM swaps WHERE id=?', [(int) $a['id']]);
    if (!$s) fail(404, 'not_found', 'Pengajuan tidak ditemukan.');
    if ($s['status'] !== 'pending') fail(409, 'decided', 'Pengajuan ini sudah diputuskan.');
    $st = (body()['status'] ?? '') === 'approved' ? 'approved' : 'rejected';
    q('UPDATE swaps SET status=?, decided_by=?, decided_at=? WHERE id=?', [$st, $me['id'], ms(), $s['id']]);
    audit($me, ($st === 'approved' ? 'Menyetujui' : 'Menolak') . " pengajuan {$s['kind']} {$s['from_id']} {$s['day']} {$s['shift']}");
    out(['ok' => true, 'status' => $st]);
});

/* =================================================================== RESPONS & LAPORAN */
function inc_fields(array $d): array {
    $t = is_array($d['times'] ?? null) ? $d['times'] : [];
    $ti = fn($k) => isset($t[$k]) && is_numeric($t[$k]) ? (int) $t[$k] : null;
    $s = fn($k, $n) => mb_substr(trim((string) ($d[$k] ?? '')), 0, $n) ?: null;
    $phase = in_array($d['phase'] ?? '', ['ready', 'enroute', 'onscene', 'returning', 'closed'], true) ? $d['phase'] : 'ready';
    return ['rtype' => $s('rtype', 20), 'itype' => $s('itype', 20), 'sub' => $s('sub', 60), 'location' => $s('location', 160), 'station' => $s('station', 60), 'phase' => $phase,
            'dispatch_at' => $ti('dispatch'), 'start_at' => $ti('start'), 'arrival_at' => $ti('arrival'), 'leave_at' => $ti('leave'), 'return_at' => $ti('return'),
            'target_min' => is_numeric($d['target'] ?? null) ? (int) $d['target'] : null];
}
function inc_data(array $d): string {
    unset($d['screen'], $d['sheet'], $d['undo'], $d['photos']);   // foto disimpan terpisah
    $j = json_encode($d, JSON_UNESCAPED_UNICODE);
    if ($j === false || strlen($j) > 2000000) fail(413, 'too_large', 'Isi laporan terlalu besar.');
    return $j;
}
function next_no(int $at): string {
    $fmt = setting('report_format', 'ESS/Pom/{NNN}/{MM}/{YYYY}'); $y = (int) date('Y', intdiv($at, 1000));
    $n = (int) row('SELECT COUNT(*) n FROM incidents WHERE created_at>=?', [strtotime("$y-01-01") * 1000])['n'] + 1;
    do { $no = strtr($fmt, ['{NNN}' => str_pad((string) $n, 3, '0', STR_PAD_LEFT), '{MM}' => date('m', intdiv($at, 1000)), '{YYYY}' => (string) $y]); $n++; }
    while (row('SELECT id FROM incidents WHERE no=?', [$no]));
    return $no;
}
function inc_out(array $r, bool $withPhotos = false): array {
    $d = json_decode($r['data'], true) ?: [];
    $o = ['id' => (int) $r['id'], 'no' => $r['no'], 'status' => $r['status'], 'version' => (int) $r['version'], 'updated_at' => (int) $r['updated_at'],
          'created_by' => $r['created_by'], 'submitted_at' => $r['submitted_at'], 'approved_by' => $r['approved_by'], 'approved_at' => $r['approved_at'], 'approval_note' => $r['approval_note'], 'data' => $d];
    if ($withPhotos) $o['photos'] = array_map(fn($p) => ['id' => (int) $p['id'], 'url' => $p['data'], 't' => (int) $p['taken_at'], 'stage' => $p['caption']], rows('SELECT * FROM photos WHERE incident_id=? ORDER BY taken_at', [$r['id']]));
    return $o;
}
route('GET', '/incidents', function () {
    $u = auth(); $st = (string) ($_GET['status'] ?? '');
    $where = '1=1'; $a = [];
    if ($st === 'active') $where = "status IN ('active','returned','revise')";
    elseif (in_array($st, ['submitted', 'approved'], true)) { $where = 'status=?'; $a[] = $st; }
    if (!in_array($u['rk'], ACCESS['dash'], true)) { $where .= ' AND (created_by=? OR data LIKE ?)'; array_push($a, $u['id'], '%"' . $u['id'] . '"%'); }
    out(['ok' => true, 'incidents' => array_map('inc_out', rows("SELECT * FROM incidents WHERE $where ORDER BY COALESCE(dispatch_at,created_at) DESC LIMIT 200", $a))]);
});
route('GET', '/incidents/{id}', function ($a) {
    $u = auth(); $r = row('SELECT * FROM incidents WHERE id=?', [(int) $a['id']]);
    if (!$r) fail(404, 'not_found', 'Laporan tidak ditemukan.');
    if (!in_array($u['rk'], ACCESS['dash'], true) && $r['created_by'] !== $u['id'] && !str_contains($r['data'], '"' . $u['id'] . '"')) fail(403, 'forbidden', 'Anda tidak terlibat di respons ini.');
    out(['ok' => true, 'incident' => inc_out($r, true)]);
});
route('POST', '/incidents', function () {
    $u = auth(RESPONSE_ROLES); $d = body()['data'] ?? null;
    if (!is_array($d)) fail(422, 'invalid', 'Data respons kosong.');
    $f = inc_fields($d); $now = ms(); $no = next_no($f['dispatch_at'] ?? $now); $d['no'] = $no;
    q('INSERT INTO incidents (no,rtype,itype,sub,location,station,phase,status,dispatch_at,start_at,arrival_at,leave_at,return_at,target_min,data,created_by,updated_by,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
      [$no, $f['rtype'], $f['itype'], $f['sub'], $f['location'], $f['station'], $f['phase'], $f['phase'] === 'closed' ? 'returned' : 'active', $f['dispatch_at'], $f['start_at'], $f['arrival_at'], $f['leave_at'], $f['return_at'], $f['target_min'], inc_data($d), $u['id'], $u['id'], $now, $now]);
    $id = (int) db()->lastInsertId();
    audit($u, "Membuat respons $no");
    out(['ok' => true, 'incident' => inc_out(row('SELECT * FROM incidents WHERE id=?', [$id]))]);
});
route('PUT', '/incidents/{id}', function ($a) {
    $u = auth(RESPONSE_ROLES); $r = row('SELECT * FROM incidents WHERE id=?', [(int) $a['id']]); $b = body();
    if (!$r) fail(404, 'not_found', 'Laporan tidak ditemukan.');
    if (in_array($r['status'], ['submitted', 'approved'], true)) fail(409, 'locked', 'Laporan sudah diajukan/disetujui dan tidak bisa diubah.');
    if (isset($b['version']) && (int) $b['version'] !== (int) $r['version']) fail(409, 'conflict', 'Laporan sudah diubah oleh anggota lain. Muat ulang dulu.', ['incident' => inc_out($r)]);
    $d = $b['data'] ?? null; if (!is_array($d)) fail(422, 'invalid', 'Data respons kosong.');
    $d['no'] = $r['no']; $f = inc_fields($d);
    $status = $r['status'] === 'revise' ? 'revise' : ($f['phase'] === 'closed' ? 'returned' : 'active');
    q('UPDATE incidents SET rtype=?,itype=?,sub=?,location=?,station=?,phase=?,status=?,dispatch_at=?,start_at=?,arrival_at=?,leave_at=?,return_at=?,target_min=?,data=?,version=version+1,updated_by=?,updated_at=? WHERE id=?',
      [$f['rtype'], $f['itype'], $f['sub'], $f['location'], $f['station'], $f['phase'], $status, $f['dispatch_at'], $f['start_at'], $f['arrival_at'], $f['leave_at'], $f['return_at'], $f['target_min'], inc_data($d), $u['id'], ms(), $r['id']]);
    out(['ok' => true, 'incident' => inc_out(row('SELECT * FROM incidents WHERE id=?', [$r['id']]))]);
});
route('POST', '/incidents/{id}/photos', function ($a) {
    $u = auth(RESPONSE_ROLES); $r = row('SELECT id,status FROM incidents WHERE id=?', [(int) $a['id']]); $b = body();
    if (!$r) fail(404, 'not_found', 'Laporan tidak ditemukan.');
    if ((int) row('SELECT COUNT(*) n FROM photos WHERE incident_id=?', [$r['id']])['n'] >= 60) fail(422, 'too_many', 'Maksimal 60 foto per laporan.');
    $img = clean_image((string) ($b['photo'] ?? ''));
    q('INSERT INTO photos (incident_id,data,caption,taken_at,created_by) VALUES (?,?,?,?,?)', [$r['id'], $img, mb_substr((string) ($b['stage'] ?? ''), 0, 200), is_numeric($b['t'] ?? null) ? (int) $b['t'] : ms(), $u['id']]);
    out(['ok' => true, 'id' => (int) db()->lastInsertId()]);
});
route('POST', '/incidents/{id}/submit', function ($a) {
    $u = auth(RESPONSE_ROLES); $r = row('SELECT * FROM incidents WHERE id=?', [(int) $a['id']]);
    if (!$r) fail(404, 'not_found', 'Laporan tidak ditemukan.');
    if (!in_array($r['status'], ['returned', 'revise', 'active'], true)) fail(409, 'locked', 'Laporan ini sudah diajukan.');
    if (!$r['return_at']) fail(422, 'not_closed', 'ERR baru bisa diajukan setelah tim kembali ke station.');
    q("UPDATE incidents SET status='submitted', submitted_at=?, version=version+1, updated_at=? WHERE id=?", [ms(), ms(), $r['id']]);
    audit($u, "Mengajukan ERR {$r['no']}");
    out(['ok' => true]);
});
route('POST', '/incidents/{id}/decision', function ($a) {
    $u = auth(APPROVERS); $r = row('SELECT * FROM incidents WHERE id=?', [(int) $a['id']]); $b = body();
    if (!$r) fail(404, 'not_found', 'Laporan tidak ditemukan.');
    if ($r['status'] !== 'submitted') fail(409, 'not_submitted', 'Hanya ERR yang sudah diajukan yang bisa diputuskan.');
    $ok = ($b['decision'] ?? '') === 'approve'; $note = str_in($b, 'note', 500, !$ok);
    q('UPDATE incidents SET status=?, approved_by=?, approved_at=?, approval_note=?, version=version+1, updated_at=? WHERE id=?', [$ok ? 'approved' : 'revise', $u['id'], ms(), $note ?: null, ms(), $r['id']]);
    audit($u, ($ok ? 'Menyetujui' : 'Meminta revisi') . " ERR {$r['no']}");
    out(['ok' => true, 'status' => $ok ? 'approved' : 'revise']);
});

/* =================================================================== DASHBOARD */
route('GET', '/dashboard', function () {
    auth(ACCESS['dash']);
    $rules = setting('rules', ['prelim' => 15]); $rows = []; $live = [];
    foreach (rows('SELECT * FROM incidents WHERE dispatch_at IS NOT NULL ORDER BY dispatch_at') as $r) {
        $d = json_decode($r['data'], true) ?: [];
        $cas = array_sum(array_map('intval', (array) ($d['cas'] ?? []))); $tri = array_values(array_filter((array) ($d['triage'] ?? []), 'is_string'));
        $triList = []; for ($i = 0; $i < $cas; $i++) $triList[] = $tri ? $tri[$i % count($tri)] : 'Green';
        $recall = (array) ($d['recall'] ?? []);
        $support = ''; foreach ($recall as $x) if (($x['cat'] ?? '') === 'Support') { $support = (string) ($x['name'] ?? ''); break; }
        $min = fn($a, $b) => $a && $b ? round(($b - $a) / 60000, 1) : null;
        preg_match('/FOX-\d/', (string) $r['station'], $m);
        $prelimSent = $d['prelim']['sent'] ?? null;
        $row = ['no' => $r['no'], 't' => (int) $r['dispatch_at'], 'type' => $r['rtype'] ?: 'ALARM', 'sub' => $r['sub'] ?: '-', 'itype' => $r['itype'] ?: 'INDUSTRIAL',
                'st' => $m[0] ?? 'FOX-5', 'place' => $r['location'] ?: '-', 'trt' => $min($r['dispatch_at'], $r['arrival_at']), 'tgt' => $r['target_min'] ?: 15,
                'travel' => $min($r['start_at'], $r['arrival_at']), 'rsp' => $min($r['dispatch_at'], $r['return_at']), 'ncas' => $cas, 'tri' => $triList, 'cats' => [],
                'units' => array_values((array) ($d['units'] ?? [])), 'crew' => count((array) ($d['crew'] ?? [])), 'recall' => count(array_filter($recall, fn($x) => ($x['cat'] ?? '') !== 'Support')) > 0,
                'support' => $support, 'prelimLate' => $r['arrival_at'] && (!$prelimSent || $prelimSent - $r['arrival_at'] > ($rules['prelim'] ?? 15) * 60000),
                'status' => match ($r['status']) { 'approved' => 'final', 'submitted' => 'review', default => 'draft' }, 'id' => (int) $r['id'], 'phase' => $r['phase']];
        if (in_array($r['phase'], ['enroute', 'onscene', 'returning'], true)) {
            $since = match ($r['phase']) { 'enroute' => ['Start', $r['start_at']], 'onscene' => ['Arrival', $r['arrival_at']], default => ['Leave', $r['leave_at']] };
            $live[] = ['id' => (int) $r['id'], 'no' => $r['no'], 'type' => $row['type'], 'sub' => $row['sub'], 'place' => $row['place'],
                       'phase' => ['enroute' => 'Menuju TKP', 'onscene' => 'Di TKP', 'returning' => 'Kembali ke station'][$r['phase']],
                       'steps' => match ($r['phase']) { 'enroute' => ['done', 'done', 'now', ''], 'onscene' => ['done', 'done', 'done', 'now'], default => ['done', 'done', 'done', 'done'] },
                       'since' => $since[0] . ' ' . ($since[1] ? max(0, (int) round((ms() - $since[1]) / 60000)) . ' mnt lalu' : ''), 'units' => implode(', ', $row['units'])];
        } elseif ($row['trt'] !== null) $rows[] = $row;
    }
    $pending = array_map('inc_out', rows("SELECT * FROM incidents WHERE status='submitted' ORDER BY submitted_at"));
    out(['ok' => true, 'now' => ms(), 'rows' => $rows, 'live' => $live, 'pending' => $pending]);
});

/* =================================================================== PENGATURAN & LOG */
const SETTING_KEYS = ['rules', 'report_format', 'recipients', 'min_staff', 'units', 'system'];
route('GET', '/settings', function () { auth(['admin', 'super', 'koord']); $o = []; foreach (SETTING_KEYS as $k) $o[$k] = setting($k); out(['ok' => true, 'settings' => $o]); });
route('PUT', '/settings/{key}', function ($a) {
    $me = auth(['admin', 'super']); $k = $a['key'];
    if (!in_array($k, SETTING_KEYS, true)) fail(404, 'not_found', 'Pengaturan tidak dikenal.');
    if ($k === 'system' && $me['rk'] !== 'super') fail(403, 'forbidden', 'Hanya Super Admin yang dapat mengubah pengaturan sistem.');
    $v = body()['value'] ?? null; $j = json_encode($v);
    if ($v === null || strlen((string) $j) > 20000) fail(422, 'invalid', 'Nilai pengaturan tidak valid.');
    if ($k === 'report_format' && (!is_string($v) || !str_contains($v, '{NNN}') || mb_strlen($v) > 40)) fail(422, 'invalid', 'Format nomor wajib memuat {NNN}.');
    set_setting($k, $v); audit($me, "Mengubah pengaturan $k");
    out(['ok' => true]);
});
route('GET', '/audit', function () {
    auth(['admin', 'super']); $lim = max(1, min(500, (int) ($_GET['limit'] ?? 200)));
    out(['ok' => true, 'audit' => rows("SELECT at, actor_name, action FROM audit ORDER BY at DESC LIMIT $lim")]);
});

/* =================================================================== dispatch */
foreach ($R as [$m, $re, $f]) if ($m === $method && preg_match($re, $path, $mm)) { $f(array_filter($mm, 'is_string', ARRAY_FILTER_USE_KEY)); exit; }
fail(404, 'not_found', 'Alamat API tidak dikenal.');
