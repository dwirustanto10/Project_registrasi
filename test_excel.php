<?php
ob_start();
ob_clean();

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Buat spreadsheet sederhana
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Tulis data test
$sheet->setCellValue('A1', 'Test Data');
$sheet->setCellValue('A2', 'Hello World');

ob_end_clean();

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="test.xlsx"');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?> 