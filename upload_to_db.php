<?php
session_start();
require_once 'koneksi.php';
header('Content-Type: application/json');

if (!isset($_SESSION['excel_data'])) {
    echo json_encode(['status' => 'error', 'message' => 'Data Excel tidak ditemukan dalam session. Silakan upload file kembali.']);
    exit;
}

$headers = $_SESSION['excel_data']['headers'];
$rows = $_SESSION['excel_data']['rows'];

// Dapatkan jumlah kolom yang sebenarnya di database
$count_sql = "SELECT COUNT(*) as total FROM information_schema.columns WHERE table_name = 'reg' AND table_schema = DATABASE()";
$count_result = $conn->query($count_sql);
$db_column_count = $count_result->fetch_assoc()['total'];

$sql = "INSERT INTO reg (
    prodi_jenjang, prodi_nama, rm_nama, rm_nama_perbaikan, rm_angkatan, 
    rm_mulai_smt, rm_nim, rm_nisn, rm_jalur, rm_id_jenis_daftar,
    rm_bidik_misi, rm_punya_kip, rm_kip, rm_nik, rm_nik_perbaikan,
    jenis_kelamin, rm_tmp_lahir, rm_tmp_lahir_perbaikan, rm_tgl_lahir, rm_tgl_lahir_perbaikan,
    rm_tinggi_badan, rm_gol_darah, rm_penyakit, rm_agama, kewarganegaraan,
    rm_alamat_telp, rm_email, rm_pddk_tk_nama, rm_pddk_tk_lokasi, rm_pddk_tk_thn_lulus,
    rm_pddk_sd_nama, rm_pddk_sd_jurusan, rm_pddk_sd_lokasi, rm_pddk_sd_thn_lulus,
    rm_pddk_sltp_nama, rm_pddk_sltp_jurusan, rm_pddk_sltp_lokasi, rm_pddk_sltp_thn_lulus,
    rm_pddk_slta_thn_lulus, rm_pddk_ijazah_tahun, rm_pddk_ijazah_nomor, rm_pddk_ijazah_nilai,
    rm_status_kawin, rm_jumlah_anak, rm_status_pekerjaan, rm_pekerjaan, rm_pendapatan,
    rm_jalan, rm_rt, rm_rw, rm_nama_dusun, rm_desa_kelurahan,
    id_wilayah, kecamatan_ortu, rm_jns_tinggal, rm_jns_tranportasi,
    rm_keluarga_ayah_nama, rm_nik_ayah, rm_keluarga_ayah_pekerjaan, rm_keluarga_ayah_tmp_lahir,
    rm_keluarga_ayah_tgl_lahir, rm_keluarga_ayah_pddk, rm_keluarga_ayah_penghasilan,
    rm_penghasilan_ayah_pddikti, rm_keluarga_ayah_alamat, rm_keluarga_ayah_telp, rm_keluarga_ayah_hp,
    rm_keluarga_ibu_nama, rm_nik_ibu, rm_keluarga_ibu_pekerjaan, rm_keluarga_ibu_tmp_lahir,
    rm_keluarga_ibu_tgl_lahir, rm_keluarga_ibu_pddk, rm_keluarga_ibu_penghasilan,
    rm_penghasilan_ibu_pddikti, rm_keluarga_ibu_alamat, rm_keluarga_ibu_telp, rm_keluarga_ibu_hp,
    rm_nama_wali, rm_nik_wali, rm_alamat_wali, rm_wali_tlp, rm_wali_hp,
    rm_wali_tmp_lahir, rm_tgl_lahir_wali, rm_pddk_wali, rm_pekerjaan_wali,
    rm_penghasilan_wali, rm_penghasilan_wali_pddikti
) VALUES (" . str_repeat('?,', 88) . "?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal mempersiapkan query database: ' . $conn->error]);
    exit;
}

$total_rows = count($rows);
$success_count = 0;
$error_rows = [];

