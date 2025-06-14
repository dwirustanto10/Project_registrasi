<?php
require_once 'koneksi.php';

// Query untuk mendapatkan struktur tabel
$sql = "DESCRIBE reg";
$result = $conn->query($sql);

if ($result) {
    echo "<h3>Struktur Tabel 'reg':</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Hitung jumlah kolom
    $result->data_seek(0);
    $column_count = $result->num_rows;
    echo "<br>Total kolom dalam tabel: " . $column_count;
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?> 