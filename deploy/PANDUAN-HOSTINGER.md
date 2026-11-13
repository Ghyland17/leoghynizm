# Panduan Pasang di Hostinger — FES Emergency Report v2.1 (dengan database)

Versi 2.1 terdiri dari dua bagian:
- **Website dan aplikasi**: file HTML yang sama seperti sebelumnya.
- **Backend**: folder `api/`, ditulis dengan PHP 8 dan memakai database MySQL.

Akun, kata sandi, laporan respons, foto, jadwal, pengajuan, dan log audit sekarang tersimpan di **database server**. Data otomatis sama di semua HP dan komputer.

Paket ini bisa dipasang di semua paket *Web Hosting* Hostinger. Tidak perlu VPS.

---

## 1. Buat database

1. Buka hPanel, lalu **Databases → Management**.
2. Buat database baru, misalnya `fes`. Hostinger akan menambahkan awalan, misalnya `u123456789_fes`.
3. Buat pengguna database dengan sandi yang kuat.
4. Catat tiga hal: **nama database**, **nama pengguna**, dan **sandi**. Host database biasanya `localhost`.

## 2. Buat email pengirim kode

Email ini dipakai untuk mengirim kode login dua langkah (Admin FES dan Super Admin) dan kode lupa kata sandi.

1. Buka hPanel, lalu **Emails**, dan buat akun email, misalnya `no-reply@domainanda.com`.
2. Catat sandi email tersebut.
3. Pengaturan SMTP Hostinger:
   - server: `smtp.hostinger.com`
   - port: `465`
   - keamanan: SSL

## 3. Unggah file

1. Buat subdomain, misalnya `fes.domainanda.com`, lewat **Domains → Subdomains**.
2. Aktifkan SSL untuk subdomain itu (**Security → SSL**) dan nyalakan **Force HTTPS**.
3. Buka **Files → File Manager**, lalu masuk ke folder subdomain tersebut.
4. Unggah `FES-Hosting-v2.1.zip`, lalu **Extract**.
5. Pindahkan **seluruh isi** folder `fes-web` satu tingkat ke atas.
   - Hasil akhirnya: `index.html`, `app.html`, folder `api/`, dan file lainnya berada langsung di folder subdomain.
   - Aktifkan *Show hidden files* supaya file `.htaccess` ikut terlihat dan ikut dipindahkan.
6. Periksa versi PHP di **Advanced → PHP Configuration**. Pastikan **8.1 atau lebih baru**.

## 4. Isi konfigurasi

1. Buka folder `api/` di File Manager.
2. Salin `config.sample.php` menjadi **`config.php`**.
3. Edit `config.php` dan isi bagian berikut:
   - `db_dsn`: ganti `u123456789_fes` dengan nama database Anda.
   - `db_user` dan `db_pass`: pengguna dan sandi database dari langkah 1.
   - `mail_from`, `smtp_user`, `smtp_pass`: email dari langkah 2.
   - `install_key`: teks acak yang panjang, misalnya 30 huruf dan angka campur.

File `config.php` tidak bisa dibuka dari browser karena diblokir oleh `.htaccess`.

## 5. Pasang database

1. Buka `https://fes.domainanda.com/api/install.php` di browser.
2. Isi formulirnya:
   - **Kunci pemasangan**: sama dengan `install_key`.
   - **Email Super Admin**: boleh dikosongkan. Kalau kosong, email yang sudah terdaftar yang dipakai.
   - **Email Admin FES**: isi dengan email asli Okky, karena dibutuhkan untuk kode login dua langkah.
3. Tekan **Pasang database**. Halaman akan memberi tahu jumlah tabel dan akun yang dibuat (19 akun).
4. **Hapus file `api/install.php`** dari File Manager.
5. Cek koneksi: buka `https://fes.domainanda.com/api/index.php/health`. Hasilnya harus `{"ok":true,...,"db":true}`.

## 6. Mulai dipakai

- **Web (dashboard dan admin):** `https://fes.domainanda.com/`
- **Aplikasi HP lewat browser:** `https://fes.domainanda.com/app.html`. Lalu pilih **Instal aplikasi** di Chrome, atau **Tambahkan ke Layar Utama** di Safari iPhone.
- **Aplikasi Android (APK):** pasang `FES-Report-v2.1.apk`. Saat pertama dibuka, aplikasi menanyakan **alamat server**. Isi `https://fes.domainanda.com`.

