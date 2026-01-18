# 🤖 Bot Migrasi - E-commerce Product Management Automation

Sistem otomasi untuk scraping, upload, dan manajemen produk e-commerce dengan Laravel dashboard.

## 📋 Deskripsi

Bot ini mengotomasi workflow lengkap untuk migrasi produk:
1. **Scraping** - Ambil data produk dari website sumber (Balimall.id)
2. **Download Images** - Download dan upload gambar produk ke Cloudinary
3. **Upload Produk** - Upload produk otomatis ke admin panel
4. **Update Stok** - Update stok produk secara batch

## 🚀 Fitur

- ✅ Web Dashboard dengan Laravel untuk monitoring
- ✅ Scraping produk dengan Selenium
- ✅ Auto-upload gambar ke Cloudinary
- ✅ Batch processing untuk upload produk
- ✅ Update stok otomatis
- ✅ Deduplikasi produk
- ✅ Export/Import Excel untuk stok

## 📦 Instalasi

### Requirements

- PHP 8.1+
- Composer
- Node.js & NPM
- Python 3.8+
- MySQL/MariaDB
- Chrome/Chromium Browser

### Setup Laravel (Web Dashboard)

```bash
# Clone repository
git clone https://github.com/DelpiTiwu/Automation-Migration.git
cd Automation-Migration

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Setup database
# Edit .env untuk konfigurasi database
php artisan migrate
php artisan db:seed

# Run development server
php artisan serve
npm run dev
```

### Setup Python Bot

```bash
cd Bot_khusus_Kantor

# Create virtual environment
python -m venv .venv
.venv\Scripts\activate  # Windows
# source .venv/bin/activate  # Linux/Mac

# Install dependencies
pip install selenium cloudinary requests pillow

# Setup credentials
cp cloudinary_config.json.example cloudinary_config.json
cp config.py.example config.py

# Edit cloudinary_config.json dan config.py dengan credentials kamu
```

## ⚙️ Konfigurasi

### 1. Cloudinary Setup

Daftar di [Cloudinary](https://cloudinary.com) (gratis 25GB), lalu edit `cloudinary_config.json`:

```json
{
    "cloud_name": "your_cloud_name",
    "api_key": "your_api_key",
    "api_secret": "your_api_secret"
}
```

### 2. Admin Panel Login

Edit `config.py`:

```python
EMAIL_LOGIN = "your_admin_email@example.com"
PASS_LOGIN  = "your_password"
```

### 3. Target Kategori

Sesuaikan kategori/subkategori di `config.py`:

```python
TARGET_KATEGORI    = "Kategori yang diinginkan" 
TARGET_SUBKATEGORI = "Subkategori yang diinginkan"
```

## 🎯 Cara Penggunaan

### Workflow Lengkap

1. **Jalankan Dashboard**
   ```bash
   php artisan serve
   ```
   Akses: http://localhost:8000

2. **Scraping Produk**
   ```bash
   cd Bot_khusus_Kantor
   python scrape_web1.py
   ```

3. **Download Gambar**
   ```bash
   python download_images.py
   ```

4. **Upload Produk ke Admin Panel**
   ```bash
   python upload_bot.py
   ```

5. **Update Stok Produk**
   ```bash
   python upload_stok.py
   ```

### Monitoring

- Dashboard menampilkan progress real-time
- Check `debug_log_*.txt` untuk troubleshooting
- Status tersimpan di `runtime_config.json`

## 📁 Struktur File Penting

```
bot-migrasi/
├── Bot_khusus_Kantor/          # Python automation scripts
│   ├── scrape_web1.py          # Web scraper
│   ├── download_images.py      # Image downloader
│   ├── upload_bot.py           # Product uploader
│   ├── upload_stok.py          # Stock updater
│   ├── config.py               # Bot configuration (gitignored)
│   ├── cloudinary_config.json  # Cloudinary credentials (gitignored)
│   └── hasil_panen_ready.json  # Scraped products data
├── app/                        # Laravel app
├── resources/views/            # Dashboard views
└── database/                   # Migrations & seeders
```

## ⚠️ File yang TIDAK DI-COMMIT

File berikut ada di `.gitignore` dan **JANGAN** di-commit ke repo:

- `Bot_khusus_Kantor/config.py` - Berisi credentials login
- `Bot_khusus_Kantor/cloudinary_config.json` - API keys Cloudinary
- `Bot_khusus_Kantor/debug_log*.txt` - Log debugging
- `Bot_khusus_Kantor/downloaded_images/` - Gambar hasil download
- `.env` - Laravel environment config

Gunakan file `.example` sebagai template.

## 🐛 Troubleshooting

### "Invalid Session ID" Error
- Naikkan nilai `SLEEP_*` di `config.py`
- Check koneksi internet
- Pastikan Chrome/Chromium terinstall

### "DataTables Ajax Error"
- Clear browser cache di script
- Tunggu lebih lama (naikkan sleep time)
- Check admin panel accessibility

### Gambar Tidak Terdownload
- Check Cloudinary credentials
- Pastikan `cloudinary` package terinstall
- Lihat `debug_log_inventory.txt`

## 📝 License

MIT License - Silakan digunakan untuk keperluan pribadi atau komersial.

## 👤 Author

Developed by [Delpiero]

---

**⚠️ PERHATIAN**: Project ini untuk automasi internal. Pastikan mematuhi Terms of Service website yang di-scrape.
