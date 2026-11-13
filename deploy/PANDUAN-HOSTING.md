# Panduan Hosting — FES Emergency Report v1.6

Paket `FES-Hosting-v1.6.zip` berisi website statis. Tidak butuh database, PHP, atau Node.js: cukup unggah isinya ke folder web.

## Isi paket

| File | Fungsi |
|---|---|
| `index.html` | Portal web: login → langsung ke halaman sesuai peran (Dashboard / Respons darurat / Admin) |
| `app.html` | Aplikasi HP (bisa dipasang ke layar utama sebagai aplikasi) |
| `dashboard.html`, `admin.html` | Halaman Dashboard dan Admin yang berdiri sendiri (masing-masing dengan login) |
| `manifest.webmanifest`, `manifest-web.webmanifest` | Data aplikasi untuk tombol "Instal aplikasi" di browser |
| `sw.js` | Service worker: aplikasi tetap terbuka saat sinyal hilang |
| `fonts/`, `icons/` | Font dan ikon (semua lokal, tidak memanggil server luar) |
| `.htaccess` | Pengaturan Apache/cPanel: paksa HTTPS, header keamanan, tanpa daftar folder |
| `_headers` | Pengaturan yang sama untuk Netlify / Cloudflare Pages |
| `robots.txt` | Melarang mesin pencari mengindeks situs |

## Syarat wajib: HTTPS

GPS, kamera, tombol instal aplikasi, dan mode offline **hanya berjalan lewat HTTPS**. Semua pilihan di bawah sudah menyediakan HTTPS.

## Pilihan 1 — Server perusahaan / cPanel (disarankan untuk data internal)

1. Ekstrak `FES-Hosting-v1.6.zip`.
2. Unggah **seluruh isi** folder `fes-web` (termasuk file `.htaccess` yang tersembunyi) ke `public_html/` atau subfolder, misalnya `public_html/fes/`.
3. Pastikan sertifikat SSL aktif (cPanel → SSL/TLS → AutoSSL / Let's Encrypt).
4. Buka `https://domain-anda/fes/` untuk portal, `https://domain-anda/fes/app.html` untuk aplikasi HP.

Nginx: arahkan `root` ke folder `fes-web`, tambahkan `types { application/manifest+json webmanifest; }` dan header yang sama dengan isi `_headers`.

## Pilihan 2 — Netlify (paling cepat)

1. Masuk ke app.netlify.com → **Add new site → Deploy manually**.
2. Seret folder `fes-web` ke halaman itu. Selesai — alamat `https://nama-situs.netlify.app` langsung aktif.
3. Batasi akses: **Site configuration → Access control → Password protection** (paket berbayar) atau gunakan Pilihan 1/3.

## Pilihan 3 — Cloudflare Pages + Cloudflare Access

1. dash.cloudflare.com → **Workers & Pages → Create → Pages → Upload assets** → unggah folder `fes-web`.
2. Batasi hanya untuk email perusahaan: **Zero Trust → Access → Applications → Add** → pilih domain Pages → aturan "Emails ending in @domain-perusahaan".

## Memperbarui versi

Unggah ulang semua file (timpa yang lama). Service worker akan mengambil versi baru otomatis saat aplikasi dibuka dengan sinyal; buka sekali lagi untuk melihatnya.

## Penting sebelum dipakai sungguhan

- Ini **prototipe tanpa server**: akun, sandi, laporan, dan jadwal disimpan di browser masing-masing perangkat. Data dari HP satu **tidak** terkirim ke dashboard di komputer lain.
- Daftar akun (nama, BN, email) tertanam di kode halaman. **Jangan pasang di alamat publik tanpa pembatasan akses** (Pilihan 1 di jaringan internal, atau Pilihan 3 dengan Cloudflare Access).
- Semua akun memakai sandi awal `Fes12345`. Minta setiap pengguna menggantinya di **Pengaturan akun** saat pertama masuk.
- Kode dua langkah untuk Admin FES & Super Admin saat ini ditampilkan di layar (simulasi). Pengiriman email sungguhan butuh server — lihat PRD bagian 7B.
- Untuk produksi: backend + database, login di server, pengiriman email OTP, dan sinkronisasi data antar perangkat (PRD 7B).
