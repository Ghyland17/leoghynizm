# Product Requirements Document (PRD)
# FES Emergency Report

| Item | Keterangan |
|---|---|
| Nama produk | FES Emergency Report |
| Pemilik produk | Fire Emergency & Service (FES) – PT Vale Indonesia |
| Platform | Mobile (Android/iOS) + Web Dashboard |
| Lokasi penggunaan | Kolaka, Sulawesi Tenggara |
| Versi dokumen | 0.6 (Draft – Target Time diisi tim respons, HP pribadi) |
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
| US-06 | Team Leader | menekan **"Leave Scene / Case Closed"** lalu **"Return to Stn"** saat tiba di fire station | respons resmi ditutup dan ERR draft dibuat otomatis | Must |
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

Tombol **wajib** dipetakan langsung ke 4 kolom waktu di form ERR (blok *RESPON TIME*). Tombol **tambahan** tidak punya kolom sendiri di ERR, tetapi otomatis menjadi baris kronologi bertimestamp di kolom *Detail Informasi*.

| # | Tahapan (Label tombol) | Jenis | Kolom ERR | Data yang tercatat otomatis |
|---|---|---|---|---|
| 1 | **Dispatch Call** (panggilan diterima) | Wajib | `Dispatch Call` | Waktu panggilan diterima, sumber info (*Informasi di Terima dari*, misalnya ERT Coordinator). Tim respons juga **mengisi Target Time** untuk lokasi ini (wajib diisi sebelum tombol Start to scene aktif) |
| 2 | **Start to Scene** (berangkat) | Wajib | `Start to scene` | Waktu berangkat, titik GPS awal, Response Station, unit & personel; **pelacakan rute dimulai** |
| 3 | **Arrival on Scene** (tiba di TKP) | Wajib | `Arrival on scene` | Waktu tiba, titik GPS TKP, **jarak tempuh → `Mileage (KM)`**; pelacakan rute berhenti; **memicu Preliminary Report** |
| 4 | Size-up / Code (misalnya *code red*) | Tambahan | Kronologi | Waktu + kode situasi |
| 5 | Mulai Pemadaman / Penanganan | Tambahan | Kronologi | Waktu + metode/peralatan (misalnya Jet Shooter) |
| 6 | Api Terkendali / Padam / Reposisi / Dihentikan | Tambahan | Kronologi | Waktu + catatan singkat |
| 7 | Leave Scene / Case Closed | Tambahan | Kronologi | Waktu tim meninggalkan TKP ("Case closed"); pelacakan rute pulang dimulai |
| 8 | **Return to Stn** (tiba di fire station) | Wajib | `Return to Stn` | Waktu tim **tiba kembali di fire station**, jarak pulang; respons resmi ditutup dan **ERR draft dibuat** |

Selain tombol tahapan, ada tombol **"+ Catatan"** untuk menambahkan kejadian penting kapan saja, misalnya *"Api yang mengarah ke pesantren berhasil dipadamkan"*. Waktu catatan terisi otomatis, dan teksnya bisa diketik atau didiktekan dengan suara. Catatan ini menjadi baris kronologi ERR dengan format `- HH:MM <keterangan>`, sama seperti yang sekarang diketik manual.

Daftar tahapan tambahan **bisa dikonfigurasi Admin** sesuai SOP FES dan jenis respons (FIRE, RESCUE, MVA, HAZMAT, dan lain-lain).

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

Template Excel: [`templates/Preliminary_Report_Template.xlsx`](templates/Preliminary_Report_Template.xlsx). Sheet *PRELIMINARY FORM* berisi form kosong dan sheet *CONTOH (Watalara)* berisi contoh pengisian dari data kejadian 31/10/2026.

**Dasar format.** Belum ada satu format baku Preliminary Report. Format ini disusun dari tiga acuan:
- pola laporan awal di regulator tambang dan dinas pemadam (laporan awal singkat dalam hitungan jam, lalu laporan final);
- flash report ala *Incident Command System*, yaitu laporan berstatus *Initial / Update* yang informasinya boleh belum lengkap;
- **gaya, istilah, dan daftar pilihan form ERR FES**, supaya Preliminary dan ERR terasa satu keluarga dan datanya bisa dipakai ulang.

