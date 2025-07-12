<?php
session_start();
require_once 'koneksi.php';

// Proses hapus semua data jika ada konfirmasi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['hapus_semua'])) {
    // Hapus semua data dari tabel reg
    $sql = "DELETE FROM reg";
    $result = $conn->query($sql);
    
    if ($result) {
        $message = "Semua data berhasil dihapus dari database.";
        $message_type = "success";
    } else {
        $message = "Gagal menghapus data: " . $conn->error;
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hapus Data - Sistem Registrasi Mahasiswa</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
        .warning-box h3 {
            color: #d63031;
            margin-top: 0;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin: 10px;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin: 10px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .message {
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
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
        .confirmation-form {
            text-align: center;
            margin: 30px 0;
        }
        .info-box {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            color: #1565c0;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗑️ Hapus Data Mahasiswa</h1>
        
        <!-- Pesan hasil operasi -->
        <?php if (isset($message)): ?>
            <div class="message <?= $message_type ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <!-- Informasi jumlah data -->
        <?php
        $count_sql = "SELECT COUNT(*) as total FROM reg";
        $count_result = $conn->query($count_sql);
        $total_data = $count_result->fetch_assoc()['total'];
        ?>
        
        <div class="info-box">
            <h3>📊 Informasi Data</h3>
            <p>Jumlah data mahasiswa saat ini: <strong><?= number_format($total_data) ?></strong> record</p>
        </div>
        
        <?php if ($total_data > 0): ?>
            <!-- Peringatan -->
            <div class="warning-box">
                <h3>⚠️ PERINGATAN!</h3>
                <p><strong>Tindakan ini akan menghapus SEMUA data mahasiswa dari database secara permanen.</strong></p>
                <p>Data yang sudah dihapus tidak dapat dikembalikan (irreversible).</p>
                <p>Pastikan Anda telah melakukan backup data sebelum melanjutkan.</p>
            </div>
            
            <!-- Form konfirmasi -->
            <div class="confirmation-form">
                <form method="post" onsubmit="return confirmHapusSemua()">
                    <input type="hidden" name="hapus_semua" value="1">
                    <button type="submit" class="btn-danger">
                        🗑️ HAPUS SEMUA DATA (<?= number_format($total_data) ?> record)
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="info-box">
                <p>Database sudah kosong. Tidak ada data yang dapat dihapus.</p>
            </div>
        <?php endif; ?>
        
        <!-- Tombol kembali -->
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="btn-secondary">← Kembali ke Menu Utama</a>
            <a href="tampil_data.php" class="btn-secondary">📋 Lihat Data</a>
        </div>
    </div>
    
    <script>
        function confirmHapusSemua() {
            const totalData = <?= $total_data ?>;
            const message = `PERINGATAN!\n\nAnda akan menghapus SEMUA data (${totalData.toLocaleString()} record) dari database.\n\nTindakan ini TIDAK DAPAT DIBATALKAN!\n\nApakah Anda yakin ingin melanjutkan?`;
            
            return confirm(message);
        }
    </script>
</body>
</html>

<?php $conn->close(); ?> 