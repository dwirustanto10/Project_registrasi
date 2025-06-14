CREATE TABLE ref_jenis_kelamin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode VARCHAR(1) NOT NULL,
    nama VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert data referensi
INSERT INTO ref_jenis_kelamin (kode, nama) VALUES 
('L', 'LAKI-LAKI'),
('P', 'PEREMPUAN'); 