foreach ($rows as $index => $row) {
    if (count($row) != 89) {
        $error_rows[] = [
            'row' => $index + 1,
            'reason' => 'Jumlah kolom tidak sesuai',
            'count' => count($row)
        ];
        continue;
    }
    
    // Proses data dengan lebih hati-hati untuk mempertahankan tipe data
    $processed_row = [];
    foreach ($row as $i => $value) {
        if ($value === null || $value === '') {
            $processed_row[] = NULL;
        } else {
            // Konversi string kosong menjadi NULL
            if (is_string($value) && trim($value) === '') {
                $processed_row[] = NULL;
            } else {
                // Pertahankan nilai asli untuk angka
                $processed_row[] = $value;
            }
        }
    }

    // Konversi khusus untuk field boolean (rm_bidik_misi dan rm_punya_kip)
    // rm_id_jenis_daftar adalah indeks 9, tapi ini bukan field boolean
    // rm_bidik_misi adalah indeks 10
    // rm_punya_kip adalah indeks 11
    $processed_row[10] = ($processed_row[10] == 'Ya') ? 1 : 0;  // rm_bidik_misi
    $processed_row[11] = ($processed_row[11] == 'Ya') ? 1 : 0;  // rm_punya_kip

    // Pastikan jumlah kolom sesuai dengan yang dibutuhkan query (89 kolom)
    while (count($processed_row) < 89) {
        $processed_row[] = NULL;
    }

    // Gunakan tipe data yang tepat untuk setiap kolom
    $types = '';
    $bind_values = [];
    
    // Daftar kolom yang harus tetap sebagai string (termasuk nomor telepon)
    $string_columns = [
        25, // rm_alamat_telp
        47, // rm_keluarga_ayah_telp
        58, // rm_keluarga_ibu_telp
        65, // rm_wali_tlp
        66, // rm_wali_hp
        0,  // prodi_jenjang
        1,  // prodi_nama
        2,  // rm_nama
        3,  // rm_nama_perbaikan
        4,  // rm_angkatan
        5,  // rm_mulai_smt
        6,  // rm_nim
        7,  // rm_nisn
        8,  // rm_jalur
        13, // rm_kip
        14, // rm_nik
        15, // rm_nik_perbaikan
        16, // jenis_kelamin
        17, // rm_tmp_lahir
        18, // rm_tmp_lahir_perbaikan
        19, // rm_tgl_lahir
        20, // rm_tgl_lahir_perbaikan
        21, // rm_tinggi_badan
        22, // rm_gol_darah
        23, // rm_penyakit
        24, // rm_agama
        25, // kewarganegaraan
        26, // rm_email
        27, // rm_pddk_tk_nama
        28, // rm_pddk_tk_lokasi
        29, // rm_pddk_tk_thn_lulus
        30, // rm_pddk_sd_nama
        31, // rm_pddk_sd_jurusan
        32, // rm_pddk_sd_lokasi
        33, // rm_pddk_sd_thn_lulus
        34, // rm_pddk_sltp_nama
        35, // rm_pddk_sltp_jurusan
        36, // rm_pddk_sltp_lokasi
        37, // rm_pddk_sltp_thn_lulus
        38, // rm_pddk_slta_thn_lulus
        39, // rm_pddk_ijazah_tahun
        40, // rm_pddk_ijazah_nomor
        41, // rm_pddk_ijazah_nilai
        42, // rm_status_kawin
        43, // rm_jumlah_anak
        44, // rm_status_pekerjaan
        45, // rm_pekerjaan
        46, // rm_pendapatan
        47, // rm_jalan
        48, // rm_rt
        49, // rm_rw
        50, // rm_nama_dusun
        51, // rm_desa_kelurahan
        52, // id_wilayah
        53, // kecamatan_ortu
        54, // rm_jns_tinggal
        55, // rm_jns_tranportasi
        56, // rm_keluarga_ayah_nama
        57, // rm_nik_ayah
        58, // rm_keluarga_ayah_pekerjaan
        59, // rm_keluarga_ayah_tmp_lahir
        60, // rm_keluarga_ayah_tgl_lahir
        61, // rm_keluarga_ayah_pddk
        62, // rm_keluarga_ayah_penghasilan
        63, // rm_penghasilan_ayah_pddikti
        64, // rm_keluarga_ayah_alamat
        65, // rm_keluarga_ayah_telp
        66, // rm_keluarga_ayah_hp
        67, // rm_keluarga_ibu_nama
        68, // rm_nik_ibu
        69, // rm_keluarga_ibu_pekerjaan
        70, // rm_keluarga_ibu_tmp_lahir
        71, // rm_keluarga_ibu_tgl_lahir
        72, // rm_keluarga_ibu_pddk
        73, // rm_keluarga_ibu_penghasilan
        74, // rm_penghasilan_ibu_pddikti
        75, // rm_keluarga_ibu_alamat
        76, // rm_keluarga_ibu_telp
        77, // rm_keluarga_ibu_hp
        78, // rm_nama_wali
        79, // rm_nik_wali
        80, // rm_alamat_wali
        81, // rm_wali_tlp
        82, // rm_wali_hp
        83, // rm_wali_tmp_lahir
        84, // rm_tgl_lahir_wali
        85, // rm_pddk_wali
        86, // rm_pekerjaan_wali
        87, // rm_penghasilan_wali
        88  // rm_penghasilan_wali_pddikti
    ];
    
    for ($i = 0; $i < 89; $i++) {
        if ($processed_row[$i] === NULL) {
            $types .= 's'; // NULL tetap di-bind sebagai string
            $bind_values[] = NULL;
        } elseif (in_array($i, $string_columns)) {
            // Kolom yang harus tetap sebagai string (termasuk nomor telepon)
            $types .= 's';
            $bind_values[] = (string)$processed_row[$i];
        } elseif (is_numeric($processed_row[$i])) {
            // Jika nilai numerik, gunakan tipe 'd' untuk integer atau 's' untuk string
            if (is_int($processed_row[$i]) || ctype_digit($processed_row[$i])) {
                $types .= 'i'; // integer
            } else {
                $types .= 'd'; // double/float
            }
            $bind_values[] = $processed_row[$i];
        } else {
            $types .= 's'; // string
            $bind_values[] = $processed_row[$i];
        }
    }

    if (!$stmt->bind_param($types, ...$bind_values)) {
        $error_rows[] = [
            'row' => $index + 1,
            'reason' => 'Gagal memproses data: ' . $stmt->error
        ];
        continue;
    }

    if ($stmt->execute()) {
        $success_count++;
    } else {
        $error_rows[] = [
            'row' => $index + 1,
            'reason' => 'Gagal menyimpan ke database: ' . $stmt->error
        ];
    }
}

$stmt->close();
$conn->close();
unset($_SESSION['excel_data']);

echo json_encode([
    'status' => 'success',
    'total' => $total_rows,
    'success' => $success_count,
    'failed' => count($error_rows),
    'errors' => $error_rows,
    'db_columns' => $db_column_count
]); 