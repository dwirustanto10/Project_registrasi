<?php
// Pastikan tidak ada output sebelum ini
ob_start();
ob_clean();

require_once 'koneksi.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Helper function untuk mengkonversi index ke huruf kolom
function getColumnLetter($index) {
    $letter = '';
    while ($index >= 0) {
        $letter = chr(65 + ($index % 26)) . $letter;
        $index = floor($index / 26) - 1;
    }
    return $letter;
}

// Query untuk mengambil data (semua data tanpa pagination)
$sql = "SELECT 
    rm_nim AS 'NIM',
    rm_nama AS 'NAMA',
    rm_tmp_lahir AS 'Tempat Lahir',
    rm_tgl_lahir AS 'Tanggal Lahir',
    jenis_kelamin AS 'Jenis Kelamin',
    rm_nik AS 'NIK',
    CASE 
        WHEN UPPER(TRIM(reg.rm_agama)) IN ('KRISTEN', 'PROTESTAN') THEN 2
        ELSE agama.id
    END AS 'ID Agama',
    rm_nisn AS 'NISN',
    jalur_daftar.kode_jalur_masuk AS 'Kode Jalur Masuk',
    rm_npwp AS 'NPWP',
    kewarganegaraan AS 'Kewarganegaraan',
    rm_id_jenis_daftar AS 'Jenis Pendaftaran',
    rm_tgl_masuk_kuliah AS 'Tgl Masuk Kuliah',
    rm_mulai_smt AS 'Mulai Semester',
    rm_jalan AS 'Jalan',
    rm_rt AS 'RT',
    rm_rw AS 'RW',
    rm_nama_dusun AS 'Nama Dusun',
    rm_desa_kelurahan AS 'Kelurahan',
    kecamatan_ortu AS 'Kecamatan',
    rm_kode_pos AS 'Kode Pos',
    rm_jns_tinggal AS 'Jenis Tinggal',
    rm_jns_tranportasi AS 'Alat Transportasi',
    REPLACE(
        REPLACE(
            CASE
                WHEN LEFT(TRIM(REPLACE(REPLACE(reg.rm_alamat_telp, '-', ''), ' ', '')), 3) = '+62'
                    THEN CONCAT('0', SUBSTRING(TRIM(REPLACE(REPLACE(reg.rm_alamat_telp, '-', ''), ' ', '')), 4))
                WHEN LEFT(TRIM(REPLACE(REPLACE(reg.rm_alamat_telp, '-', ''), ' ', '')), 2) = '62'
                    THEN CONCAT('0', SUBSTRING(TRIM(REPLACE(REPLACE(reg.rm_alamat_telp, '-', ''), ' ', '')), 3))
                ELSE TRIM(REPLACE(REPLACE(reg.rm_alamat_telp, '-', ''), ' ', ''))
            END
        , '-', ''), ' ', '') AS 'Nomor Telepon',
    rm_hp AS 'No HP',
    TRIM(rm_email) AS 'Email',
    rm_terima_kps AS 'Terima KPS',
    rm_no_kps AS 'No KPS',
    rm_nik_ayah AS 'NIK Ayah',
    rm_keluarga_ayah_nama AS 'Nama Ayah',
    rm_keluarga_ayah_tgl_lahir AS 'Tgl Lahir Ayah',
    pendidikan_ayah.kode_pend AS 'Kode Pendidikan Ayah',
    pekerjaan_ayah.kode_kerja AS 'Kode Pekerjaan Ayah',
    rm_penghasilan_ayah_pddikti AS 'Nominal Penghasilan Ayah',
    -- pr_ayah.kode_penghasilan AS 'Kode Penghasilan Ayah',
    rm_nik_ibu AS 'NIK Ibu',
    rm_keluarga_ibu_nama AS 'Nama Ibu',
    rm_keluarga_ibu_tgl_lahir AS 'Tanggal Lahir Ibu',
    pendidikan_ibu.kode_pend AS 'Kode Pendidikan Ibu',
    pekerjaan_ibu.kode_kerja AS 'Kode Pekerjaan Ibu',
    rm_penghasilan_ibu_pddikti AS 'Nominal Penghasilan Ibu',
    -- pr_ibu.kode_penghasilan AS 'Kode Penghasilan Ibu',
    rm_nama_wali AS 'Nama Wali',
    rm_tgl_lahir_wali AS 'Tanggal Lahir Wali',
    pendidikan_wali.kode_pend AS 'Kode Pendidikan Wali',
    pekerjaan_wali.kode_kerja AS 'Kode Pekerjaan Wali',
    rm_penghasilan_wali_pddikti AS 'Nominal Penghasilan Wali',
    -- pr_wali.kode_penghasilan AS 'Kode Penghasilan Wali',
    prodi.kode_prodi AS 'Kode Prodi',
    rm_jenis_pembiayaan AS 'Jenis Pembiayaan',
    rm_biaya_masuk_kuliah AS 'Biaya Masuk Kuliah',
    rm_asal_perguruan_tinggi AS 'Asal Perguruan Tinggi',
    rm_asal_program_studi AS 'Asal Program Studi',
    prodi.fakultas AS 'Fakultas',
    CASE
        WHEN CHAR_LENGTH(rm_nik) <> 16
          OR CHAR_LENGTH(rm_nik_ayah) <> 16
          OR CHAR_LENGTH(rm_nik_ibu) <> 16
        THEN 'SILAKAN DI CEK DATA NIK'
        ELSE ''
    END AS 'Catatan NIK',
    CASE 
        WHEN UPPER(TRIM(kewarganegaraan)) = 'wni' THEN 'ID'
        ELSE kewarganegaraan
    END AS 'Kewarganegaraan',
    CASE 
        WHEN UPPER(TRIM(kewarganegaraan)) <> 'wni' THEN 'silakan cek kewarganegaraan'
        ELSE ''
    END AS 'Catatan Kewarganegaraan',
    CASE 
        WHEN TRIM(rm_email) IS NULL OR TRIM(rm_email) = '' OR TRIM(rm_email) NOT LIKE '%@%' THEN 'silakan cek email'
        ELSE ''
    END AS 'Catatan Email'
