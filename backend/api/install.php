<?php
/* Pemasangan database sekali jalan. Setelah berhasil, file install.lock dibuat dan halaman ini tidak bisa dipakai lagi.
   Hapus file install.php dari hosting setelah selesai. */
declare(strict_types=1);
define('FES', true);
require __DIR__ . '/lib.php';
header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; form-action 'self'");
$lock = __DIR__ . '/install.lock';
$h = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$msg = ''; $done = false;

if (is_file($lock)) $msg = 'Database sudah terpasang. Hapus file install.php dari hosting.';
elseif (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $key = (string) ($_POST['key'] ?? ''); $real = (string) cfg('install_key', '');
    if ($real === '' || str_starts_with($real, 'GANTI') || !hash_equals($real, $key)) { sleep(2); $msg = 'Kunci pemasangan salah, atau install_key di config.php belum diganti.'; }
    else {
        try {
            $sql = preg_replace('/--[^\n]*/', '', file_get_contents(__DIR__ . '/schema.sql'));
            foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) db()->exec($stmt);
            if ((int) row('SELECT COUNT(*) n FROM users')['n'] === 0) {
                $now = ms(); $pw = hash_pw('Fes12345');
                $adminEmail = strtolower(trim((string) ($_POST['admin_email'] ?? '')));
                $superEmail = strtolower(trim((string) ($_POST['super_email'] ?? '')));
                foreach (require __DIR__ . '/seed.php' as [$id, $name, $rk, $squad, $email]) {
                    if ($rk === 'admin' && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) $email = $adminEmail;
                    if ($rk === 'super' && filter_var($superEmail, FILTER_VALIDATE_EMAIL)) $email = $superEmail;
                    q('INSERT INTO users (id,name,rk,squad,station,email,password_hash,must_change,status,created_at,updated_at) VALUES (?,?,?,?,?,?,?,1,?,?,?)',
                      [$id, $name, $rk, $squad, 'FOX-5', $email, $pw, 'active', $now, $now]);
                }
                set_setting('rules', ['prelim' => 15, 'focus' => 15, 'approve' => 24, 'photo' => true]);
                set_setting('report_format', 'ESS/Pom/{NNN}/{MM}/{YYYY}');
                audit(null, 'Database dipasang');
            }
            file_put_contents($lock, date('c'));
            $done = true; $msg = 'Berhasil. Database dan ' . row('SELECT COUNT(*) n FROM users')['n'] . ' akun sudah dibuat.';
        } catch (Throwable $e) { error_log('FES install: ' . $e->getMessage()); $msg = 'Gagal terhubung atau membuat tabel. Periksa db_dsn, db_user, dan db_pass di config.php.'; }
    }
}
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex">
<title>Pasang database FES</title>
<style>body{font:16px/1.5 system-ui,sans-serif;background:#0b0d10;color:#e8eaed;margin:0;padding:32px 16px}main{max-width:520px;margin:auto}
h1{font-size:22px}label{display:block;margin:16px 0 6px;font-weight:600}input{width:100%;box-sizing:border-box;padding:12px;border-radius:10px;border:1px solid #3a3f47;background:#15181d;color:inherit;font:inherit}
button{margin-top:20px;padding:12px 18px;border:0;border-radius:10px;background:#eb6834;color:#fff;font:600 16px system-ui;cursor:pointer}
.msg{padding:12px 14px;border-radius:10px;background:#1d2229;margin:16px 0}.ok{background:#12351f}small{color:#9aa0a6}</style></head><body><main>
<h1>Pasang database FES Emergency Report</h1>
<?php if ($msg): ?><div class="msg<?= $done ? ' ok' : '' ?>"><?= $h($msg) ?></div><?php endif; ?>
<?php if ($done): ?>
<p>Semua akun memakai kata sandi awal <b>Fes12345</b> dan <b>wajib menggantinya</b> saat pertama masuk.</p>
<p>Langkah berikutnya: <b>hapus file install.php</b> dari File Manager, lalu buka halaman utama situs dan masuk sebagai Super Admin.</p>
<?php elseif (!is_file($lock)): ?>
<form method="post" autocomplete="off">
<label for="key">Kunci pemasangan (install_key di config.php)</label><input id="key" name="key" type="password" required>
<label for="se">Email Super Admin <small>(kosongkan untuk memakai email yang sudah terdaftar)</small></label><input id="se" name="super_email" type="email" placeholder="nama@gmail.com">
<label for="ae">Email Admin FES <small>(opsional, untuk kode login dua langkah)</small></label><input id="ae" name="admin_email" type="email" placeholder="nama@gmail.com">
<button type="submit">Pasang database</button></form>
<?php endif; ?>
</main></body></html>
