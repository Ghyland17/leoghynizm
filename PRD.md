# Product Requirements Document (PRD)
# FES Emergency Report

| Item | Keterangan |
|---|---|
| Nama produk | FES Emergency Report |
| Pemilik produk | Fire Emergency & Service (FES) – PT Vale Indonesia |
| Platform | Mobile (Android/iOS) + Web Dashboard |
| Lokasi penggunaan | Kolaka, Sulawesi Tenggara |
| Versi dokumen | 0.1 (Draft) |
| Tanggal | 5 November 2026 |
| Status | Draft – menunggu review tim FES |

> **Catatan:** Bagian bertanda **[ASUMSI]** adalah perkiraan penulis karena informasinya belum tersedia. Mohon diverifikasi oleh tim FES.

---

## 1. Latar Belakang

Saat ini tim Fire Emergency & Service (FES) PT Vale Indonesia membuat laporan respons darurat secara **manual menggunakan Excel**. Ada dua jenis laporan yang harus dibuat:

1. **Preliminary Report**: laporan awal yang harus segera dikirim setelah kejadian.
2. **ERR (Emergency Response Report)**: laporan lengkap dan resmi setelah respons ditutup.

### Masalah yang terjadi

| # | Masalah | Dampak |
|---|---|---|
| 1 | Laporan diketik manual di Excel | Memakan waktu dan rawan salah ketik, terutama pada jam dan durasi |
| 2 | Satu orang harus tetap **standby di kantor** untuk membuat laporan | Tim di lapangan tidak bisa turun dengan **kekuatan penuh (full team)** |
| 3 | Waktu (berangkat, tiba, padam, dan seterusnya) dicatat dari ingatan atau radio | Data waktu respons kurang akurat dan sulit diaudit |
| 4 | Jarak tempuh diperkirakan, bukan diukur | Tidak ada data objektif untuk evaluasi waktu respons |
| 5 | Foto bukti tersebar di HP pribadi atau grup chat | Sulit dikumpulkan, tidak terarsip, dan rawan hilang |
| 6 | Arsip laporan berupa file Excel terpisah-pisah | Sulit mencari data historis dan membuat rekap statistik |

---

## 2. Tujuan Produk

| # | Tujuan |
|---|---|
| G1 | **Menghilangkan kebutuhan petugas standby di kantor** untuk membuat laporan, sehingga seluruh tim dapat turun ke lapangan |
| G2 | **Mencatat timeline respons secara otomatis dan akurat** melalui tombol aksi yang ditekan sesuai kejadian aktual |
| G3 | **Mengukur jarak dan rute** dari pos ke TKP menggunakan GPS |
| G4 | **Menghasilkan Preliminary Report dan ERR secara otomatis** dari data yang tercatat |
| G5 | **Menyimpan foto evidence** yang terhubung langsung ke setiap kejadian |
| G6 | **Menyediakan arsip dan dashboard** data respons untuk evaluasi dan pelaporan manajemen |

### Di luar cakupan (Non-Goals) versi 1

- Menggantikan sistem dispatch atau radio komunikasi yang sudah ada.
- Integrasi otomatis dengan sistem HSE atau ERP perusahaan (direncanakan untuk fase berikutnya).
- Navigasi turn-by-turn seperti Google Maps.
- Fitur chat antar anggota tim.

---

## 3. Pengguna & Persona

### 3.1 Peran pengguna

| Peran | Deskripsi | Platform utama |
|---|---|---|
| **Petugas / Crew FES** | Anggota tim respons yang turun ke lapangan | Mobile |
| **Team Leader / Komandan Regu** | Memimpin respons, menekan tombol tahapan, dan menyetujui laporan dari lapangan | Mobile |
| **Supervisor / Koordinator FES** | Memeriksa, melengkapi, dan menyetujui laporan akhir (ERR) | Web + Mobile |
| **Admin Sistem** | Mengelola akun, unit kendaraan, pos, dan master data | Web |
| **Manajemen / Viewer** | Melihat dashboard dan arsip (hanya baca) | Web |

### 3.2 Persona

**Persona 1: Andi, Team Leader FES**
- Bertanggung jawab memimpin regu saat ada panggilan darurat.
- Saat respons, kedua tangannya sering sibuk dan ia memakai sarung tangan APD.
- Kebutuhan: **sesedikit mungkin interaksi dengan HP**, tombol besar, dan satu ketukan per tahapan.

