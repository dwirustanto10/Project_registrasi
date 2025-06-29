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
    agama.id AS 'ID Agama',
    rm_nisn AS 'NISN',
    rm_jalur AS 'Jalur Pendaftaran',
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
    rm_alamat_telp AS 'Telp Rumah',
    rm_hp AS 'No HP',
    rm_email AS 'Email',
    rm_terima_kps AS 'Terima KPS',
    rm_no_kps AS 'No KPS',
    rm_nik_ayah AS 'NIK Ayah',
    rm_keluarga_ayah_nama AS 'Nama Ayah',
    rm_keluarga_ayah_tgl_lahir AS 'Tgl Lahir Ayah',
    rm_keluarga_ayah_pddk AS 'Pendidikan Ayah',
    rm_keluarga_ayah_pekerjaan AS 'Pekerjaan Ayah',
    rm_keluarga_ayah_penghasilan AS 'Penghasilan Ayah',
    rm_nik_ibu AS 'NIK Ibu',
    rm_keluarga_ibu_nama AS 'Nama Ibu',
    rm_keluarga_ibu_tgl_lahir AS 'Tanggal Lahir Ibu',
    rm_keluarga_ibu_pddk AS 'Pendidikan Ibu',
    rm_keluarga_ibu_pekerjaan AS 'Pekerjaan Ibu',
    rm_keluarga_ibu_penghasilan AS 'Penghasilan Ibu',
    rm_nama_wali AS 'Nama Wali',
    rm_tgl_lahir_wali AS 'Tanggal Lahir Wali',
    rm_pddk_wali AS 'Pendidikan Wali',
    rm_pekerjaan_wali AS 'Pekerjaan Wali',
    rm_penghasilan_wali AS 'Penghasilan Wali',
    prodi_kode AS 'Kode Prodi',
    rm_jenis_pembiayaan AS 'Jenis Pembiayaan',
    rm_biaya_masuk_kuliah AS 'Biaya Masuk Kuliah',
    rm_asal_perguruan_tinggi AS 'Asal Perguruan Tinggi',
    rm_asal_program_studi AS 'Asal Program Studi'
FROM reg
LEFT JOIN agama ON reg.rm_agama = agama.nama_agama";

$result = $conn->query($sql);

// Buat spreadsheet baru
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set judul sheet
$sheet->setTitle('Data Mahasiswa');

// Ambil header dari hasil query
if ($result && $result->num_rows > 0) {
    $headers = array_keys($result->fetch_assoc());
    
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
    $sheet->getStyle('AC:AC')->getNumberFormat()->setFormatCode('@');
    $sheet->getStyle('AI:AI')->getNumberFormat()->setFormatCode('@');
    $sheet->getStyle('AP:AP')->getNumberFormat()->setFormatCode('@');
    
    // Reset pointer ke awal
    $result->data_seek(0);
    
    // Tulis data ke Excel
    $row = 2;
    while ($data = $result->fetch_assoc()) {
        $colIndex = 0;
        foreach ($data as $value) {
            $currentCol = getColumnLetter($colIndex);
            
            // Khusus untuk kolom NIM (A), NIK (F), dan kolom AC (AC) - paksa sebagai text
            if ($currentCol == 'A' || $currentCol == 'F' || $currentCol == 'AC' || $currentCol == 'AI' || $currentCol == 'AP') {
                $sheet->setCellValueExplicit($currentCol . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            } else {
                $sheet->setCellValue($currentCol . $row, $value);
            }
            
            $colIndex++;
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