**Kapan dibuat & dikirim**
- Draft dibuat otomatis saat tombol **"Arrival on scene"** ditekan. Team Leader cukup melengkapi size-up singkat, lalu mengirimnya.
- Target terkirim **≤ 15 menit setelah tiba di TKP** (disetujui FES; bisa diatur Admin).
- Laporan bisa dikirim ulang sebagai **UPDATE 1, 2, 3…** bila situasi berubah (misalnya korban bertambah atau api terkendali). Setiap update menyimpan versi sebelumnya.
- Nomor laporan **sama dengan nomor ERR** (format 5.5.2), sehingga Preliminary dan ERR satu kejadian saling terhubung.
- Dikirim sebagai notifikasi + PDF ke daftar distribusi yang diatur Admin (default: Supervisor FES, ERT Coordinator, HSE Department, Manajemen Area). Sebelum dikirim, Team Leader menandatanganinya secara digital.

**Isi form & sumber data** (Auto = otomatis, Master = pilih dari master data, Input = diisi petugas)

| Bagian | Field | Sumber |
|---|---|---|
| Header | NO, REPORT STATUS (INITIAL/UPDATE n), DATE, TIME SENT | Auto |
| | REGION, RESPONSE STATION | Master |
| | LOCATION, GPS COORDINATE, Mileage (KM) | Input + Auto (GPS) |
| | WEATHER / WIND | Input (pilihan cepat) |
| A. Respon Time | Dispatch Call, Start to scene, Arrival on scene | Auto (tombol) |
| | On Scene Time, **Total Response Time** (Dispatch s/d Arrival) | Auto (hitung) |
| | Target Time | Input (tim respons, saat Dispatch Call) |
| B. Klasifikasi | RESPONSE TYPE, INCIDENT TYPE, SUB TYPE (daftar sama dengan ERR) | Input (pilih) |
| | SITUATION CODE (Code Green / Yellow / Red) | Input (pilih) |
| C. Informasi Awal | Informasi di Terima dari, Pelapor/Kontak, Detail Informasi | Input (diisi saat Dispatch Call) |
| D. Kondisi Awal (Size-up) | Kondisi saat tiba, Potensi bahaya/eskalasi, Akses lokasi | Input (teks/suara) |
| E. Korban (Sementara) | Meninggal, Luka Berat, Luka Ringan, Hilang/Terjebak, Dievakuasi, Triase G/Y/R/B, Total, Dirujuk ke | Input (angka) + Auto (total) |
| F. Sumber Daya | Respon Vehicle, Personil On Duty, Medical Sup. | Auto (dari Dispatch) |
| | Off Duty Recall / Support: nama/ID/instansi, kategori, **jam dipanggil, jam tiba** | Master + Auto (tombol) |
| | Bantuan yang dibutuhkan | Input |
| G. Tindakan & Rencana | Tindakan yang sudah dilakukan, Rencana berikutnya, Perkiraan update berikutnya, Status respons (On going / Under control / Selesai) | Input + Auto (dari kronologi) |
| H. Foto Awal | 1–3 foto evidence pertama | Auto (dari galeri kejadian) |
| Penutup | Distribusi, Reported by On Duty Leaders Operation, tanda tangan | Auto |

**Hubungan dengan ERR.** Semua data Preliminary (waktu, klasifikasi, informasi, personel, korban) **otomatis terbawa ke draft ERR**, sehingga petugas tidak mengetik ulang. Di ERR, data korban Preliminary diperinci lagi menjadi Adult/Child/Infant × Male/Female dan Casualty Details.

### 5.5 ERR (Emergency Response Report) Otomatis

