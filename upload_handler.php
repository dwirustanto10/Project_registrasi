<?php
// Pastikan tidak ada output sebelum ini
ob_start();

session_start();
header('Content-Type: application/json');

require_once 'koneksi.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

function sendJsonResponse($data) {
    ob_clean();
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['file'])) {
        if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            sendJsonResponse(['status' => 'error', 'message' => 'Error upload file: ' . $_FILES['file']['error']]);
        }

        $file = $_FILES['file']['tmp_name'];
        $file_name = $_FILES['file']['name'];

        if (!file_exists($file)) {
            sendJsonResponse(['status' => 'error', 'message' => 'Error: File temporary tidak ditemukan']);
        }

        $allowed_extensions = ['xls', 'xlsx', 'csv'];
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);

        if (!in_array($file_extension, $allowed_extensions)) {
            sendJsonResponse(['status' => 'error', 'message' => 'Error: Hanya file Excel (.xls, .xlsx) atau CSV yang diperbolehkan.']);
        }

        try {
            // Simpan status pemrosesan di session
            $_SESSION['processing_status'] = 'Memulai pemrosesan file...';
            
            // Proses file Excel
            $spreadsheet = IOFactory::load($file);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            $_SESSION['processing_status'] = 'Membaca data Excel...';
            
            $headers = array_shift($rows);
            $_SESSION['excel_data'] = [
                'headers' => $headers,
                'rows' => $rows
            ];

            $_SESSION['processing_status'] = 'Menyiapkan preview...';
            
            $preview_html = "<div class='preview-container'>";
            $preview_html .= "<h3>Preview Data Excel</h3>";
            $preview_html .= "<p>Total baris data: " . count($rows) . "</p>";
            
            $preview_html .= "<div class='table-responsive'>";
            $preview_html .= "<table border='1'>";
            $preview_html .= "<tr>";
            foreach ($headers as $header) {
                $preview_html .= "<th>" . htmlspecialchars($header ?? '') . "</th>";
            }
            $preview_html .= "</tr>";
            
            for ($i = 0; $i < min(5, count($rows)); $i++) {
                $preview_html .= "<tr>";
                foreach ($rows[$i] as $cell) {
                    $preview_html .= "<td>" . htmlspecialchars($cell ?? '') . "</td>";
                }
                $preview_html .= "</tr>";
            }
            $preview_html .= "</table>";
            $preview_html .= "</div>";

            if (count($rows) > 5) {
                $preview_html .= "<p>Menampilkan 5 dari " . count($rows) . " baris data</p>";
            }

            $preview_html .= "<form method='post' action=''>";
            $preview_html .= "<input type='hidden' name='confirm_upload' value='1'>";
            $preview_html .= "<button type='submit' class='btn-upload'>Mulai Upload ke Database</button>";
            $preview_html .= "</form>";
            $preview_html .= "</div>";

            // Hapus status pemrosesan dari session
            unset($_SESSION['processing_status']);
            
            sendJsonResponse(['status' => 'success', 'preview' => $preview_html]);

        } catch (Exception $e) {
            unset($_SESSION['processing_status']);
            sendJsonResponse(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
    } elseif (isset($_POST['check_status'])) {
        // Endpoint untuk mengecek status pemrosesan
        $status = isset($_SESSION['processing_status']) ? $_SESSION['processing_status'] : null;
        sendJsonResponse(['status' => 'processing', 'message' => $status]);
    }
}

// Jika sampai di sini, berarti request tidak valid
sendJsonResponse(['status' => 'error', 'message' => 'Invalid request']);
?> 