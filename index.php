<?php
session_start(); // Pindahkan session_start ke awal file
ob_start(); // Mulai output buffering

// Tampilkan informasi konfigurasi upload hanya jika bukan request AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    echo "<div class='config-info'>";
    echo "<h3>Informasi Konfigurasi Upload</h3>";
    echo "<p>Batas ukuran file: " . ini_get('upload_max_filesize') . "</p>";
    echo "<p>Batas ukuran POST: " . ini_get('post_max_size') . "</p>";
    echo "<p>Batas memori: " . ini_get('memory_limit') . "</p>";
    echo "</div>";
}

// koneksi.php - File untuk koneksi database
require_once 'koneksi.php';
require 'vendor/autoload.php'; // Pastikan sudah install PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

// Cek apakah ini adalah request AJAX
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    ob_clean(); // Bersihkan buffer
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_FILES['file'])) {
            if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['status' => 'error', 'message' => 'Error upload file: ' . $_FILES['file']['error']]);
                exit;
            }

            $file = $_FILES['file']['tmp_name'];
            $file_name = $_FILES['file']['name'];

            if (!file_exists($file)) {
                echo json_encode(['status' => 'error', 'message' => 'Error: File temporary tidak ditemukan']);
                exit;
            }

            $allowed_extensions = ['xls', 'xlsx', 'csv'];
            $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);

            if (!in_array($file_extension, $allowed_extensions)) {
                echo json_encode(['status' => 'error', 'message' => 'Error: Hanya file Excel (.xls, .xlsx) atau CSV yang diperbolehkan.']);
                exit;
            }

            try {
                // Kirim status memulai pemrosesan
                ob_clean(); // Bersihkan buffer sebelum mengirim response
                echo json_encode(['status' => 'processing', 'message' => 'Memulai pemrosesan file...']);
                flush();
                ob_flush();

                $spreadsheet = IOFactory::load($file);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();

                $headers = array_shift($rows);
                $_SESSION['excel_data'] = [
                    'headers' => $headers,
                    'rows' => $rows
                ];

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

                ob_clean(); // Bersihkan buffer sebelum mengirim response final
                echo json_encode(['status' => 'success', 'preview' => $preview_html]);
                exit;

            } catch (Exception $e) {
                ob_clean(); // Bersihkan buffer sebelum mengirim error
                echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
                exit;
            }
        }
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['file'])) {
        if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            die("Error upload file: " . $_FILES['file']['error']);
        }

        $file = $_FILES['file']['tmp_name'];
        $file_name = $_FILES['file']['name'];

        if (!file_exists($file)) {
            die("Error: File temporary tidak ditemukan");
        }

        $allowed_extensions = ['xls', 'xlsx', 'csv'];
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);

        if (!in_array($file_extension, $allowed_extensions)) {
            die("Error: Hanya file Excel (.xls, .xlsx) atau CSV yang diperbolehkan.");
        }

        try {
            $spreadsheet = IOFactory::load($file);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            $headers = array_shift($rows);
            $_SESSION['excel_data'] = [
                'headers' => $headers,
                'rows' => $rows
            ];

            echo "<div class='preview-container'>";
            echo "<h3>Preview Data Excel</h3>";
            echo "<p>Total baris data: " . count($rows) . "</p>";
            
            echo "<div class='table-responsive'>";
            echo "<table border='1'>";
            echo "<tr>";
            foreach ($headers as $header) {
                echo "<th>" . htmlspecialchars($header ?? '') . "</th>";
            }
            echo "</tr>";
            
            for ($i = 0; $i < min(5, count($rows)); $i++) {
                echo "<tr>";
                foreach ($rows[$i] as $cell) {
                    echo "<td>" . htmlspecialchars($cell ?? '') . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
            echo "</div>";

            if (count($rows) > 5) {
                echo "<p>Menampilkan 5 dari " . count($rows) . " baris data</p>";
            }

            echo "<form method='post' action=''>";
            echo "<input type='hidden' name='confirm_upload' value='1'>";
            echo "<button type='submit' class='btn-upload'>Mulai Upload ke Database</button>";
            echo "</form>";
            echo "</div>";

        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    } elseif (isset($_POST['confirm_upload'])) {
        if (!isset($_SESSION['excel_data'])) {
            die("Error: Data Excel tidak ditemukan dalam session. Silakan upload file Excel kembali.");
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
            die("Error: Gagal mempersiapkan query database");
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
            
            // Proses jenis kelamin
            if ($processed_row[14] !== NULL) {
                $jenis_kelamin = strtoupper($processed_row[14]);
                // Cari id jenis kelamin dari referensi
                $stmt_jenis_kelamin = $conn->prepare("SELECT id FROM ref_jenis_kelamin WHERE nama = ?");
                $stmt_jenis_kelamin->bind_param("s", $jenis_kelamin);
                $stmt_jenis_kelamin->execute();
                $result = $stmt_jenis_kelamin->get_result();
                if ($row_jenis_kelamin = $result->fetch_assoc()) {
                    $processed_row[14] = $row_jenis_kelamin['id'];
                } else {
                    $error_rows[] = [
                        'row' => $index + 1,
                        'reason' => 'Jenis kelamin tidak valid: ' . $jenis_kelamin
                    ];
                    continue;
                }
                $stmt_jenis_kelamin->close();
            }
            
            if ($processed_row[37] !== NULL) {
                $processed_row[37] = strtoupper($processed_row[37]);
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

        echo "<div class='result-container'>";
        echo "<h3>Hasil Upload</h3>";
        echo "<p>Total baris dalam file: " . $total_rows . "</p>";
        echo "<p>Baris berhasil diupload: " . $success_count . "</p>";
        echo "<p>Baris gagal diupload: " . count($error_rows) . "</p>";
        
        if (!empty($error_rows)) {
            echo "<div class='error-container'>";
            echo "<h4>Detail Error:</h4>";
            echo "<table border='1'>";
            echo "<tr><th>Baris</th><th>Alasan Error</th></tr>";
            foreach ($error_rows as $error) {
                echo "<tr>";
                echo "<td>" . $error['row'] . "</td>";
                echo "<td>" . $error['reason'] . "</td>";
                if (isset($error['count'])) {
                    echo " (Jumlah kolom: " . $error['count'] . ")";
                }
                echo "</td></tr>";
            }
            echo "</table>";
            echo "</div>";
        }
        echo "</div>";

        $stmt->close();
        $conn->close();
        unset($_SESSION['excel_data']);
    }
}
?>

<!-- Form HTML untuk upload -->
<!DOCTYPE html>
<html>
<head>
    <title>Upload Excel ke Database</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .preview-container, .result-container {
            margin: 20px 0;
            padding: 20px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .upload-status {
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
            display: none;
        }
        .upload-status.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .upload-status.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .table-responsive {
            overflow-x: auto;
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
        .error-container {
            margin-top: 20px;
            padding: 15px;
            background-color: #fff3f3;
            border: 1px solid #ffcdd2;
            border-radius: 4px;
        }
        h3 {
            color: #333;
            margin-bottom: 20px;
        }
        h4 {
            color: #666;
            margin-bottom: 15px;
        }
        .config-info {
            background-color: #e8f5e9;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #c8e6c9;
        }
        .config-info h3 {
            margin-top: 0;
            color: #2e7d32;
        }
        .config-info p {
            margin: 5px 0;
            color: #1b5e20;
        }
        .processing-status {
            display: none;
            margin-top: 10px;
            padding: 10px;
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Upload File Excel</h2>
        <form id="uploadForm" method="post" enctype="multipart/form-data">
            <input type="file" name="file" accept=".xls,.xlsx,.csv" required>
            <button type="submit">Upload dan Preview</button>
        </form>

        <div class="processing-status"></div>
        <div class="upload-status"></div>
        <div id="preview-container"></div>
    </div>

    <script>
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const uploadStatus = document.querySelector('.upload-status');
            const processingStatus = document.querySelector('.processing-status');
            const previewContainer = document.getElementById('preview-container');
            
            uploadStatus.style.display = 'none';
            processingStatus.style.display = 'none';
            previewContainer.innerHTML = '';
            
            const xhr = new XMLHttpRequest();
            
            xhr.addEventListener('load', function() {
                if (xhr.status === 200) {
                    try {
                        console.log('Response:', xhr.responseText);
                        const response = JSON.parse(xhr.responseText);
                        console.log('Parsed response:', response);
                        
                        if (response.status === 'success') {
                            uploadStatus.className = 'upload-status success';
                            uploadStatus.textContent = 'Upload berhasil!';
                            uploadStatus.style.display = 'block';
                            processingStatus.style.display = 'none';
                            
                            // Tampilkan preview data
                            previewContainer.innerHTML = response.preview;
                        } else if (response.status === 'processing') {
                            processingStatus.textContent = response.message;
                            processingStatus.style.display = 'block';
                        } else {
                            uploadStatus.className = 'upload-status error';
                            uploadStatus.textContent = response.message || 'Terjadi kesalahan saat upload';
                            uploadStatus.style.display = 'block';
                            processingStatus.style.display = 'none';
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        uploadStatus.className = 'upload-status error';
                        uploadStatus.textContent = 'Terjadi kesalahan saat memproses response: ' + e.message;
                        uploadStatus.style.display = 'block';
                        processingStatus.style.display = 'none';
                    }
                } else {
                    uploadStatus.className = 'upload-status error';
                    uploadStatus.textContent = 'Upload gagal: ' + xhr.statusText;
                    uploadStatus.style.display = 'block';
                    processingStatus.style.display = 'none';
                }
            });
            
            xhr.addEventListener('error', function() {
                console.error('XHR Error');
                uploadStatus.className = 'upload-status error';
                uploadStatus.textContent = 'Terjadi kesalahan saat upload';
                uploadStatus.style.display = 'block';
                processingStatus.style.display = 'none';
            });
            
            xhr.open('POST', 'upload_handler.php', true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.send(formData);

            // Mulai polling untuk status pemrosesan
            const checkStatus = setInterval(function() {
                const statusXhr = new XMLHttpRequest();
                statusXhr.open('POST', 'upload_handler.php', true);
                statusXhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                
                const statusFormData = new FormData();
                statusFormData.append('check_status', '1');
                
                statusXhr.onload = function() {
                    if (statusXhr.status === 200) {
                        try {
                            const response = JSON.parse(statusXhr.responseText);
                            if (response.status === 'processing' && response.message) {
                                processingStatus.textContent = response.message;
                                processingStatus.style.display = 'block';
                            } else {
                                clearInterval(checkStatus);
                            }
                        } catch (e) {
                            console.error('Error checking status:', e);
                            clearInterval(checkStatus);
                        }
                    }
                };
                
                statusXhr.send(statusFormData);
            }, 1000); // Cek status setiap 1 detik
        });
    </script>
</body>

copyright &copy; 2025 dwikemaren
</html>
<?php
ob_end_flush(); // Akhiri output buffering
?>