- Draft ERR dibuat otomatis saat **"Return to Stn"** ditekan (tim tiba kembali di fire station).
- Seluruh data timeline, GPS, foto, kru, dan unit **terisi otomatis**.
- Kolom yang perlu dilengkapi manual: kronologi, penyebab dugaan, tindakan yang dilakukan, peralatan/APAR/air yang digunakan, kerusakan, korban, rekomendasi, dan lesson learned.
- **Alur persetujuan:** Draft → Diajukan (Team Leader) → Direview (Supervisor) → **Disetujui (Final)**. Laporan final terkunci dan hanya bisa direvisi dengan versi baru.
- Ekspor ke **PDF** (format resmi dengan logo FES & Vale) dan **Excel** (file `.xlsx` yang **identik dengan form "ERR FORM" saat ini**). Nama file mengikuti pola yang sekarang dipakai: `ERR_<DD-MM-YYYY>_<Lokasi>.xlsx`, contoh `ERR_31-10-2026_Watalara.xlsx`.

#### 5.5.1 Pemetaan field form ERR → sumber data aplikasi

Acuan: file `ERR_31-10-2026_Watalara.xlsx` (sheet **ERR FORM** dan **DATA**). Form yang sudah disesuaikan, dengan tambahan kolom **Jam Tiba** untuk Off Duty Recall dan Support, ada di [`templates/ERR_Form_Template.xlsx`](templates/ERR_Form_Template.xlsx). Layout, checkbox, dropdown, dan rumusnya tetap sama dengan aslinya.

Keterangan sumber: **Auto** = terisi otomatis oleh aplikasi · **Master** = dipilih dari master data · **Input** = diisi petugas (pilihan cepat/teks).