**Persona 2: Rina, Supervisor FES**
- Bertugas menyerahkan laporan ke manajemen dan departemen HSE.
- Kebutuhan: laporan **cepat jadi, akurat, dan format standar**, serta dapat diekspor ke PDF/Excel.

**Persona 3: Pak Budi, Manajer**
- Kebutuhan: melihat **tren kejadian, rata-rata waktu respons**, dan arsip kejadian kapan saja.

---

## 4. User Stories

| ID | Sebagai… | Saya ingin… | Supaya… | Prioritas |
|---|---|---|---|---|
| US-01 | Team Leader | menekan **"Mulai Respons"** saat tim berangkat | waktu berangkat dan titik awal GPS tercatat otomatis | Must |
| US-02 | Team Leader | menekan tombol tahapan berikutnya (Tiba di TKP, Pemadaman, dll.) | setiap waktu tahapan tercatat tanpa menulis manual | Must |
| US-03 | Team Leader | melihat jarak tempuh dan durasi berjalan di layar | saya tahu progres respons secara real time | Must |
| US-04 | Petugas | mengambil foto evidence langsung dari aplikasi | foto otomatis tersimpan dengan waktu dan koordinat GPS | Must |
| US-05 | Team Leader | mengisi data singkat kejadian (jenis, lokasi, korban) | Preliminary Report bisa langsung dibuat | Must |
| US-06 | Team Leader | menekan **"Tutup Respons"** | respons resmi ditutup dan ERR draft dibuat otomatis | Must |
| US-07 | Supervisor | menerima notifikasi saat Preliminary Report siap | saya bisa segera meneruskannya ke manajemen | Must |
| US-08 | Supervisor | mereview, mengedit, dan menyetujui ERR | laporan final sesuai standar perusahaan | Must |
| US-09 | Supervisor | mengekspor laporan ke PDF dan Excel | saya bisa mengirimnya sesuai format yang berlaku | Must |
| US-10 | Manajemen | melihat dashboard statistik respons | saya bisa mengevaluasi kinerja tim | Should |
| US-11 | Supervisor | mencari arsip kejadian berdasarkan tanggal, jenis, dan lokasi | data historis mudah ditemukan | Must |
| US-12 | Petugas | tetap bisa mencatat tahapan saat sinyal hilang | data tidak hilang ketika berada di area tanpa sinyal | Must |
| US-13 | Supervisor | melihat posisi tim yang sedang respons di peta | saya bisa memantau tanpa harus berada di lapangan | Should |
| US-14 | Admin | mengelola akun petugas, unit kendaraan, dan pos | master data selalu terbarui | Must |

---

## 5. Fitur Utama

### 5.1 Tombol Respons (Response Timeline Button), fitur inti

Satu tombol besar di layar utama yang **berubah sesuai tahapan respons**. Setiap kali ditekan, sistem mencatat **waktu (timestamp)** dan **koordinat GPS** pada saat itu.

#### Tahapan respons

| # | Tahapan (Label tombol) | Data yang tercatat otomatis |
|---|---|---|
| 0 | **Terima Panggilan** | Waktu laporan diterima, sumber laporan, pelapor *(input singkat)* |
| 1 | **Mulai Respons / Berangkat** | Waktu berangkat, titik GPS awal (pos), unit dan kru yang ikut; **pelacakan rute dimulai** |
| 2 | **Tiba di TKP** | Waktu tiba, titik GPS TKP, **jarak tempuh** (km), **waktu perjalanan**; pelacakan rute berhenti |
| 3 | **Mulai Pemadaman / Penanganan** | Waktu mulai aksi |
| 4 | **Api Terkendali (Under Control)** | Waktu terkendali |
| 5 | **Api Padam (Extinguished)** | Waktu padam |
| 6 | **Pendinginan / Overhaul Selesai** | Waktu selesai |
| 7 | **Tutup Respons (Close)** | Waktu respons resmi ditutup, **memicu pembuatan ERR draft** |
| 8 | **Kembali ke Pos** *(opsional)* | Waktu tiba kembali di pos dan jarak pulang |

**[ASUMSI]** Tahapan di atas mengikuti praktik umum respons kebakaran. Daftar tahapan **harus bisa dikonfigurasi Admin** supaya sesuai dengan SOP FES PT Vale. Jenis kejadian non-kebakaran (rescue, medis, tumpahan B3) bisa memiliki tahapan berbeda.

#### Aturan & perilaku

