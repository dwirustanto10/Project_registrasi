# Sistem Upload Data Excel

Sistem ini digunakan untuk mengupload data dari file Excel ke database MySQL. Fitur-fitur yang tersedia:

- Upload file Excel (.xls, .xlsx) dan CSV
- Preview data sebelum upload ke database
- Validasi data
- Penanganan error
- Integrasi dengan referensi jenis kelamin

## Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Composer
- PhpSpreadsheet library

## Instalasi

1. Clone repository ini

```bash
git clone [URL_REPOSITORY]
```

2. Install dependencies menggunakan Composer

```bash
composer install
```

3. Buat database MySQL dan import struktur tabel

```sql
CREATE DATABASE registrasi;
USE registrasi;

-- Import file SQL yang tersedia di folder database
```

4. Konfigurasi koneksi database di file `koneksi.php`

## Penggunaan

1. Buka aplikasi di browser
2. Pilih file Excel yang akan diupload
3. Klik tombol "Upload dan Preview"
4. Periksa preview data
5. Klik "Mulai Upload ke Database" untuk menyimpan data

## Struktur File

- `index.php` - Halaman utama dan form upload
- `upload_handler.php` - Handler untuk proses upload
- `koneksi.php` - Konfigurasi koneksi database
- `vendor/` - Dependencies (PhpSpreadsheet)

## Kontribusi

Silakan buat pull request untuk kontribusi. Untuk perubahan besar, harap buka issue terlebih dahulu untuk mendiskusikan perubahan yang diinginkan.

## Lisensi

[MIT](https://choosealicense.com/licenses/mit/)