| Bagian form | Field | Sumber | Keterangan |
|---|---|---|---|
| Header | NO | Auto | Nomor otomatis dengan **format yang bisa diubah Admin** (lihat 5.5.2). Default: `ESS/Pom/{NNN}/{MM}/{YYYY}` |
| | DATE | Auto | Tanggal kejadian (dari Dispatch Call) |
| | REGION | Master | Default `SULAWESI TENGGARA, KOLAKA` |
| | LOCATION | Input + Auto | Nama lokasi (misalnya *Desa Watalara*) + koordinat GPS TKP |
| | RESPONSE STATION | Master | FIRE STATION FAP FOX-5, FIRST AID STATION FOX-4, OPS. CAMP FOX-3, GROUND TRAINING DOJO, CONSTRUCTION OFFICE FOX-2, ON CALL, CO/MGH, NAR |
| | Mileage (KM) | **Auto (GPS)** | Jarak rute aktual dari station ke TKP. Menggantikan tabel jarak manual di sheet DATA |
| RESPON TIME | Dispatch Call | **Auto (tombol 1)** | |
| | Start to scene | **Auto (tombol 2)** | |
| | Arrival on scene | **Auto (tombol 3)** | |
| | Return to Stn | **Auto (tombol 8)** | Waktu tim tiba kembali di fire station |
| | On Scene Time (Mnts) | Auto (hitung) | `Arrival on scene − Start to scene` (rumus sama dengan Excel; contoh 0:13) |
| | Respond Time (Mnts) | Auto (hitung) | `Return to Stn − Arrival on scene`: lama di lokasi ditambah perjalanan pulang ke station |
| | **Total Response Time (Mnts)** *(baris tambahan)* | Auto (hitung) | `Arrival on scene − Dispatch Call` (contoh 12:40 → 12:54 = 0:14). Ditampilkan menyatu dengan blok RESPON TIME dan diberi keterangan *"Dispatch Call s/d Arrival on scene"*. Dipakai sebagai KPI utama di dashboard |
| | Target Time (Mnts) | **Input** | **Diisi tim respons sebelum berangkat** (saat Dispatch Call), karena target berbeda-beda per lokasi. Tidak ada nilai default. Aplikasi bisa menampilkan target yang pernah dipakai untuk lokasi yang sama sebagai **saran**, tapi tim tetap yang menentukan. Perubahan setelah berangkat tercatat di log audit |
| | Time Gap (Mnts) | Tetap 0 | **Sesuai keputusan FES, nilai tetap 0** seperti form sekarang. Kolom tetap ada di ekspor supaya layout tidak berubah. Rumusnya bisa diaktifkan Admin nanti (usulan: `On Scene Time − Target Time`) |
| RESPON VEHICLE | FS R6, FS E8, EMS6, dll. | Master (pilih) | Unit kendaraan yang ikut respons; bisa lebih dari satu |
| RESPON PERSONIL & NURSE (BN) | On Duty | Master (pilih) | Daftar ID karyawan (misalnya C041489) |
| | Medical Sup. | Master (pilih) | Personel medis/nurse |
| | Off Duty Recall | Master (pilih) + **Auto (jam)** | Personel off-duty yang dipanggil, beserta **jam dipanggil dan jam tiba** di TKP. Jam tiba dicatat lewat tombol "Tiba" per orang. Di form ERR, kolom pertama berisi ID dan kolom kedua **Jam Tiba** (header: *Off Duty Recall (ID \| Jam Tiba)*). Jam tiba juga otomatis masuk kronologi (`- 13:05 Off duty recall C0xxxxx tiba di TKP`) |
| | Support from Nearest Station | Input/Master + **Auto (jam)** | Bantuan dari station lain atau instansi luar (misalnya **BPBD**), beserta **jam diminta dan jam tiba**. Di form ERR, kolomnya: *Instansi \| Ket. \| Jam Tiba*. Juga otomatis masuk kronologi |
| DESCRIPTION OF CASUALTIES | Casualty Numbers | Input (angka) | Adult / Child / Infant × Male / Female, plus centang *Mass casualty* |
| | Total Casualties | Auto (hitung) | Jumlah seluruh korban |
| | Casualty Details | Input (centang) | Employee, Contractor, Dependent Vale, Dependent Contractor, Community |
| | Casualty Conditions (triase) | Input (centang) | Green, Yellow, Red, Black |
| Klasifikasi | RESPONSE TYPE | Input (pilih) | ALARM, FIRE, MVA (Motor Vehicle Accident), RESCUE, HAZMAT, OIL SPILL, PATIENT MOBILISATION (MEDICAL) |
| | INCIDENT TYPE | Input (pilih) | INDUSTRIAL, NON INDUSTRIAL, MEDICAL |
| Informasi | Informasi di Terima dari | Input (pilih) | Misalnya ERT Coordinator (diisi saat Dispatch Call) |
| | Detail Informasi | Input | Ringkasan awal kejadian |
| | Kronologi | **Auto** | Disusun otomatis dari tombol tahapan + "+ Catatan", format `- HH:MM keterangan`; bisa diedit sebelum diajukan |
| Detail per jenis respons (hanya bagian yang sesuai jenis respons yang ditampilkan) | FIRE | Input (pilih) | Structural, Electrical, Equipment, Sulphur, Compressed Cylinder, Vehicle/Motor, House, Tank Farm, Wildland Fire; *Other Fire (Explain)* |
| | ALARM | Input | Alarm detection / Trouble-False; Zone/Equipt. Address; FES Tag Number; Other Alarm Activation |
| | MVA | Input (pilih) | Single Vehicle, Multi Vehicle, Vehicle VS Motorcycle, Other Vehicle, Single Motor, Motor VS Motor, Motor VS People; *Other MVA (Explain)* |
| | RESCUE | Input (pilih) | Fire Search & Rescue, LSAR, Water Rescue, Confined Space, Trench, Vehicle Extrication, Vertical/Height, People Entrapment; *Other Rescue (Explain)* |
| | HAZMAT / OIL SPILL | Input | Name of Chemical, HAZMAT Class/HAZCHEM Code, Type of Container, Volume of Container |
| Penutup | Reported by On Duty Leaders Operation | Auto | Nama Team Leader yang login + tanda tangan digital |
| | DATE | Auto | Tanggal pengajuan laporan |

**Catatan desain:**
- Semua daftar pilihan (dropdown) di atas diambil dari *data validation* Excel yang sekarang. Di aplikasi daftar ini menjadi **master data yang bisa diubah Admin**.
- Form di HP menampilkan **hanya bagian yang relevan** dengan jenis respons yang dipilih. Contohnya, untuk FIRE bagian MVA/RESCUE/HAZMAT disembunyikan. Hasil ekspor tetap memakai layout form lengkap.
- Dengan pemetaan ini, **±60% isi ERR terisi otomatis** (header, seluruh waktu & durasi, mileage, unit, personel, kronologi, pelapor). Petugas cukup memilih jenis respons, mengisi korban, dan meninjau kronologi.

#### 5.5.2 Format Nomor ERR (bisa diubah)