FROM reg
LEFT JOIN agama ON reg.rm_agama = agama.nama_agama
LEFT JOIN jalur_daftar ON reg.rm_jalur = jalur_daftar.jalur_masuk
LEFT JOIN pendidikan AS pendidikan_ayah ON reg.rm_keluarga_ayah_pddk = pendidikan_ayah.nama_pend
LEFT JOIN pekerjaan AS pekerjaan_ayah ON reg.rm_keluarga_ayah_pekerjaan = pekerjaan_ayah.nama_kerja
-- LEFT JOIN penghasilan_ref AS pr_ayah ON reg.rm_keluarga_ayah_penghasilan >= pr_ayah.min_penghasilan AND reg.rm_keluarga_ayah_penghasilan <= pr_ayah.max_penghasilan
LEFT JOIN pendidikan AS pendidikan_ibu ON reg.rm_keluarga_ibu_pddk = pendidikan_ibu.nama_pend
LEFT JOIN pekerjaan AS pekerjaan_ibu ON reg.rm_keluarga_ibu_pekerjaan = pekerjaan_ibu.nama_kerja
-- LEFT JOIN penghasilan_ref AS pr_ibu ON reg.rm_keluarga_ibu_penghasilan >= pr_ibu.min_penghasilan AND reg.rm_keluarga_ibu_penghasilan <= pr_ibu.max_penghasilan
LEFT JOIN pendidikan AS pendidikan_wali ON reg.rm_pddk_wali = pendidikan_wali.nama_pend
LEFT JOIN pekerjaan AS pekerjaan_wali ON reg.rm_pekerjaan_wali = pekerjaan_wali.nama_kerja
-- LEFT JOIN penghasilan_ref AS pr_wali ON reg.rm_penghasilan_wali >= pr_wali.min_penghasilan AND reg.rm_penghasilan_wali <= pr_wali.max_penghasilan
LEFT JOIN prodi ON TRIM(LOWER(reg.prodi_nama)) = TRIM(LOWER(prodi.prodi_nama))
    AND TRIM(LOWER(reg.prodi_jenjang)) = TRIM(LOWER(prodi.prodi_jenjang))
";

$result = $conn->query($sql);

// Buat spreadsheet baru
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set judul sheet
$sheet->setTitle('Data Mahasiswa');

// Ambil header dari hasil query
if ($result && $result->num_rows > 0) {
    $headers = array_keys($result->fetch_assoc());
    // Cari index kolom NIK Ayah dan NIK Ibu
    $nik_ayah_idx = array_search('NIK Ayah', $headers);
    $nik_ibu_idx = array_search('NIK Ibu', $headers);
    // Tulis header ke Excel
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '1', $header);
        $col++;
    }
    
    // Set format text untuk kolom NIM (kolom A)
    $sheet->getStyle('A:A')->getNumberFormat()->setFormatCode('@');
    
    // Set format text untuk kolom NIK (F) dan kolom AC dengan format yang lebih kuat
    $sheet->getStyle('F:F')->getNumberFormat()->setFormatCode('@');
    $sheet->getStyle('AD:AD')->getNumberFormat()->setFormatCode('@');
    $sheet->getStyle('AJ:AJ')->getNumberFormat()->setFormatCode('@');
    $sheet->getStyle('AP:AP')->getNumberFormat()->setFormatCode('@');
    $sheet->getStyle('AG:AG')->getNumberFormat()->setFormatCode('@'); // Email
    
    // Set format untuk kolom NIK Ayah dan NIK Ibu
    if ($nik_ayah_idx !== false) {
        $colLetter = getColumnLetter($nik_ayah_idx);
        $sheet->getStyle($colLetter . ':' . $colLetter)->getNumberFormat()->setFormatCode('@');
    }
    if ($nik_ibu_idx !== false) {
        $colLetter = getColumnLetter($nik_ibu_idx);
        $sheet->getStyle($colLetter . ':' . $colLetter)->getNumberFormat()->setFormatCode('@');
    }

    // Reset pointer ke awal
    $result->data_seek(0);
    
    // Tulis data ke Excel
    $row = 2;
    while ($data = $result->fetch_assoc()) {
        foreach (array_values($data) as $colIndex => $value) {
            $currentCol = getColumnLetter($colIndex);
            // Paksa kolom NIK (F), NIK Ayah, dan NIK Ibu sebagai text
            if ($currentCol == 'F' || $colIndex == $nik_ayah_idx || $colIndex == $nik_ibu_idx) {
                $sheet->setCellValueExplicit($currentCol . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            } else {
                $sheet->setCellValue($currentCol . $row, $value);
            }
        }
        $row++;
    }
    
    // Auto-size columns
    foreach (range('A', $sheet->getHighestColumn()) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
}

// Bersihkan output buffer
ob_end_clean();

// Set header untuk download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Data_Mahasiswa_' . date('Y-m-d_H-i-s') . '.xlsx"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: cache, must-revalidate');
header('Pragma: public');

// Tulis file ke output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

$conn->close();
exit;
?> 