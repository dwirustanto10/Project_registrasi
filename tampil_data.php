<?php
require_once 'koneksi.php';

// Pagination settings
$rows_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $rows_per_page;

// Query untuk menghitung total data
$count_sql = "SELECT COUNT(*) as total FROM reg";
$count_result = $conn->query($count_sql);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $rows_per_page);

// Query hanya kolom tertentu sesuai permintaan dengan LIMIT dan OFFSET
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
    rm_email AS 'Email',
    rm_terima_kps AS 'Terima KPS',
    rm_no_kps AS 'No KPS',
    rm_nik_ayah AS 'NIK Ayah',
    rm_keluarga_ayah_nama AS 'Nama Ayah',
    rm_keluarga_ayah_tgl_lahir AS 'Tgl Lahir Ayah',
    -- reg.rm_keluarga_ayah_pddk AS 'Nama Pendidikan Ayah',
    pendidikan_ayah.kode_pend AS 'Kode Pendidikan Ayah',
    pekerjaan_ayah.kode_kerja AS 'Kode Pekerjaan Ayah',
    rm_penghasilan_ayah_pddikti AS 'Nominal Penghasilan Ayah',
    -- pr_ayah.kode_penghasilan AS 'Kode Penghasilan Ayah',
    rm_nik_ibu AS 'NIK Ibu',
    rm_keluarga_ibu_nama AS 'Nama Ibu',
    rm_keluarga_ibu_tgl_lahir AS 'Tanggal Lahir Ibu',
    -- reg.rm_keluarga_ibu_pddk AS 'Nama Pendidikan Ibu',
    pendidikan_ibu.kode_pend AS 'Kode Pendidikan Ibu',
    pekerjaan_ibu.kode_kerja AS 'Kode Pekerjaan Ibu',
    rm_penghasilan_ibu_pddikti AS 'Nominal Penghasilan Ibu',
    -- pr_ibu.kode_penghasilan AS 'Kode Penghasilan Ibu',
    rm_nama_wali AS 'Nama Wali',
    rm_tgl_lahir_wali AS 'Tanggal Lahir Wali',
    -- reg.rm_pddk_wali AS 'Nama Pendidikan Wali',
    pendidikan_wali.kode_pend AS 'Kode Pendidikan Wali',
    pekerjaan_wali.kode_kerja AS 'Kode Pekerjaan Wali',
    rm_penghasilan_wali_pddikti AS 'Nominal Penghasilan Wali',
    -- pr_wali.kode_penghasilan AS 'Kode Penghasilan Wali',
    prodi.kode_prodi AS 'Kode Prodi',
    rm_jenis_pembiayaan AS 'Jenis Pembiayaan',
    rm_biaya_masuk_kuliah AS 'Biaya Masuk Kuliah',
    rm_asal_perguruan_tinggi AS 'Asal Perguruan Tinggi',
    rm_asal_program_studi AS 'Asal Program Studi',
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
    END AS 'Catatan Kewarganegaraan'
FROM reg
LEFT JOIN agama ON reg.rm_agama = agama.nama_agama
LEFT JOIN jalur_daftar ON reg.rm_jalur = jalur_daftar.jalur_masuk
LEFT JOIN pendidikan AS pendidikan_ayah ON reg.rm_keluarga_ayah_pddk = pendidikan_ayah.nama_pend
LEFT JOIN pekerjaan AS pekerjaan_ayah ON reg.rm_keluarga_ayah_pekerjaan = pekerjaan_ayah.nama_kerja
LEFT JOIN pendidikan AS pendidikan_ibu ON reg.rm_keluarga_ibu_pddk = pendidikan_ibu.nama_pend
LEFT JOIN pekerjaan AS pekerjaan_ibu ON reg.rm_keluarga_ibu_pekerjaan = pekerjaan_ibu.nama_kerja
LEFT JOIN pendidikan AS pendidikan_wali ON reg.rm_pddk_wali = pendidikan_wali.nama_pend
LEFT JOIN pekerjaan AS pekerjaan_wali ON reg.rm_pekerjaan_wali = pekerjaan_wali.nama_kerja
-- LEFT JOIN penghasilan_ref AS pr_ayah ON reg.rm_keluarga_ayah_penghasilan >= pr_ayah.min_penghasilan AND reg.rm_keluarga_ayah_penghasilan <= pr_ayah.max_penghasilan
-- LEFT JOIN penghasilan_ref AS pr_ibu ON reg.rm_keluarga_ibu_penghasilan >= pr_ibu.min_penghasilan AND reg.rm_keluarga_ibu_penghasilan <= pr_ibu.max_penghasilan
-- LEFT JOIN penghasilan_ref AS pr_wali ON reg.rm_penghasilan_wali >= pr_wali.min_penghasilan AND reg.rm_penghasilan_wali <= pr_wali.max_penghasilan
LEFT JOIN prodi ON TRIM(LOWER(reg.prodi_nama)) = TRIM(LOWER(prodi.prodi_nama))
    AND TRIM(LOWER(reg.prodi_jenjang)) = TRIM(LOWER(prodi.prodi_jenjang))