Nomor ERR dibuat otomatis dari **template yang diatur Admin** di menu Pengaturan. Format tidak ditanam di kode, jadi bisa diganti kapan saja tanpa memperbarui aplikasi.

| Token | Arti | Contoh |
|---|---|---|
| `{NNN}` | Nomor urut (jumlah `N` = jumlah digit, diisi nol di depan) | `007` |
| `{MM}` / `{ROMAWI}` | Bulan angka / bulan Romawi | `10` / `X` |
| `{YYYY}` / `{YY}` | Tahun | `2026` / `26` |
| `{STATION}` | Kode Response Station | `FOX5` |
| `{TYPE}` | Kode jenis respons | `FIRE` |
| Teks bebas | Awalan/pemisah tetap | `ESS/Pom/` |

- **Default:** `ESS/Pom/{NNN}/{MM}/{YYYY}` → `ESS/Pom/007/10/2026`.
- **Reset nomor urut** bisa dipilih: per tahun (default), per bulan, atau tidak pernah reset.
- Nomor dipesan di server saat ERR dibuat, supaya tidak ada nomor ganda walaupun beberapa HP membuat laporan bersamaan. Kalau dibuat saat offline, laporan memakai nomor sementara (`DRAFT-…`) dan menerima nomor resmi begitu tersinkron.
- Perubahan format hanya berlaku untuk laporan baru. Nomor laporan lama tidak berubah, dan setiap perubahan format tercatat di log audit.

### 5.6 Dashboard & Arsip (Web)

**Dashboard**
- Jumlah kejadian per periode (harian, bulanan, tahunan) dan per jenis kejadian.
- **Rata-rata Total Response Time** (Dispatch Call → Arrival on scene) dibandingkan dengan Target Time, beserta trennya dari waktu ke waktu.
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
- Master data:
  - **Response Station**: FIRE STATION FAP FOX-5, FIRST AID STATION FOX-4, OPS. CAMP FOX-3, GROUND TRAINING DOJO, CONSTRUCTION OFFICE FOX-2, ON CALL, CO/MGH, NAR (beserta koordinat GPS masing-masing).
  - **Lokasi/area umum**: Camp Tetenggala, Camp Manado, MCO, Main Gate, Dormi/FOX-4, Camp Pad PAMA, Ground Training/DOJO, Ops. Camp, Gate NAR, Construction Office CO, dan seterusnya (beserta koordinat).
  - **Unit kendaraan**: FS R6, FS E8, EMS6, dan lainnya.
  - **Personel**: ID karyawan, nama, peran (On Duty / Medical / Off Duty).
  - **Instansi pendukung**: BPBD, Damkar setempat, dll.
  - **Jenis respons, jenis insiden, dan sub-jenis** (FIRE/ALARM/MVA/RESCUE/HAZMAT), tahapan respons tambahan, dan template laporan. (*Target Time tidak termasuk master data karena diisi per kejadian oleh tim respons.*)
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
[DISPATCH CALL]  ── waktu panggilan + sumber info (misalnya ERT Coordinator)
        │            pilih Response Station, unit (R6/E8/EMS6) & personel
        ▼
[START TO SCENE] ── waktu berangkat + GPS awal, rute mulai direkam
        │
        ▼
[ARRIVAL ON SCENE] ── waktu tiba + Mileage (KM) otomatis + On Scene Time
        │                └─► Preliminary Report otomatis → notifikasi Supervisor
        ▼
[Tahapan tambahan & "+ Catatan"] → kronologi bertimestamp
        │      (code red, pemadaman, reposisi, dll.; foto evidence bisa kapan saja)
        ▼
[LEAVE SCENE / CASE CLOSED] ── masuk kronologi, rute pulang mulai direkam
        │
        ▼
