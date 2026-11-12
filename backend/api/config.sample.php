<?php
// Salin file ini menjadi config.php lalu isi sesuai akun Hostinger Anda.
// File config.php tidak bisa dibuka dari browser (diblokir oleh .htaccess).
return [
    // Database: hPanel → Databases → Management (MySQL). Host biasanya "localhost".
    'db_dsn'  => 'mysql:host=localhost;dbname=u123456789_fes;charset=utf8mb4',
    'db_user' => 'u123456789_fes',
    'db_pass' => 'GANTI_DENGAN_SANDI_DATABASE',

    // Asal (origin) lain yang boleh memanggil API. Aplikasi Android memakai origin di bawah ini.
    'allowed_origins' => ['https://appassets.androidplatform.net'],

    // Email untuk kode login dua langkah & lupa kata sandi.
    // Buat email di hPanel → Emails, mis. no-reply@domainanda.com, lalu isi di bawah.
    'mail_driver'    => 'smtp',               // smtp | mail
    'mail_from'      => 'no-reply@domainanda.com',
    'mail_from_name' => 'FES Emergency Report',
    'smtp_host'      => 'smtp.hostinger.com',
    'smtp_port'      => 465,
    'smtp_secure'    => 'ssl',                // ssl (port 465) | tls (port 587)
    'smtp_user'      => 'no-reply@domainanda.com',
    'smtp_pass'      => 'GANTI_DENGAN_SANDI_EMAIL',

    // Kunci sekali pakai untuk halaman install.php. Ganti dengan teks acak panjang, hapus install.php setelah selesai.
    'install_key' => 'GANTI_DENGAN_KUNCI_ACAK_PANJANG',
];
