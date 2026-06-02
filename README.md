# Safety Campaign Website - Kolaka

Website informasi kampanye keselamatan untuk Kota Kolaka dengan fitur lengkap termasuk admin panel.

## Fitur Utama

✅ **Halaman Utama** - Menampilkan daftar campaign terbaru  
✅ **Detail Campaign** - Flyer dan video campaign  
✅ **Halaman Lokasi** - Peta Google Maps Kota Kolaka  
✅ **Admin Panel** - Login dan dashboard untuk mengelola konten  
✅ **Upload Konten** - Admin dapat menambah, edit, dan hapus campaign  
✅ **Bagikan Campaign** - Share ke WhatsApp, Facebook, Twitter  
✅ **Responsive Design** - Berfungsi di desktop, tablet, dan mobile  

## Teknologi

- HTML5
- CSS3 (Responsive)
- JavaScript (Vanilla)
- LocalStorage (untuk data dan autentikasi)
- Google Maps API (untuk lokasi)

## Cara Menggunakan

### 1. Akses Website
- Buka `index.html` di browser

### 2. Login Admin
- Klik "Admin" di navbar
- Username: `admin`
- Password: `admin123`

### 3. Kelola Campaign
- Di dashboard admin, Anda bisa:
  - Melihat daftar campaign
  - Menambah campaign baru
  - Mengedit campaign
  - Menghapus campaign

## Struktur File

```
leoghynizm/
├── index.html              # Halaman utama
├── detail.html             # Detail campaign
├── maps.html               # Halaman lokasi
├── admin-login.html        # Login admin
├── admin-dashboard.html    # Dashboard admin
├── css/
│   ├── style.css           # Styling utama
│   └── responsive.css      # Media queries
├── js/
│   ├── data.js             # Fungsi data & localStorage
│   ├── main.js             # Script halaman utama
│   ├── detail.js           # Script detail campaign
│   └── admin.js            # Script admin panel
└── README.md               # File ini
```

## Fitur Share Campaign

Setiap campaign dapat dibagikan ke:
- 📱 **WhatsApp** - Bagikan langsung ke kontak WhatsApp
- 📘 **Facebook** - Share ke timeline Facebook
- 𝕏 **Twitter** - Tweet campaign
- 📋 **Copy Link** - Salin link campaign ke clipboard

## Data Campaign

Data campaign disimpan di `localStorage` browser. Setiap campaign memiliki:
- ID (otomatis)
- Judul
- Deskripsi
- URL Flyer
- URL Video (YouTube)
- Tanggal

## Autentikasi Admin

Sistem login sederhana menggunakan localStorage:
- Username: `admin`
- Password: `admin123`

## Deployment

Website ini dapat di-deploy ke:
- GitHub Pages
- Netlify
- Vercel
- Server hosting apapun

Cukup upload semua file ke hosting Anda.

## Catatan

- Google Maps memerlukan API key yang valid
- Data campaign disimpan di browser (localStorage)
- Untuk production, gunakan database backend
- Pastikan semua URL image dan video valid

## Support

Untuk pertanyaan atau masalah, hubungi:
- Email: safety@kolaka.gov.id
- Telepon: 0812-3456-7890

---

**Dibuat dengan ❤️ untuk keselamatan masyarakat Kolaka**