- Tombol berukuran besar (minimal 1/3 layar) dan kontras tinggi agar **bisa ditekan dengan sarung tangan**.
- Tahapan berjalan berurutan. Tahapan yang tidak relevan dapat **dilewati** dengan konfirmasi.
- Ada fitur **Undo** dalam 10 detik bila tombol tertekan tidak sengaja.
- Setiap koreksi waktu setelah undo hanya boleh dilakukan Team Leader/Supervisor dan **tercatat di log audit** (nilai lama, nilai baru, siapa, dan kapan).
- Layar menampilkan: tahapan saat ini, stopwatch sejak berangkat, jarak tempuh berjalan, dan peta mini.
- Satu kejadian dapat diikuti **lebih dari satu unit**. Setiap unit memiliki timeline sendiri yang digabung dalam satu laporan kejadian.

### 5.2 Pelacakan GPS & Jarak

- Merekam rute perjalanan dari titik berangkat sampai TKP (interval ±5 detik atau setiap 20 meter).
- Menghitung **jarak tempuh aktual** berdasarkan rute, bukan garis lurus.
- Menyimpan koordinat TKP dan menampilkannya di peta.
- Mendukung **mode offline**: titik GPS tetap direkam di perangkat dan disinkronkan saat sinyal kembali (GPS satelit tidak membutuhkan internet).
- Menampilkan peringatan jika GPS mati atau akurasinya rendah (> 50 m).
- Pelacakan hanya aktif selama respons berjalan dan **berhenti otomatis** saat respons ditutup, demi privasi dan hemat baterai.

### 5.3 Foto Evidence

- Mengambil foto langsung dari kamera di dalam aplikasi, atau memilih dari galeri.
- Setiap foto otomatis diberi **watermark/metadata**: tanggal, jam, koordinat GPS, nomor kejadian, dan nama pengambil.
- Foto dikaitkan ke tahapan saat diambil (misalnya "Tiba di TKP" atau "Api Padam").
- Bisa diberi keterangan singkat (opsional, juga lewat **input suara**).
- Foto dikompresi sebelum diunggah dan **antre unggah otomatis** saat offline.
- Foto yang sudah diunggah **tidak bisa dihapus** oleh petugas (hanya ditandai "tidak dipakai" oleh Supervisor) untuk menjaga integritas evidence.

### 5.4 Preliminary Report Otomatis

- Dibuat otomatis setelah tahapan **"Tiba di TKP"** dan data singkat kejadian terisi.
- Isi minimal **[ASUMSI, sesuaikan dengan template Excel yang sekarang]**:
  - Nomor kejadian (otomatis), tanggal & jam kejadian
  - Jenis kejadian (kebakaran, rescue, medis, B3, false alarm, dan lain-lain)
  - Lokasi (nama area + koordinat GPS + peta)
  - Pelapor dan waktu laporan diterima
  - Unit dan kru yang merespons
  - Waktu berangkat, waktu tiba, jarak tempuh, response time
  - Kondisi awal di TKP (singkat) dan korban/cedera (jika ada)
  - 1–3 foto awal
- Bisa dikirim (notifikasi + tautan/PDF) ke Supervisor dan daftar penerima yang ditentukan.
- Ditandatangani digital oleh Team Leader sebelum dikirim.

### 5.5 ERR (Emergency Response Report) Otomatis

- Draft ERR dibuat otomatis saat **"Tutup Respons"** ditekan.
- Seluruh data timeline, GPS, foto, kru, dan unit **terisi otomatis**.
- Kolom yang perlu dilengkapi manual: kronologi, penyebab dugaan, tindakan yang dilakukan, peralatan/APAR/air yang digunakan, kerusakan, korban, rekomendasi, dan lesson learned.
- **Alur persetujuan:** Draft → Diajukan (Team Leader) → Direview (Supervisor) → **Disetujui (Final)**. Laporan final terkunci dan hanya bisa direvisi dengan versi baru.
- Ekspor ke **PDF** (format resmi dengan logo) dan **Excel** (agar kompatibel dengan format lama).
- **Template laporan mengikuti format Excel yang sekarang dipakai FES**, sehingga tidak mengubah kebiasaan pembaca laporan.

### 5.6 Dashboard & Arsip (Web)

