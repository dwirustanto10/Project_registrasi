<?php
require_once 'koneksi.php';

// Query hanya kolom tertentu sesuai permintaan
$sql = "SELECT 
    rm_nim AS 'NIM',
    rm_nama AS 'NAMA',
    rm_tmp_lahir AS 'Tempat Lahir',
    rm_tgl_lahir AS 'Tanggal Lahir',
    jenis_kelamin AS 'Jenis Kelamin',
    rm_nik AS 'NIK',
    rm_agama AS 'Agama',
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
FROM reg";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Daftar Data Mahasiswa</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background: #f2f2f2; }
        tr:nth-child(even) { background: #fafafa; }
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { margin-bottom: 20px; }
    </style>
</head>
<body>
    <h2>Daftar Data Mahasiswa</h2>
    <table>
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
                        <td><?= htmlspecialchars($cell ?? '') ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endwhile; } else { ?>
                <tr><td colspan="100">Tidak ada data.</td></tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>
<?php $conn->close(); ?> 