# Sistem Registrasi Mahasiswa

Aplikasi web PHP untuk registrasi mahasiswa dengan fitur upload file Excel, preview data, dan penyimpanan ke database.

## Fitur

- 📤 **Upload File Excel**: Upload file Excel (.xlsx, .xls, .csv) dengan validasi
- 👀 **Preview Data**: Preview data Excel sebelum upload ke database dengan pagination
- 💾 **Database Integration**: Penyimpanan data ke database MySQL dengan prepared statements
- 📊 **Data Display**: Tampilan data mahasiswa dengan pagination dan fitur pencarian
- 📥 **Export Excel**: Download data dalam format Excel
- 📱 **Responsive Design**: Interface yang responsif untuk desktop dan mobile
- 🔗 **SPA Navigation**: Single Page Application dengan AJAX navigation

## Teknologi yang Digunakan

- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Excel Processing**: PhpSpreadsheet
- **Styling**: Custom CSS dengan responsive design

## Instalasi

### Prerequisites

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web server (Apache/Nginx)
- Composer

### Langkah Instalasi

1. **Clone repository**

   ```bash
   git clone https://github.com/username/project_registrasi.git
   cd project_registrasi
   ```

2. **Install dependencies**

   ```bash
   composer install
   ```

3. **Setup database**

   - Buat database MySQL
   - Import struktur tabel dari file SQL yang disediakan
   - Konfigurasi koneksi database di `koneksi.php`

4. **Konfigurasi web server**

   - Pastikan folder project dapat diakses melalui web server
   - Set permission yang sesuai untuk folder uploads (jika ada)

5. **Akses aplikasi**
   - Buka browser dan akses `http://localhost/project_registrasi`

## Struktur Database

### Tabel Utama

- `reg` - Data registrasi mahasiswa
- `agama` - Referensi data agama
- `jalur_daftar` - Referensi jalur pendaftaran

### Struktur Tabel `reg`

Tabel ini berisi 89 kolom dengan data lengkap mahasiswa termasuk:

- Data pribadi (NIM, Nama, NIK, dll)
- Data akademik (Program Studi, Angkatan, dll)
- Data keluarga (Ayah, Ibu, Wali)
- Data alamat dan kontak

## Penggunaan

### Upload Data

1. Klik menu "Upload Data"
2. Pilih file Excel yang berisi data mahasiswa
3. Preview data untuk memastikan kebenaran
4. Klik "Mulai Upload ke Database"

### Tampil Data

1. Klik menu "Tampil Data"
2. Data akan ditampilkan dengan pagination
3. Gunakan tombol pagination untuk navigasi
4. Klik "Download Data Excel" untuk export

## Konfigurasi

### Upload Settings

Pastikan konfigurasi PHP sesuai untuk upload file besar:

```ini
upload_max_filesize = 10M
post_max_size = 10M
memory_limit = 256M
max_execution_time = 300
```

### Database Configuration

Edit file `koneksi.php`:

```php
$host = 'localhost';
$username = 'your_username';
$password = 'your_password';
$database = 'your_database';
```

## Troubleshooting

### Masalah Upload File

- Periksa permission folder uploads
- Pastikan ukuran file tidak melebihi batas
- Cek error log PHP

### Masalah Database

- Pastikan koneksi database benar
- Periksa struktur tabel sesuai dengan yang diharapkan
- Cek error log MySQL

## Kontribusi

1. Fork repository
2. Buat branch fitur baru (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## License

Distributed under the MIT License. See `LICENSE` for more information.

## Contact

Your Name - [@your_twitter](https://twitter.com/your_twitter) - email@example.com

Project Link: [https://github.com/username/project_registrasi](https://github.com/username/project_registrasi)
