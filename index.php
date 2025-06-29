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
                
                for ($i = 0; $i < min(10, count($rows)); $i++) {
                    $preview_html .= "<tr>";
                    foreach ($rows[$i] as $cell) {
                        $preview_html .= "<td>" . htmlspecialchars($cell ?? '') . "</td>";
                    }
                    $preview_html .= "</tr>";
                }
                $preview_html .= "</table>";
                $preview_html .= "</div>";

                if (count($rows) > 10) {
                    $preview_html .= "<p>Menampilkan 10 dari " . count($rows) . " baris data</p>";
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
            
            for ($i = 0; $i < min(10, count($rows)); $i++) {
                echo "<tr>";
                foreach ($rows[$i] as $cell) {
                    echo "<td>" . htmlspecialchars($cell ?? '') . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
            echo "</div>";

            if (count($rows) > 10) {
                echo "<p>Menampilkan 10 dari " . count($rows) . " baris data</p>";
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


            // Pastikan jumlah kolom sesuai dengan database (misal 50 kolom)
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
    <title>Registrasi Mahasiswa - SPA</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <nav>
            <ul>
                <li><a href="#" data-page="upload" class="active">Upload Data</a></li>
                <li><a href="#" data-page="tampil">Tampil Data</a></li>
            </ul>
        </nav>
        <div id="main-content"></div>
    </div>
    <script>
        function setActiveMenu(page) {
            document.querySelectorAll('nav a').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('data-page') === page) link.classList.add('active');
            });
        }
        function loadPage(page) {
            setActiveMenu(page);
            let url = '';
            if (page === 'upload') url = 'upload_form.html';
            if (page === 'tampil') {
                loadTampilData(1);
                return;
            }
            fetch(url)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('main-content').innerHTML = html;
                    if (page === 'upload') initUploadForm();
                });
        }
        
        // Fungsi AJAX untuk pagination tampil data
        function loadTampilData(page = 1) {
            fetch('tampil_data.php?page=' + page)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('main-content').innerHTML = html;
                });
        }
        document.querySelectorAll('nav a').forEach(link => {
            link.addEventListener('click', function(e) {
            e.preventDefault();
                const page = this.getAttribute('data-page');
                loadPage(page);
            });
        });
        // SPA: load default page
        loadPage('upload');

        // Inisialisasi upload form (AJAX)
        function initUploadForm() {
            const uploadForm = document.getElementById('uploadForm');
            if (!uploadForm) return;
            const uploadStatus = document.getElementById('upload-status');
            const previewContainer = document.getElementById('preview-container');
            uploadStatus.style.display = 'none';
            previewContainer.innerHTML = '';
            uploadForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
            uploadStatus.style.display = 'none';
            previewContainer.innerHTML = '';
            fetch('upload_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    uploadStatus.className = 'upload-status success';
                    uploadStatus.textContent = 'Upload berhasil!';
                    uploadStatus.style.display = 'block';
                    previewContainer.innerHTML = data.preview;
                } else {
                    uploadStatus.className = 'upload-status error';
                    uploadStatus.textContent = data.message || 'Terjadi kesalahan saat upload';
                    uploadStatus.style.display = 'block';
                }
            })
            .catch(err => {
                uploadStatus.className = 'upload-status error';
                uploadStatus.textContent = 'Terjadi error: ' + err;
                uploadStatus.style.display = 'block';
            });
        });
            // Event delegation untuk konfirmasi upload
            previewContainer.addEventListener('submit', function(e) {
            if (e.target && e.target.matches('form')) {
                e.preventDefault();
                fetch('upload_to_db.php', {
                    method: 'POST'
                })
                .then(res => res.json())
                .then(data => {
                    let html = '';
                    if (data.status === 'success') {
                        html += `<div class="result-container">
                            <h3>Hasil Upload</h3>
                            <p>Total baris dalam file: ${data.total}</p>
                            <p>Baris berhasil diupload: ${data.success}</p>
                            <p>Baris gagal diupload: ${data.failed}</p>`;
                        if (data.errors && data.errors.length > 0) {
                            html += `<div class="error-container">
                                <h4>Detail Error:</h4>
                                <table border="1">
                                <tr><th>Baris</th><th>Alasan Error</th></tr>`;
                            data.errors.forEach(err => {
                                html += `<tr><td>${err.row}</td><td>${err.reason}${err.count ? ' (Jumlah kolom: ' + err.count + ')' : ''}</td></tr>`;
                            });
                            html += `</table></div>`;
                        }
                        html += `</div>`;
                    } else {
                        html = `<div class="upload-status error">${data.message}</div>`;
                    }
                        previewContainer.innerHTML = html;
                });
            }
        });
        }
    </script>
</body>
</html>
<?php
ob_end_flush(); // Akhiri output buffering
?>