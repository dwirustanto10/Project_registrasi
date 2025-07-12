<?php
session_start();
require_once 'koneksi.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'hapus_semua') {
        // Hapus semua data dari tabel reg
        $sql = "DELETE FROM reg";
        $result = $conn->query($sql);
        
        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Semua data berhasil dihapus dari database.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Gagal menghapus data: ' . $conn->error
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Aksi tidak valid.'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Metode request tidak valid.'
    ]);
}

$conn->close();
?> 