[RETURN TO STN] ── tiba di fire station + Respond Time → respons ditutup, ERR draft otomatis
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
| **HP pribadi (BYOD)** | Petugas memakai **HP pribadi**, sehingga: (1) data aplikasi disimpan **terenkripsi di dalam aplikasi**, terpisah dari data pribadi; (2) foto evidence **tidak masuk ke galeri pribadi** dan dihapus dari HP setelah terunggah; (3) GPS **hanya aktif selama respons berjalan**, tanpa pelacakan di luar tugas; (4) Admin bisa **mencabut akses & menghapus data aplikasi dari jarak jauh** jika HP hilang atau anggota pindah tugas; (5) login dengan PIN/biometrik; (6) ukuran aplikasi kecil dan hemat kuota data (foto dikompresi; unggah foto asli bisa diatur hanya via Wi-Fi) |
| **Integritas data** | Log audit untuk setiap perubahan data waktu dan laporan; laporan final tidak bisa diubah |
| **Ketersediaan** | Uptime server ≥ 99,5%; backup database harian |
| **Retensi data** | Arsip disimpan minimal 5 tahun **[ASUMSI, sesuaikan dengan kebijakan perusahaan]** |
| **Kompatibilitas** | Android 9+ dan iOS 15+ (rentang luas karena HP pribadi beragam merek); berjalan baik di HP kelas menengah ke bawah (RAM 3 GB); browser Chrome, Edge, dan Safari versi terbaru |
| **Bahasa** | Bahasa Indonesia (utama); Bahasa Inggris untuk ekspor laporan **[opsional]** |
| **Kepatuhan** | Mengikuti kebijakan IT & keamanan data PT Vale Indonesia (termasuk lokasi server/hosting) |

---

## 8. Model Data (Garis Besar)

| Entitas | Atribut utama |
|---|---|
| **Incident** (Kejadian) | id, nomor_err (ESS/Pom/…), tanggal, region, lokasi, koordinat_tkp, response_station_id, response_type, incident_type, sub_type, info_diterima_dari, detail_informasi, status, dibuat_oleh |
| **ResponseTime** | incident_id, dispatch_call, start_to_scene, arrival_on_scene, return_to_stn, on_scene_time, respond_time, target_time, time_gap, mileage_km |
| **ResponseUnit** | id, incident_id, unit_kendaraan_id (R6/E8/EMS6…), team_leader_id |
| **ResponsePersonnel** | incident_id, personel_id, kategori (on_duty / medical / off_duty_recall / support), instansi_pendukung, jam_dipanggil, jam_tiba |
| **PreliminaryVersion** | incident_id, versi (INITIAL/UPDATE n), status_kode, size_up, korban_sementara, tindakan, rencana, status_respons, waktu_kirim, dikirim_oleh |
| **Casualty** | incident_id, adult_m, adult_f, child_m, child_f, infant_m, infant_f, mass_casualty, total, detail (employee/contractor/dependent_vale/dependent_contractor/community), kondisi_triase (green/yellow/red/black) |
| **IncidentDetail** | incident_id, fire_type, alarm_type, zone_address, fes_tag_number, mva_type, rescue_type, other_explain, chemical_name, hazchem_code, container_type, container_volume |
| **ChronologyEntry** | id, incident_id, waktu, keterangan, sumber (tombol/catatan), dicatat_oleh |
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
| HP pribadi rusak, basah, atau terlalu panas di lokasi kebakaran | Data hilang | Sinkronisasi sesering mungkin ke server; **dua anggota tim bisa mencatat di respons yang sama** (cadangan); sediakan pelindung tahan air/pouch HP **[usulan]** |
| HP pribadi beragam merek, versi OS, dan kondisi | Aplikasi tidak berjalan sama di semua HP | Uji di berbagai HP anggota saat pilot, tetapkan spesifikasi minimum, fitur penghemat baterai |
| Kekhawatiran privasi anggota karena GPS di HP pribadi | Anggota enggan memasang aplikasi | GPS hanya aktif saat respons, kebijakan privasi yang jelas, data pribadi tidak diakses |
| Biaya kuota data & baterai ditanggung pribadi | Adopsi rendah | Pertimbangkan **tunjangan pulsa/kuota** dari perusahaan; foto besar diunggah via Wi-Fi station |
| Baterai habis saat respons panjang | Pencatatan terhenti | Optimasi GPS, charger di kendaraan, peringatan baterai rendah |
| Penolakan perubahan dari kebiasaan Excel | Adopsi rendah | Format laporan **mengikuti template lama**, pelatihan, masa pilot paralel |
| Kebijakan keamanan data perusahaan | Penundaan rilis | Libatkan tim IT PT Vale sejak Fase 0 |
| Akurasi GPS rendah di area tertutup | Jarak kurang akurat | Penyaringan titik GPS, tampilkan nilai akurasi, Team Leader bisa mengoreksi titik TKP |