**Dashboard**
- Jumlah kejadian per periode (harian, bulanan, tahunan) dan per jenis kejadian.
- **Rata-rata response time** (laporan diterima → tiba di TKP) dan tren dari waktu ke waktu.
- Rata-rata durasi penanganan (tiba → padam → tutup).
- Peta sebaran lokasi kejadian (heatmap).
- Respons yang **sedang berjalan** (live) beserta posisi unit.
- Status laporan: Draft / Menunggu review / Final.

**Arsip**
- Daftar seluruh kejadian dengan pencarian dan filter (tanggal, jenis, area, unit, status).
- Halaman detail kejadian: timeline, peta rute, galeri foto, Preliminary Report, ERR, dan log audit.
- Ekspor rekap ke Excel.

### 5.7 Manajemen Pengguna & Master Data (Web)

- Akun pengguna dan peran (lihat bagian 3.1). Login memakai NIK/ID karyawan.
- Master data: pos/stasiun, unit kendaraan, jenis kejadian, area/lokasi, tahapan respons, dan template laporan.
- Pengaturan daftar penerima notifikasi laporan.

### 5.8 Notifikasi

- Push notification ke Supervisor saat: respons dimulai, tim tiba di TKP, Preliminary Report siap, respons ditutup, dan ERR menunggu review.
- **[Fase 2]** Notifikasi melalui email/WhatsApp ke manajemen.

---

## 6. Alur Pengguna (User Flow)

### 6.1 Alur respons di lapangan (Mobile)

```
Panggilan darurat masuk (radio/telepon)
        │
        ▼
[Team Leader buka aplikasi] → pilih jenis kejadian, lokasi, unit & kru
        │
        ▼
[MULAI RESPONS]  ── waktu berangkat + GPS awal, rute mulai direkam
        │
        ▼
[TIBA DI TKP]    ── waktu tiba + jarak tempuh + durasi perjalanan
        │                └─► Preliminary Report otomatis → notifikasi Supervisor
        ▼
[MULAI PEMADAMAN] → [API TERKENDALI] → [API PADAM] → [OVERHAUL SELESAI]
        │      (foto evidence bisa diambil kapan saja)
        ▼
[TUTUP RESPONS]  ── respons resmi ditutup → ERR draft otomatis
        │
        ▼
[KEMBALI KE POS] (opsional)
```

### 6.2 Alur pelaporan (Web/Mobile)

```
ERR Draft → Team Leader melengkapi narasi → Ajukan
        → Supervisor review/edit → Setujui → ERR Final (terkunci, PDF/Excel)
        → Masuk arsip & dashboard
```

---

## 7. Kebutuhan Non-Fungsional

| Kategori | Kebutuhan |
|---|---|
| **Kemudahan pakai** | Tombol aksi minimal 1/3 layar dan bisa dipakai dengan sarung tangan; mode kontras tinggi agar terbaca di bawah sinar matahari; mode gelap untuk malam hari |
| **Offline-first** | Seluruh tombol tahapan, GPS, dan foto **tetap berfungsi tanpa internet**, lalu disinkronkan otomatis saat online kembali |
| **Akurasi waktu** | Waktu menggunakan jam server/NTP saat online. Saat offline memakai jam perangkat dan diberi penanda; zona waktu **WITA (UTC+8)** |
| **Kinerja** | Respons tombol < 300 ms; Preliminary Report terbentuk < 10 detik setelah data lengkap |
| **Baterai** | Pelacakan GPS selama 1 jam respons menghabiskan < 15% baterai **[ASUMSI target]** |
| **Keamanan** | Login per pengguna (bukan akun bersama); HTTPS/TLS; data terenkripsi; akses berbasis peran; sesi otomatis berakhir; dukungan SSO perusahaan **[jika tersedia]** |
| **Integritas data** | Log audit untuk setiap perubahan data waktu dan laporan; laporan final tidak bisa diubah |
| **Ketersediaan** | Uptime server ≥ 99,5%; backup database harian |
| **Retensi data** | Arsip disimpan minimal 5 tahun **[ASUMSI, sesuaikan dengan kebijakan perusahaan]** |
| **Kompatibilitas** | Android 9+ dan iOS 15+; browser Chrome, Edge, dan Safari versi terbaru |
| **Bahasa** | Bahasa Indonesia (utama); Bahasa Inggris untuk ekspor laporan **[opsional]** |
| **Kepatuhan** | Mengikuti kebijakan IT & keamanan data PT Vale Indonesia (termasuk lokasi server/hosting) |

---

## 8. Model Data (Garis Besar)

