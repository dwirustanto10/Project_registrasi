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
    echo json_encode(['status' => 'error', 'message' => 'Gagal mempersiapkan query database']);
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
    
    $processed_row = array_map(function($value) {
        return ($value === null || $value === '') ? NULL : $value;
    }, $row);

    $processed_row[9] = ($processed_row[9] == 'Ya') ? 1 : 0;
    $processed_row[10] = ($processed_row[10] == 'Ya') ? 1 : 0;

    $jumlah_kolom_db = 50;
    while (count($processed_row) < $jumlah_kolom_db) {
        $processed_row[] = NULL;
    }

    $types = str_repeat('s', 89);
    if (!$stmt->bind_param($types, ...$processed_row)) {
        $error_rows[] = [
            'row' => $index + 1,
            'reason' => 'Gagal memproses data'
        ];
        continue;
    }

    if ($stmt->execute()) {
        $success_count++;
    } else {
        $error_rows[] = [
            'row' => $index + 1,
            'reason' => 'Gagal menyimpan ke database'
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
    'errors' => $error_rows
]); 