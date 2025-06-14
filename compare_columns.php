<?php
require_once 'koneksi.php';

// Query untuk mendapatkan struktur tabel
$sql = "DESCRIBE reg";
$result = $conn->query($sql);

// Ambil semua kolom dari database
$db_columns = [];
while ($row = $result->fetch_assoc()) {
    $db_columns[] = $row['Field'];
}

// Kolom-kolom dari query di index.php
$query_columns = [
    'id', 'prodi_jenjang', 'prodi_nama', 'rm_nama', 'rm_nama_perbaikan', 'rm_angkatan', 
    'rm_mulai_smt', 'rm_nim', 'rm_nisn', 'rm_jalur', 'rm_id_jenis_daftar',
    'rm_bidik_misi', 'rm_punya_kip', 'rm_kip', 'rm_nik', 'rm_nik_perbaikan',
    'jenis_kelamin', 'rm_tmp_lahir', 'rm_tmp_lahir_perbaikan', 'rm_tgl_lahir', 'rm_tgl_lahir_perbaikan',
    'rm_tinggi_badan', 'rm_gol_darah', 'rm_penyakit', 'rm_agama', 'kewarganegaraan',
    'rm_alamat_telp', 'rm_email', 'rm_pddk_tk_nama', 'rm_pddk_tk_lokasi', 'rm_pddk_tk_thn_lulus',
    'rm_pddk_sd_nama', 'rm_pddk_sd_jurusan', 'rm_pddk_sd_lokasi', 'rm_pddk_sd_thn_lulus',
    'rm_pddk_sltp_nama', 'rm_pddk_sltp_jurusan', 'rm_pddk_sltp_lokasi', 'rm_pddk_sltp_thn_lulus',
    'rm_pddk_slta_thn_lulus', 'rm_pddk_ijazah_tahun', 'rm_pddk_ijazah_nomor', 'rm_pddk_ijazah_nilai',
    'rm_status_kawin', 'rm_jumlah_anak', 'rm_status_pekerjaan', 'rm_pekerjaan', 'rm_pendapatan',
    'rm_jalan', 'rm_rt', 'rm_rw', 'rm_nama_dusun', 'rm_desa_kelurahan',
    'id_wilayah', 'kecamatan_ortu', 'rm_jns_tinggal', 'rm_jns_tranportasi',
    'rm_keluarga_ayah_nama', 'rm_nik_ayah', 'rm_keluarga_ayah_pekerjaan', 'rm_keluarga_ayah_tmp_lahir',
    'rm_keluarga_ayah_tgl_lahir', 'rm_keluarga_ayah_pddk', 'rm_keluarga_ayah_penghasilan',
    'rm_penghasilan_ayah_pddikti', 'rm_keluarga_ayah_alamat', 'rm_keluarga_ayah_telp', 'rm_keluarga_ayah_hp',
    'rm_keluarga_ibu_nama', 'rm_nik_ibu', 'rm_keluarga_ibu_pekerjaan', 'rm_keluarga_ibu_tmp_lahir',
    'rm_keluarga_ibu_tgl_lahir', 'rm_keluarga_ibu_pddk', 'rm_keluarga_ibu_penghasilan',
    'rm_penghasilan_ibu_pddikti', 'rm_keluarga_ibu_alamat', 'rm_keluarga_ibu_telp', 'rm_keluarga_ibu_hp',
    'rm_nama_wali', 'rm_nik_wali', 'rm_alamat_wali', 'rm_wali_tlp', 'rm_wali_hp',
    'rm_wali_tmp_lahir', 'rm_tgl_lahir_wali', 'rm_pddk_wali', 'rm_pekerjaan_wali',
    'rm_penghasilan_wali', 'rm_penghasilan_wali_pddikti'
];

echo "<h3>Perbandingan Kolom:</h3>";

// Kolom yang ada di query tapi tidak ada di database
echo "<h4>Kolom yang ada di query tapi tidak ada di database:</h4>";
$missing_in_db = array_diff($query_columns, $db_columns);
if (empty($missing_in_db)) {
    echo "Tidak ada kolom yang hilang di database<br>";
} else {
    echo "<ul>";
    foreach ($missing_in_db as $column) {
        echo "<li>" . $column . "</li>";
    }
    echo "</ul>";
}

// Kolom yang ada di database tapi tidak ada di query
echo "<h4>Kolom yang ada di database tapi tidak ada di query:</h4>";
$missing_in_query = array_diff($db_columns, $query_columns);
if (empty($missing_in_query)) {
    echo "Tidak ada kolom yang hilang di query<br>";
} else {
    echo "<ul>";
    foreach ($missing_in_query as $column) {
        echo "<li>" . $column . "</li>";
    }
    echo "</ul>";
}

echo "<br>Jumlah kolom di query: " . count($query_columns) . "<br>";
echo "Jumlah kolom di database: " . count($db_columns) . "<br>";

$conn->close();
?> 