**Masuk pertama kali:**
1. Semua akun memakai kata sandi awal **`Fes12345`**.
2. Sistem **mewajibkan** setiap orang membuat kata sandi baru saat pertama masuk. Sebelum itu, data lain belum bisa dibuka.
3. Super Admin (Eghy, C061138) masuk dulu. Kode dua langkah dikirim ke email Super Admin.
4. Buka **Admin → Anggota & akses** dan ganti email contoh (`...@email.contoh`) dengan email asli setiap anggota. Email ini dipakai untuk fitur **Lupa kata sandi**.

---

## Yang sekarang ditangani server

| Fitur | Cara kerja |
|---|---|
| Login | Sandi diperiksa di server. Sandi disimpan sebagai hash Argon2id/bcrypt, tidak pernah dalam bentuk asli. |
| Penguncian | 5 kali salah sandi membuat akun terkunci 15 menit. Ada juga batas percobaan per alamat IP. |
| Login dua langkah | Kode 6 digit dikirim ke email Admin FES dan Super Admin. Berlaku 5 menit, maksimal 5 kali salah. Ada pilihan "percayai perangkat" selama 30 hari. |
| Sesi | Web keluar otomatis setelah 1 jam tidak aktif. Aplikasi HP tetap masuk sampai menekan **Keluar**. Mengganti kata sandi mengeluarkan sesi di perangkat lain. |
| Hak akses | Dicek di server untuk setiap permintaan, sesuai peran: Tim FES, Team Leader, Koordinator, Admin FES, Super Admin. |
| Respons darurat | Setiap tombol (Dispatch, Start, Arrival, Leave, Return), Preliminary, personel, korban, dan foto langsung tersimpan. Nomor laporan dibuat otomatis oleh server. |
| Tanpa sinyal | Perubahan ditahan di HP dan dikirim otomatis saat sinyal kembali. |
| ERR | Tim mengajukan ERR. Koordinator menyetujui atau meminta revisi di Dashboard. Laporan yang sudah diajukan terkunci dari perubahan. |
| Dashboard | Statistik dihitung dari respons asli. Ada panel "Sedang berjalan" dan "ERR menunggu persetujuan". Data diperbarui setiap 30 detik. |
| Jadwal | Shift, rotasi regu, jam kerja individu, terbitkan jadwal, serta pengajuan tukar jaga, izin, cuti, dan sakit beserta persetujuannya. |
| Kalender jaga | Tanda Izin, Cuti, atau Sakit dan pengganti per shift tersimpan di server. Tanda ini langsung terlihat di **Jadwal saya** anggota. |
| Kebutuhan minimum | Jumlah personel minimum per station tersimpan di server dan dipakai untuk peringatan "kurang personel". |
| Unit kendaraan | Unit bisa ditambah, diubah, dihapus, dan diberi status Siap atau Perbaikan. Unit yang sedang **Perbaikan** tidak muncul di pilihan Dispatch. Status "Sedang respons" dihitung otomatis. |
| Data master | Response station, lokasi, sumber informasi, dan instansi pendukung bisa diubah dari Admin. Daftar ini langsung dipakai di aplikasi. |
| Pengaturan sistem | Super Admin bisa mengatur masa berlaku kode email (3–15 menit) dan batas salah kode (3–10 kali). |
| Log audit | Mencatat siapa melakukan apa dan kapan. Bisa diekspor ke CSV dari halaman Admin. |

## Cadangan (backup)

hPanel membuat backup otomatis (**Files → Backups**). Unduh juga backup database secara berkala lewat **Databases → phpMyAdmin → Export**.

## Memperbarui versi

Dari v2.0 ke v2.1: unggah ulang semua file, kecuali `api/config.php`. Tabel baru dibuat **otomatis** saat API pertama kali dipanggil, jadi tidak perlu menjalankan `install.php` lagi.


1. Unggah ulang file HTML dan folder `api/`. **Jangan timpa `api/config.php`.**
2. Database tetap aman. Skema hanya dibuat oleh `install.php`, yang sudah Anda hapus.

## Batasan yang perlu diketahui

- File HTML masih memuat daftar nama dan BN anggota (dipakai pada tampilan). Kata sandi dan email tidak ada lagi di halaman.
- Untuk membatasi siapa yang bisa membuka situs, pasang **Cloudflare Access** di depan domain (lihat PANDUAN-HOSTING.md).
- Notifikasi push ke HP belum ada. Status diperbarui saat aplikasi dibuka atau setiap 1 menit.
- Peta masih gambar sederhana. GPS sungguhan dan peta interaktif bisa ditambahkan pada tahap berikutnya.