| Entitas | Atribut utama |
|---|---|
| **Incident** (Kejadian) | id, nomor_kejadian, jenis, area, koordinat_tkp, pelapor, waktu_laporan, status, dibuat_oleh |
| **ResponseUnit** | id, incident_id, unit_kendaraan_id, team_leader_id, daftar_kru |
| **TimelineEvent** | id, response_unit_id, tahapan, timestamp, lat, lng, akurasi_gps, sumber_waktu (server/perangkat), dicatat_oleh |
| **GpsTrack** | id, response_unit_id, daftar titik (lat, lng, timestamp), jarak_total_km |
| **Evidence** (Foto) | id, incident_id, tahapan, file_url, lat, lng, timestamp, keterangan, diambil_oleh |
| **PreliminaryReport** | id, incident_id, isi, status, ditandatangani_oleh, waktu_kirim |
| **ERR** | id, incident_id, versi, isi_narasi, status (draft/diajukan/direview/final), disetujui_oleh, waktu_final |
| **AuditLog** | id, entitas, field, nilai_lama, nilai_baru, user_id, timestamp |
| **User / Unit / Station / IncidentType / ResponseStage** | master data |

---

## 9. Usulan Arsitektur & Teknologi

> Rekomendasi awal. Keputusan akhir perlu disesuaikan dengan kebijakan IT PT Vale Indonesia, misalnya kewajiban server on-premise atau cloud yang sudah disetujui.

| Komponen | Rekomendasi | Alasan |
|---|---|---|
| **Aplikasi Mobile** | **Flutter** (satu kode untuk Android & iOS) | Performa baik, dukungan GPS di latar belakang, kamera, dan database lokal untuk offline |
| **Penyimpanan offline** | SQLite / Drift atau Isar di perangkat + antrean sinkronisasi | Mendukung offline-first |
| **Web Dashboard** | **Next.js (React)** + library peta (Leaflet/Mapbox) + grafik | Cepat dikembangkan dan cocok untuk dashboard |
| **Backend API** | Node.js (NestJS) **atau** Laravel | Ekosistem luas, mudah mencari developer lokal |
| **Database** | **PostgreSQL + PostGIS** | Mendukung data lokasi/geospasial (rute, jarak, heatmap) |
| **Penyimpanan foto** | Object storage (S3-compatible / MinIO on-premise) | Skalabel untuk file foto |
| **Notifikasi** | Firebase Cloud Messaging | Push notification Android & iOS |
| **Pembuatan laporan** | Generator PDF (template HTML → PDF) & Excel (template .xlsx) | Mengikuti format laporan yang sudah ada |
| **Peta** | OpenStreetMap / Google Maps SDK + **peta area tambang internal** (jika tersedia) | Banyak lokasi di area perusahaan tidak tercantum di peta umum |

```
[Mobile App (Flutter)] ──┐
                         ├──► [Backend API] ──► [PostgreSQL + PostGIS]
[Web Dashboard (Next.js)]┘          │
                                    ├──► [Object Storage (foto, PDF)]
                                    └──► [FCM (notifikasi)]
```

---

## 10. Metrik Keberhasilan

| Metrik | Kondisi sekarang | Target (3 bulan setelah rilis) |
|---|---|---|
| Petugas yang harus standby di kantor untuk membuat laporan | 1 orang per respons | **0 orang** |
| Waktu pembuatan Preliminary Report | **[perlu diukur]** | **< 5 menit** setelah tiba di TKP |
| Waktu penyelesaian ERR final | **[perlu diukur]** | **< 24 jam** setelah respons ditutup |
| Persentase respons yang tercatat lewat aplikasi | 0% | **≥ 95%** |
| Kelengkapan data waktu (semua tahapan wajib terisi) | **[perlu diukur]** | **≥ 98%** |
| Laporan dengan foto evidence | **[perlu diukur]** | **100%** |
| Kepuasan pengguna (survei tim FES) | – | **≥ 4/5** |

---

## 11. Rencana Rilis & Timeline

**[ASUMSI]** Estimasi untuk tim kecil (1 PM, 2–3 developer, 1 UI/UX, 1 QA).

