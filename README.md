<div align="center">

# ☕ Flint Cafe — Digital Menu System

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![Apache](https://img.shields.io/badge/Apache-2.4-D22128?style=for-the-badge&logo=apache&logoColor=white)

**Sistem menu digital berbasis web untuk Flint Cafe — lengkap dengan pemesanan via QR Code per meja, dashboard admin real-time, dan manajemen status pesanan.**

</div>

---

## ✨ Fitur Utama

- 📱 **QR Code per meja** — pelanggan langsung scan & pesan tanpa antri
- 🛒 **Keranjang belanja** dinamis berbasis JavaScript
- 📊 **Dashboard admin** dengan statistik harian (order & revenue)
- 🔄 **Tracking status pesanan** — `pending → confirmed → preparing → ready → completed`
- 🖨️ **QR Generator** bawaan untuk cetak QR tiap meja
- 🐳 **Fully Dockerized** — siap jalan dalam satu perintah

---

## 🚀 Cara Menjalankan

> **Prasyarat:** Pastikan [Docker](https://www.docker.com/) dan Docker Compose sudah terinstall di sistemmu.

```bash
# 1. Masuk ke direktori proyek
cd flint-cafe

# 2. Build & jalankan semua service
docker compose up -d --build

# 3. Cek status service (tunggu ~30-60 detik)
docker compose ps
```

Pastikan semua service berstatus `healthy` atau `running` sebelum mengakses aplikasi.

### 🌐 Akses Aplikasi

| URL | Keterangan |
|-----|------------|
| `http://localhost:8091` | Halaman menu utama (customer) |
| `http://localhost:8091/?meja=1` | Menu untuk Meja 1 |
| `http://localhost:8091/admin.php` | Dashboard admin |
| `http://localhost:8091/qr-generator.php` | Generator QR Code per meja |

---

## 📱 Alur Pemesanan Customer
Scan QR Code di meja
↓
Browse menu & pilih item
↓
Tambah ke keranjang
↓
Isi nama & metode bayar
↓
Klik "Pesan Sekarang"
↓
Terima kode pesanan untuk tracking


---

## 👨‍💼 Panel Admin

Login menggunakan password default: `flintadmin2024`

| Fitur | Deskripsi |
|-------|-----------|
| 📈 Dashboard | Statistik order & revenue harian |
| 📋 Kelola Pesanan | Update status pesanan secara real-time |
| 🖨️ QR Generator | Generate & print QR Code per meja |

> ⚠️ **Catatan Keamanan:** Ganti password default sebelum deploy ke produksi. Edit `$adminPass` di `admin.php`.

---

## 🐳 Docker Services

| Service | Container | Port Mapping |
|---------|-----------|--------------|
| PHP 8.2 + Apache | `FlintCafe-APP` | `8091:80` |
| MySQL 8.0 | `FlintCafe-DB` | `3307:3306` |
| Alpine Linux | `FlintCafe-Terminal` | — |

```bash
# Masuk ke terminal Alpine
docker exec -it FlintCafe-Terminal bash

# Akses MySQL langsung
docker exec -it FlintCafe-DB mysql -u cafe_user -pcafepassword flintcafe

# Stop semua container
docker compose down

# Stop + hapus data database
docker compose down -v
```

---

## 📂 Struktur Proyek

flint-cafe/
├── 🐳 docker-compose.yml # Konfigurasi Docker services
├── 🐳 Dockerfile # PHP + Apache image
├── ⚙️ config.php # Konfigurasi database & konstanta
├── 🏠 index.php # Halaman menu (customer)
├── 🔌 api.php # REST API (order, status update)
├── 🔐 admin.php # Dashboard admin
├── 📱 qr-generator.php # Generator QR Code
├── .htaccess # Apache URL rules
├── assets/
│ ├── css/
│ │ ├── style.css # Stylesheet halaman menu
│ │ └── admin.css # Stylesheet dashboard admin
│ └── js/
│ └── app.js # Logika cart & pemesanan
└── sql/
└── database.sql # Schema database + seed data


---

## 🎨 Desain & Palet Warna

Tema visual Flint Cafe terinspirasi dari nuansa kafe artisanal yang hangat.

| Nama | HEX | Penggunaan |
|------|-----|------------|
| 🟫 Espresso Dark | `#3C2A21` | Header, teks utama, tombol |
| 🟡 Warm Latte | `#D4A373` | Aksen, badge, efek hover |
| 🌿 Sage Green | `#828E82` | Teks sekunder, label kategori |
| 🟨 Cream | `#FEFAE0` | Background utama |

---

## ✏️ Kustomisasi

| Yang Ingin Diubah | Caranya |
|---|---|
| Nama kafe | Edit konstanta `CAFE_NAME` di `config.php` |
| Password admin | Edit `$adminPass` di `admin.php` |
| Tarif pajak | Edit konstanta `TAX_RATE` di `config.php` |
| Tambah menu | Insert langsung ke DB atau edit `sql/database.sql` |
| Tambah meja | Insert ke tabel `tables_cafe` |

---

<div align="center">

Made with ☕ by **MfBally354**

</div>