LIMIT $rows_per_page OFFSET $offset";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Daftar Data Mahasiswa</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .message {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Daftar Data Mahasiswa</h2>
        
        <!-- Info Pagination -->
        <div class="pagination-info">
            <p>Menampilkan <?= ($offset + 1) ?> - <?= min($offset + $rows_per_page, $total_rows) ?> dari <?= $total_rows ?> data</p>
        </div>
        
        <!-- Tombol Download Excel dan Hapus Data -->
        <div style="text-align: center; margin-bottom: 20px;">
            <a href="download_excel.php" class="download-btn" style="margin-right: 10px;">
                📥 Download Data Excel
            </a>
            <a href="hapus_data.php" class="download-btn" style="background: #dc3545;">
                🗑️ Hapus Semua Data
            </a>
        </div>
        
        <!-- Tombol Toggle Kolom untuk Mobile -->
        <div style="text-align: center; margin-bottom: 15px;">
            <button type="button" onclick="toggleColumns()" class="btn-toggle-columns" style="display: none;">
                📋 Tampilkan Semua Kolom
            </button>
        </div>
        
        <!-- Container untuk tabel responsif -->
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php 
                            $headers = array_keys($result->fetch_assoc());
                            foreach($headers as $col): ?>
                                <th><?= htmlspecialchars($col) ?></th>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result && $result->num_rows > 0) {
                        $result->data_seek(0); // Reset pointer ke awal
                        while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <?php foreach($row as $cell): ?>
                                <td class="wrap-text"><?= htmlspecialchars($cell ?? '') ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endwhile; } else { ?>
                        <tr><td colspan="100">Tidak ada data.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Navigation -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <button type="button" class="page-link" onclick="loadTampilData(1)">« Pertama</button>
                <button type="button" class="page-link" onclick="loadTampilData(<?= $page - 1 ?>)">‹ Sebelumnya</button>
            <?php endif; ?>
            
            <?php
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, $page + 2);
            
            for ($i = $start_page; $i <= $end_page; $i++): ?>
                <button type="button" class="page-link <?= $i == $page ? 'active' : '' ?>" onclick="loadTampilData(<?= $i ?>)"><?= $i ?></button>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <button type="button" class="page-link" onclick="loadTampilData(<?= $page + 1 ?>)">Selanjutnya ›</button>
                <button type="button" class="page-link" onclick="loadTampilData(<?= $total_pages ?>)">Terakhir »</button>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Toggle kolom untuk mobile
        function toggleColumns() {
            const table = document.querySelector('.data-table');
            const button = document.querySelector('.btn-toggle-columns');
            const hiddenColumns = table.querySelectorAll('th:nth-child(n+8), td:nth-child(n+8)');
            
            if (hiddenColumns[0].style.display === 'none' || hiddenColumns[0].style.display === '') {
                // Tampilkan semua kolom
                hiddenColumns.forEach(col => col.style.display = '');
                button.textContent = '📋 Sembunyikan Kolom';
            } else {
                // Sembunyikan kolom
                hiddenColumns.forEach(col => col.style.display = 'none');
                button.textContent = '📋 Tampilkan Semua Kolom';
            }
        }
        
        // Tampilkan tombol toggle di mobile
        function checkScreenSize() {
            const button = document.querySelector('.btn-toggle-columns');
            if (window.innerWidth <= 768) {
                button.style.display = 'inline-block';
            } else {
                button.style.display = 'none';
            }
        }
        
        // Check saat load dan resize
        window.addEventListener('load', checkScreenSize);
        window.addEventListener('resize', checkScreenSize);
    </script>
</body>
</html>
<?php $conn->close(); ?> 