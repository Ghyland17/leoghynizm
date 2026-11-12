# Panduan Instal Aplikasi — FES Report v1.6

## A. Android — file APK (`FES-Report-v1.6.apk`)

1. Kirim file APK ke HP (WhatsApp, Google Drive, kabel USB, atau unduh dari server perusahaan).
2. Buka file tersebut di HP. Jika muncul "Untuk keamanan, ponsel Anda tidak diizinkan menginstal aplikasi tidak dikenal dari sumber ini", ketuk **Setelan → Izinkan dari sumber ini**, lalu kembali.
3. Ketuk **Instal**. Bila Play Protect memberi peringatan "aplikasi tidak dikenal", pilih **Detail lainnya → Tetap instal** (wajar untuk aplikasi internal yang belum ada di Play Store).
4. Buka **FES Report**, login dengan BN dan sandi. Login cukup sekali — tetap masuk sampai menekan **Keluar**.
5. Saat diminta, izinkan **Lokasi** (untuk GPS di laporan) dan **Kamera** (foto dokumentasi).

- Syarat: Android 7.0 atau lebih baru.
- Aplikasi berjalan tanpa internet (semua halaman ada di dalam APK).
- Nama paket: `com.leoghynizm.fesreport`, versi 1.6.0 (kode 1).

### Memperbarui APK
Versi baru harus ditandatangani dengan **kunci yang sama** (`FES-Kunci-Tanda-Tangan.zip`) dan `versionCode` lebih besar, agar bisa dipasang di atas versi lama tanpa menghapus data. **Simpan file kunci itu baik-baik dan jangan dibagikan** — jika hilang, pembaruan harus didahului hapus-instal (data login & laporan di HP ikut hilang).

### Membuat ulang APK sendiri (opsional)
Proyek ada di `FES-Android-Source-v1.6.zip`.
1. Ekstrak; salin `fes-release.jks` dan `keystore.properties` dari paket kunci ke folder proyek.
2. Salin isi folder `fes-web` (dari paket hosting) ke `app/src/main/assets/www/`.
3. Naikkan `versionCode` / `versionName` di `app/build.gradle`.
4. Buka di Android Studio lalu **Build → Generate Signed App Bundle / APK**, atau jalankan `./gradlew assembleRelease`. Hasil: `app/build/outputs/apk/release/app-release.apk`.
5. Untuk Google Play: `./gradlew bundleRelease` menghasilkan file `.aab`.

## B. iPhone / iPad — pasang dari Safari

Apple tidak mengizinkan file instal di luar App Store, jadi gunakan versi web (setelah paket hosting dipasang):
1. Buka `https://alamat-hosting/app.html` di **Safari**.
2. Ketuk tombol **Bagikan** → **Tambahkan ke Layar Utama** → **Tambah**.
3. Ikon FES Report muncul di layar utama dan terbuka layar penuh seperti aplikasi.

## C. Android tanpa APK / Laptop — pasang dari Chrome atau Edge

1. Buka `https://alamat-hosting/app.html` (aplikasi HP) atau `https://alamat-hosting/` (portal dashboard/admin).
2. Chrome: menu **⋮ → Instal aplikasi** (atau ikon instal di kolom alamat). Edge: **⋯ → Aplikasi → Instal situs ini sebagai aplikasi**.

## Akun

Semua akun memakai sandi awal `Fes12345` — ganti di **Pengaturan akun** setelah masuk. Admin FES dan Super Admin memakai login dua langkah.
