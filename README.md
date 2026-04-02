# ⚡ Flint Cafe — Digital Menu System

Website menu digital untuk Flint Cafe dengan sistem pemesanan berbasis QR Code per meja.

---

## 🚀 Cara Menjalankan

### 1. Clone / Copy proyek ini
```bash
# Pastikan Docker & Docker Compose sudah terinstall
docker --version
docker compose version
```

### 2. Jalankan Docker Compose
```bash
cd flint-cafe
docker compose up -d --build
```

### 3. Tunggu sampai healthy (~30-60 detik)
```bash
docker compose ps
# Pastikan semua service status "healthy" atau "running"
```

### 4. Akses aplikasi
| URL | Keterangan |
|-----|-----------|
| `http://localhost:8091` | Halaman menu utama |
| `http://localhost:8091/?meja=1` | Menu untuk Meja 1 |
| `http://localhost:8091/admin.php` | Dashboard admin |
| `http://localhost:8091/qr-generator.php` | Generator QR Code |

---

## 📱 Alur Pemesanan Customer

1. **Scan QR Code** di meja → diarahkan ke `/?meja={nomor}`
2. **Browse menu** berdasarkan kategori
3. **Tambah ke keranjang** dengan tombol "+ Tambah"
4. **Isi nama & metode bayar**, lalu klik **Pesan Sekarang**
5. Dapat **kode pesanan** untuk tracking

---

## 👨‍💼 Fitur Admin

- **Login** → password: `flintadmin2024`
- **Dashboard** statistik harian (order, revenue)
- **Kelola pesanan** → update status (pending → confirmed → preparing → ready → completed)
- **QR Generator** → generate & print QR per meja

---

## 🐳 Services Docker

| Service | Container | Port |
|---------|-----------|------|
| PHP 8.2 + Apache | FlintCafe-APP | 8091:80 |
| MySQL 8.0 | FlintCafe-DB | 3307:3306 |
| Alpine Linux (terminal) | FlintCafe-Terminal | - |

### Masuk ke terminal Alpine:
```bash
docker exec -it FlintCafe-Terminal bash
```

### Cek database:
```bash
docker exec -it FlintCafe-DB mysql -u cafe_user -pcafepassword flintcafe
```

---

## 🎨 Palet Warna

| Nama | HEX | Penggunaan |
|------|-----|-----------|
| Espresso Dark | `#3C2A21` | Header, teks utama, tombol |
| Warm Latte | `#D4A373` | Aksen, badge, hover |
| Sage Green | `#828E82` | Teks sekunder, kategori |
| Cream | `#FEFAE0` | Background utama |

---

## 📂 Struktur File

```
flint-cafe/
├── docker-compose.yml      # Docker services
├── Dockerfile              # PHP + Apache image
├── .htaccess               # Apache rules
├── config.php              # Database config
├── index.php               # Halaman menu (customer)
├── api.php                 # REST API (order, status)
├── admin.php               # Dashboard admin
├── qr-generator.php        # QR Code generator
├── assets/
│   ├── css/
│   │   ├── style.css       # Stylesheet menu
│   │   └── admin.css       # Stylesheet admin
│   └── js/
│       └── app.js          # JavaScript (cart, order)
└── sql/
    └── database.sql        # Schema + seed data
```

---

## 🛑 Stop Aplikasi

```bash
docker compose down          # Stop containers
docker compose down -v       # Stop + hapus data DB
```

---

## ✏️ Kustomisasi

- **Tambah menu** → edit `sql/database.sql` atau langsung di DB
- **Ganti nama kafe** → edit `CAFE_NAME` di `config.php`
- **Ubah password admin** → edit `$adminPass` di `admin.php`
- **Tambah meja** → insert ke tabel `tables_cafe`
- **Pajak** → edit `TAX_RATE` di `config.php`