| Fase | Durasi | Cakupan |
|---|---|---|
| **Fase 0: Discovery** | 2 minggu | Mengumpulkan template Excel Preliminary & ERR saat ini, SOP tahapan respons, kebijakan IT, wawancara tim FES |
| **Fase 1: Desain** | 2–3 minggu | Wireframe, prototipe, uji coba prototipe dengan Team Leader |
| **Fase 2: MVP** | 8–10 minggu | Tombol respons, GPS & jarak, foto evidence, Preliminary & ERR otomatis, ekspor PDF/Excel, arsip dasar, manajemen user, mode offline |
| **Fase 3: Pilot** | 4 minggu | Uji coba dengan 1 regu/pos di Kolaka, berjalan **paralel dengan Excel** sebagai cadangan |
| **Fase 4: Rilis penuh** | 2 minggu | Pelatihan seluruh anggota FES, Excel manual dihentikan |
| **Fase 5: Pengembangan lanjut** | Berkelanjutan | Dashboard analitik lanjutan, pelacakan live di peta, notifikasi WhatsApp/email, integrasi sistem HSE, input suara |

### Prioritas fitur (MoSCoW)

- **Must have (MVP):** Tombol respons + timeline, GPS & jarak, foto evidence, Preliminary Report otomatis, ERR otomatis + alur persetujuan, ekspor PDF/Excel, arsip & pencarian, mode offline, manajemen user, log audit.
- **Should have:** Dashboard statistik, pelacakan posisi live, push notification lengkap, multi-unit dalam satu kejadian.
- **Could have:** Input suara untuk keterangan, heatmap kejadian, laporan bahasa Inggris, mode gelap.
- **Won't have (v1):** Integrasi ERP/HSE, chat tim, navigasi turn-by-turn.

---

## 12. Risiko & Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Sinyal internet lemah atau tidak ada di area tambang/hutan | Data tidak terkirim | Desain **offline-first** dengan sinkronisasi otomatis |
| Petugas lupa menekan tombol saat situasi kritis | Data waktu tidak akurat | Tombol besar, pengingat getar/suara, koreksi oleh Team Leader dengan log audit, opsi **Bluetooth button** atau tombol di kendaraan (fase lanjut) |
| HP rusak, basah, atau terlalu panas di lokasi kebakaran | Data hilang | Gunakan **HP rugged dinas**, sinkronisasi berkala, data tersimpan di server secepatnya |
| Baterai habis saat respons panjang | Pencatatan terhenti | Optimasi GPS, charger di kendaraan, peringatan baterai rendah |
| Penolakan perubahan dari kebiasaan Excel | Adopsi rendah | Format laporan **mengikuti template lama**, pelatihan, masa pilot paralel |
| Kebijakan keamanan data perusahaan | Penundaan rilis | Libatkan tim IT PT Vale sejak Fase 0 |
| Akurasi GPS rendah di area tertutup | Jarak kurang akurat | Penyaringan titik GPS, tampilkan nilai akurasi, Team Leader bisa mengoreksi titik TKP |

---

## 13. Pertanyaan Terbuka

1. Seperti apa **template Excel Preliminary Report dan ERR** yang dipakai saat ini? (sangat dibutuhkan untuk Fase 0)
2. Apa saja **tahapan respons resmi** menurut SOP FES PT Vale?
3. Siapa saja yang **wajib menerima** Preliminary Report, dan berapa batas waktunya?
4. Apakah petugas memakai **HP pribadi atau HP dinas**?
5. Apakah ada kebijakan **server on-premise** atau cloud tertentu yang wajib dipakai?
6. Apakah tersedia **SSO/Active Directory** perusahaan untuk login?
7. Apakah ada **peta digital area perusahaan** yang bisa dipakai?
8. Berapa jumlah anggota FES, pos, dan unit kendaraan yang akan memakai aplikasi?
9. Selain kebakaran, jenis kejadian apa saja yang ditangani (rescue, medis, B3, dan lain-lain)?
10. Berapa lama data harus disimpan menurut kebijakan perusahaan?

---

## 14. Glosarium

| Istilah | Arti |
|---|---|
| **FES** | Fire Emergency & Service |
| **ERR** | Emergency Response Report: laporan lengkap respons darurat |
| **Preliminary Report** | Laporan awal kejadian yang dikirim segera |
| **TKP** | Tempat Kejadian Perkara (lokasi kejadian) |
| **Response time** | Selisih waktu antara laporan diterima dan tim tiba di TKP |
| **Overhaul** | Tahap pendinginan dan pemeriksaan sisa api setelah padam |
| **Offline-first** | Aplikasi tetap berfungsi tanpa internet dan menyinkronkan data saat online |
| **MVP** | Minimum Viable Product: versi pertama dengan fitur inti |