---

## 13. Pertanyaan Terbuka

1. ~~Template ERR & Preliminary~~ **Terjawab:** ERR dari `ERR_31-10-2026_Watalara.xlsx` (5.5.1); Preliminary dibuatkan baru (5.4, perlu review, lihat #17).
2. Apa saja **tahapan respons resmi** menurut SOP FES PT Vale?
3. Siapa saja yang **wajib menerima** Preliminary Report, dan berapa batas waktunya?
4. ~~HP pribadi atau dinas~~ **Terjawab:** HP pribadi (lihat kebutuhan non-fungsional *HP pribadi (BYOD)* dan risiko di bagian 12).
5. Apakah ada kebijakan **server on-premise** atau cloud tertentu yang wajib dipakai?
6. Apakah tersedia **SSO/Active Directory** perusahaan untuk login?
7. Apakah ada **peta digital area perusahaan** yang bisa dipakai?
8. Berapa jumlah anggota FES, pos, dan unit kendaraan yang akan memakai aplikasi?
9. Selain kebakaran, jenis kejadian apa saja yang ditangani (rescue, medis, B3, dan lain-lain)?
10. Berapa lama data harus disimpan menurut kebijakan perusahaan?

**Dari hasil review form ERR:**

11. ~~Arti "Return to Stn"~~ **Terjawab:** waktu tim **tiba kembali di fire station**.
12. ~~Definisi Time Gap~~ **Terjawab:** tetap 0 seperti form sekarang. Rumus bisa diaktifkan nanti.
13. ~~Target Time~~ **Terjawab:** tidak ada nilai default karena berbeda per lokasi. **Diisi tim respons saat Dispatch Call**, sebelum berangkat.
14. ~~Total Response Time~~ **Terjawab:** digabung ke blok RESPON TIME dengan keterangan "Dispatch Call s/d Arrival on scene".
15. ~~Pola nomor ERR~~ **Terjawab:** format dibuat bisa diubah Admin (lihat 5.5.2).
16. ~~Jam datang Off Duty Recall & Support~~ **Terjawab:** ya, dicatat (jam dipanggil + jam tiba) di Preliminary, ERR, dan kronologi.
17. ~~Review format Preliminary Report~~ **Terjawab:** format, daftar distribusi, dan target kirim ≤ 15 menit **disetujui**.
18. ~~Kolom Jam Tiba di ERR~~ **Terjawab:** boleh ditambah. Lihat `templates/ERR_Form_Template.xlsx`.

---

## 14. Glosarium

| Istilah | Arti |
|---|---|
| **FES** | Fire Emergency & Service |
| **ERR** | Emergency Response Report: laporan lengkap respons darurat |
| **Preliminary Report** | Laporan awal kejadian yang dikirim segera |
| **TKP** | Tempat Kejadian Perkara (lokasi kejadian) |
| **Dispatch Call / Start to scene / Arrival on scene / Return to Stn** | Empat titik waktu resmi pada form ERR |
| **On Scene Time** | Istilah di form ERR untuk waktu tempuh: *Arrival on scene − Start to scene* |
| **Respond Time** | Istilah di form ERR: *Return to Stn − Arrival on scene* (di lokasi + perjalanan pulang) |
| **Total Response Time** | *Arrival on scene − Dispatch Call*: KPI utama waktu respons |
| **Mileage (KM)** | Jarak tempuh dari Response Station ke TKP |
| **Triase (Green/Yellow/Red/Black)** | Kategori kondisi korban dari ringan sampai meninggal |
| **BPBD** | Badan Penanggulangan Bencana Daerah |
| **Overhaul** | Tahap pendinginan dan pemeriksaan sisa api setelah padam |
| **Offline-first** | Aplikasi tetap berfungsi tanpa internet dan menyinkronkan data saat online |
| **MVP** | Minimum Viable Product: versi pertama dengan fitur inti |
