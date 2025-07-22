# Validasi Email - Fitur Baru

## Deskripsi

Fitur ini menambahkan validasi otomatis untuk kolom email di halaman tampil data dan download Excel.

## Perubahan yang Dilakukan

### 1. File `tampil_data.php`

- Menambahkan kolom "Catatan Email" yang akan menampilkan "silakan cek email" jika:
  - Email kosong (NULL atau string kosong)
  - Email tidak mengandung lambang @

### 2. File `download_excel.php`

- Menambahkan kolom "Catatan Email" dengan logika yang sama
- Menambahkan format text untuk kolom email agar tetap sebagai string
- Memastikan kolom email tidak berubah format saat di-export ke Excel

## Logika Validasi

```sql
TRIM(rm_email) AS 'Email',
CASE
    WHEN TRIM(rm_email) IS NULL OR TRIM(rm_email) = '' OR TRIM(rm_email) NOT LIKE '%@%' THEN 'silakan cek email'
    ELSE ''
END AS 'Catatan Email'
```

## Hasil

- Di halaman tampil data: akan muncul kolom baru "Catatan Email" yang berisi pesan "silakan cek email" jika email tidak valid
- Di file Excel yang di-download: kolom "Catatan Email" akan muncul dengan pesan yang sama
- Email yang valid (mengandung @) akan menampilkan string kosong di kolom catatan

## Contoh

- Email: "john.doe@gmail.com" → Catatan Email: "" (kosong)
- Email: " john.doe@gmail.com " → Email: "john.doe@gmail.com", Catatan Email: "" (kosong)
- Email: "invalid-email" → Catatan Email: "silakan cek email"
- Email: " " (hanya spasi) → Email: "", Catatan Email: "silakan cek email"
- Email: "" (kosong) → Catatan Email: "silakan cek email"
- Email: NULL → Catatan Email: "silakan cek email"

## Tanggal Implementasi

Diterapkan pada: [Tanggal hari ini]
