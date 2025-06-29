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
            
            // Pagination settings untuk preview
            $rows_per_page = 10;
            $page = isset($_POST['preview_page']) ? (int)$_POST['preview_page'] : 1;
            $offset = ($page - 1) * $rows_per_page;
            $total_rows = count($rows);
            $total_pages = ceil($total_rows / $rows_per_page);
            
            // Selalu tampilkan 10 baris per halaman untuk preview
            $preview_rows = array_slice($rows, $offset, $rows_per_page);
            
            $preview_html = "<div class='preview-container'>";
            $preview_html .= "<h3>Preview Data Excel</h3>";
            $preview_html .= "<p>Total baris data: " . $total_rows . "</p>";
            
            // Info pagination
            if ($total_pages > 1) {
                $preview_html .= "<div class='pagination-info'>";
                $preview_html .= "<p>Menampilkan " . ($offset + 1) . " - " . min($offset + $rows_per_page, $total_rows) . " dari " . $total_rows . " data</p>";
                $preview_html .= "</div>";
            }
            
            $preview_html .= "<div class='table-responsive'>";
            $preview_html .= "<table>";
            $preview_html .= "<tr>";
            foreach ($headers as $header) {
                $preview_html .= "<th>" . htmlspecialchars($header ?? '') . "</th>";
            }
            $preview_html .= "</tr>";
            
            foreach ($preview_rows as $row) {
                $preview_html .= "<tr>";
                foreach ($row as $cell) {
                    $preview_html .= "<td>" . htmlspecialchars($cell ?? '') . "</td>";
                }
                $preview_html .= "</tr>";
            }
            $preview_html .= "</table>";
            $preview_html .= "</div>";

            // Selalu tampilkan pagination jika ada lebih dari 1 halaman
            if ($total_pages > 1) {
                $preview_html .= "<p>Menampilkan 10 dari " . $total_rows . " baris data</p>";
                
                // Pagination navigation untuk preview
                $preview_html .= "<div class='pagination'>";
                if ($page > 1) {
                    $preview_html .= "<button type='button' class='page-link' data-page='1'>« Pertama</button>";
                    $preview_html .= "<button type='button' class='page-link' data-page='" . ($page - 1) . "'>‹ Sebelumnya</button>";
                }
                
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                for ($i = $start_page; $i <= $end_page; $i++) {
                    $active_class = $i == $page ? 'active' : '';
                    $preview_html .= "<button type='button' class='page-link $active_class' data-page='$i'>$i</button>";
                }
                
                if ($page < $total_pages) {
                    $preview_html .= "<button type='button' class='page-link' data-page='" . ($page + 1) . "'>Selanjutnya ›</button>";
                    $preview_html .= "<button type='button' class='page-link' data-page='$total_pages'>Terakhir »</button>";
                }
                $preview_html .= "</div>";
            }

            $preview_html .= "<form method='post' action=''>";
            $preview_html .= "<input type='hidden' name='confirm_upload' value='1'>";
            $preview_html .= "<button type='submit' class='btn-upload'>Mulai Upload ke Database</button>";
            $preview_html .= "</form>";
            $preview_html .= "</div>";
            
            // Tambahkan script JavaScript untuk pagination
            if ($total_pages > 1) {
                $preview_html .= "<script>
                function changePreviewPage(page) {
                    const formData = new FormData();
                    formData.append('preview_page', page);
                    formData.append('change_page', '1');
                    
                    fetch('upload_handler.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            document.getElementById('preview-container').innerHTML = data.preview;
                        }
                    });
                }
                </script>";
            }

            // Event delegation untuk pagination preview upload
            $preview_html .= "<script>
            document.getElementById('preview-container').addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('page-link')) {
                    const page = e.target.getAttribute('data-page');
                    if (page) {
                        changePreviewPage(page);
                    }
                }
            });
            </script>";

            // Hapus status pemrosesan dari session
            unset($_SESSION['processing_status']);
            
            sendJsonResponse(['status' => 'success', 'preview' => $preview_html]);

        } catch (Exception $e) {
            unset($_SESSION['processing_status']);
            sendJsonResponse(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
    } elseif (isset($_POST['change_page']) && isset($_POST['preview_page'])) {
        // Handler untuk perubahan halaman preview
        if (!isset($_SESSION['excel_data'])) {
            sendJsonResponse(['status' => 'error', 'message' => 'Data Excel tidak ditemukan dalam session.']);
        }
        
        $headers = $_SESSION['excel_data']['headers'];
        $rows = $_SESSION['excel_data']['rows'];
        
        // Pagination settings untuk preview
        $rows_per_page = 10;
        $page = (int)$_POST['preview_page'];
        $offset = ($page - 1) * $rows_per_page;
        $total_rows = count($rows);
        $total_pages = ceil($total_rows / $rows_per_page);
        
        // Selalu tampilkan 10 baris per halaman untuk preview
        $preview_rows = array_slice($rows, $offset, $rows_per_page);
        
        $preview_html = "<div class='preview-container'>";
        $preview_html .= "<h3>Preview Data Excel</h3>";
        $preview_html .= "<p>Total baris data: " . $total_rows . "</p>";
        
        // Info pagination
        if ($total_pages > 1) {
            $preview_html .= "<div class='pagination-info'>";
            $preview_html .= "<p>Menampilkan " . ($offset + 1) . " - " . min($offset + $rows_per_page, $total_rows) . " dari " . $total_rows . " data</p>";
            $preview_html .= "</div>";
        }
        
        $preview_html .= "<div class='table-responsive'>";
        $preview_html .= "<table>";
        $preview_html .= "<tr>";
        foreach ($headers as $header) {
            $preview_html .= "<th>" . htmlspecialchars($header ?? '') . "</th>";
        }
        $preview_html .= "</tr>";
        
        foreach ($preview_rows as $row) {
            $preview_html .= "<tr>";
            foreach ($row as $cell) {
                $preview_html .= "<td>" . htmlspecialchars($cell ?? '') . "</td>";
            }
            $preview_html .= "</tr>";
        }
        $preview_html .= "</table>";
        $preview_html .= "</div>";

        // Selalu tampilkan pagination jika ada lebih dari 1 halaman
        if ($total_pages > 1) {
            $preview_html .= "<p>Menampilkan 10 dari " . $total_rows . " baris data</p>";
            
            // Pagination navigation untuk preview
            $preview_html .= "<div class='pagination'>";
            if ($page > 1) {
                $preview_html .= "<button type='button' class='page-link' data-page='1'>« Pertama</button>";
                $preview_html .= "<button type='button' class='page-link' data-page='" . ($page - 1) . "'>‹ Sebelumnya</button>";
            }
            
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, $page + 2);
            
            for ($i = $start_page; $i <= $end_page; $i++) {
                $active_class = $i == $page ? 'active' : '';
                $preview_html .= "<button type='button' class='page-link $active_class' data-page='$i'>$i</button>";
            }
            
            if ($page < $total_pages) {
                $preview_html .= "<button type='button' class='page-link' data-page='" . ($page + 1) . "'>Selanjutnya ›</button>";
                $preview_html .= "<button type='button' class='page-link' data-page='$total_pages'>Terakhir »</button>";
            }
            $preview_html .= "</div>";
        }

        $preview_html .= "<form method='post' action=''>";
        $preview_html .= "<input type='hidden' name='confirm_upload' value='1'>";
        $preview_html .= "<button type='submit' class='btn-upload'>Mulai Upload ke Database</button>";
        $preview_html .= "</form>";
        $preview_html .= "</div>";
        
        // Tambahkan script JavaScript untuk pagination
        if ($total_pages > 1) {
            $preview_html .= "<script>
            function changePreviewPage(page) {
                const formData = new FormData();
                formData.append('preview_page', page);
                formData.append('change_page', '1');
                
                fetch('upload_handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('preview-container').innerHTML = data.preview;
                    }
                });
            }
            </script>";
        }
        
        // Event delegation untuk pagination preview upload
        $preview_html .= "<script>
        document.getElementById('preview-container').addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('page-link')) {
                const page = e.target.getAttribute('data-page');
                if (page) {
                    changePreviewPage(page);
                }
            }
        });
        </script>";
        
        sendJsonResponse(['status' => 'success', 'preview' => $preview_html]);
    } elseif (isset($_POST['check_status'])) {
        // Endpoint untuk mengecek status pemrosesan
        $status = isset($_SESSION['processing_status']) ? $_SESSION['processing_status'] : null;
        sendJsonResponse(['status' => 'processing', 'message' => $status]);
    }
}

// Jika sampai di sini, berarti request tidak valid
sendJsonResponse(['status' => 'error', 'message' => 'Invalid request']);
?> 