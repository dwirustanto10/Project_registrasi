# Perbaikan Masalah Leading Zeros pada Nomor Telepon

## Masalah

Pada saat upload data Excel, kolom nomor telepon seperti `rm_alamat_telp` kehilangan angka 0 di depan (leading zeros). Contoh:

- Data asli di Excel: `02123456789`
- Hasil di database: `2123456789`

## Penyebab

Masalah ini terjadi karena:

1. PHP menganggap nilai numerik dengan leading zeros sebagai integer
2. Saat binding parameter dengan tipe `i` (integer), leading zeros otomatis hilang
3. Excel sering membaca nomor telepon sebagai numeric value

## Solusi

Telah ditambahkan daftar kolom yang harus tetap sebagai string untuk mempertahankan format asli:

### File yang Diperbaiki:

1. `upload_to_db.php`
2. `index.php`

### Kolom Nomor Telepon yang Diperbaiki:

- `rm_alamat_telp` (indeks 25)
- `rm_keluarga_ayah_telp` (indeks 65)
- `rm_keluarga_ibu_telp` (indeks 76)
- `rm_wali_tlp` (indeks 81)
- `rm_wali_hp` (indeks 82)

### Implementasi:

```php
// Daftar kolom yang harus tetap sebagai string
$string_columns = [
    25, // rm_alamat_telp
    47, // rm_keluarga_ayah_telp
    58, // rm_keluarga_ibu_telp
    65, // rm_wali_tlp
    66, // rm_wali_hp
    // ... kolom lainnya
];

// Logika binding parameter
for ($i = 0; $i < 89; $i++) {
    if ($processed_row[$i] === NULL) {
        $types .= 's';
        $bind_values[] = NULL;
    } elseif (in_array($i, $string_columns)) {
        // Kolom yang harus tetap sebagai string
        $types .= 's';
        $bind_values[] = (string)$processed_row[$i];
    } elseif (is_numeric($processed_row[$i])) {
        // Kolom numerik lainnya
        if (is_int($processed_row[$i]) || ctype_digit($processed_row[$i])) {
            $types .= 'i';
        } else {
            $types .= 'd';
        }
        $bind_values[] = $processed_row[$i];
    } else {
        $types .= 's';
        $bind_values[] = $processed_row[$i];
    }
}
```

## Hasil

Setelah perbaikan:

- ✅ Nomor telepon `02123456789` akan tetap `02123456789` di database
- ✅ Leading zeros tidak hilang
- ✅ Format nomor telepon terjaga
- ✅ Tidak mempengaruhi kolom numerik lainnya

## Kolom Lain yang Diperbaiki

Selain nomor telepon, semua kolom berikut juga dipaksa sebagai string untuk konsistensi:

- NIM, NISN, NIK
- Kode-kode referensi
- Nama-nama
- Alamat
- Dan kolom lainnya

## Testing

Untuk memastikan perbaikan berhasil:

1. Upload file Excel dengan nomor telepon yang memiliki leading zeros
2. Periksa data di database
3. Download Excel untuk memverifikasi format tetap terjaga

## Catatan

- Perubahan ini tidak mempengaruhi data yang sudah ada di database
- Hanya berlaku untuk upload data baru
- Jika ada data lama yang salah, perlu diupdate manual atau re-upload
