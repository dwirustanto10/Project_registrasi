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
    agama.id AS 'ID Agama',
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
LEFT JOIN agama ON reg.rm_agama = agama.nama_agama
LEFT JOIN jalur_daftar ON reg.rm_jalur = jalur_daftar.jalur_masuk
LIMIT $rows_per_page OFFSET $offset";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Daftar Data Mahasiswa</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Daftar Data Mahasiswa</h2>
        
        <!-- Info Pagination -->
        <div class="pagination-info">
            <p>Menampilkan <?= ($offset + 1) ?> - <?= min($offset + $rows_per_page, $total_rows) ?> dari <?= $total_rows ?> data</p>
        </div>
        
        <!-- Tombol Download Excel -->
        <div style="text-align: center;">
            <a href="download_excel.php" class="download-btn">
                📥 Download Data Excel
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
                            <?php foreach(array_keys($result->fetch_assoc()) as $col): ?>
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