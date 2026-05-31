# 💼 BukuTabungan

Aplikasi manajemen keuangan pribadi berbasis PHP + SQLite. Gratis, ringan, tidak perlu MySQL.

## Fitur
- 👛 Banyak dompet (reguler & tabungan) dengan warna custom
- 🏷️ Kategori pengeluaran & pemasukan (makanan, listrik, gas, dll)
- 💸 Transaksi: saldo dompet otomatis berkurang/bertambah
- 📊 Ringkasan: harian, bulanan, tahunan

## Instalasi

### Opsi 1: PHP Built-in Server (lokal)
```bash
git clone https://github.com/kusmedihana-bot/BukuTabungan.git
cd BukuTabungan
php -S localhost:8000
```
Buka http://localhost:8000

### Opsi 2: Hosting Gratis
Upload semua file ke hosting PHP gratis seperti:
- [InfinityFree](https://infinityfree.net)
- [000webhost](https://www.000webhost.com)
- [Hostinger Free](https://www.hostinger.com)

> Pastikan PHP ≥ 7.4 dan ekstensi `pdo_sqlite` aktif.

## Struktur
```
BukuTabungan/
├── config/database.php   # SQLite auto-setup
├── includes/             # Header & footer
├── assets/css/style.css  # Styling
├── assets/js/app.js      # JS
├── index.php             # Dashboard
├── transactions.php      # Transaksi
├── wallets.php           # Dompet
├── categories.php        # Kategori
└── summary.php           # Ringkasan
```

Database SQLite otomatis dibuat di `data/bukutabungan.db` saat pertama kali diakses.
