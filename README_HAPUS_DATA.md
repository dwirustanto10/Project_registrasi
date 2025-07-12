# Dokumentasi Fitur Hapus Data

## Deskripsi

Fitur hapus data telah disederhanakan menjadi hanya "Hapus Semua Data" untuk memudahkan penggunaan dan mengurangi kompleksitas sistem.

## File yang Terlibat

### 1. `hapus_data.php`

- **Fungsi**: Halaman utama untuk menghapus semua data mahasiswa
- **Fitur**:
  - Menampilkan jumlah data saat ini
  - Peringatan tentang tindakan yang tidak dapat dibatalkan
  - Konfirmasi sebelum menghapus semua data
  - Pesan hasil operasi (sukses/error)

### 2. `ajax_hapus_data.php`

- **Fungsi**: Handler AJAX untuk menghapus semua data
- **Endpoint**: Menerima request POST dengan action `hapus_semua`
- **Response**: JSON dengan status dan pesan

### 3. `tampil_data.php`

- **Perubahan**: Menghapus tombol hapus per baris
- **Fitur**: Hanya menampilkan data dengan tombol "Hapus Semua Data"

## Cara Penggunaan

### 1. Melalui Menu Utama

1. Klik menu "Hapus Data" di navigasi utama
2. Sistem akan menampilkan halaman hapus data dengan informasi jumlah data
3. Klik tombol "HAPUS SEMUA DATA" jika ingin melanjutkan
4. Konfirmasi dialog akan muncul untuk memastikan tindakan
5. Data akan dihapus dan pesan sukses ditampilkan

### 2. Melalui Halaman Tampil Data

1. Buka halaman "Tampil Data"
2. Klik tombol "🗑️ Hapus Semua Data" di bagian atas
3. Ikuti langkah yang sama seperti di atas

## Keamanan

### Konfirmasi Berganda

- Dialog konfirmasi JavaScript sebelum submit form
- Pesan peringatan yang jelas tentang konsekuensi tindakan
- Informasi jumlah data yang akan dihapus

### Validasi

- Hanya menerima request POST
- Validasi session untuk keamanan
- Pesan error yang informatif jika terjadi masalah

## Pesan yang Ditampilkan

### Sukses

```
"Semua data berhasil dihapus dari database."
```

### Error

```
"Gagal menghapus data: [detail error]"
```

## Catatan Penting

⚠️ **PERINGATAN**:

- Tindakan ini bersifat **permanen** dan **tidak dapat dibatalkan**
- Semua data mahasiswa akan dihapus dari database
- Pastikan untuk melakukan **backup data** sebelum menggunakan fitur ini
- Fitur ini hanya untuk keperluan maintenance atau reset database

## Struktur Database

Fitur ini menghapus semua data dari tabel `reg`:

```sql
DELETE FROM reg;
```

## Troubleshooting

### Jika data tidak terhapus:

1. Periksa koneksi database
2. Pastikan user database memiliki hak DELETE
3. Periksa log error PHP/MySQL
4. Pastikan tidak ada foreign key constraint yang mencegah penghapusan

### Jika halaman tidak merespons:

1. Periksa apakah file `hapus_data.php` ada dan dapat diakses
2. Pastikan session PHP berjalan dengan baik
3. Periksa error log web server
