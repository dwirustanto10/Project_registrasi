-- Tambah kolom id_jenis_kelamin
ALTER TABLE reg ADD COLUMN id_jenis_kelamin INT AFTER jenis_kelamin;

-- Tambah foreign key
ALTER TABLE reg ADD CONSTRAINT fk_jenis_kelamin 
FOREIGN KEY (id_jenis_kelamin) REFERENCES ref_jenis_kelamin(id);

-- Update data yang sudah ada
UPDATE reg r 
JOIN ref_jenis_kelamin rjk ON UPPER(r.jenis_kelamin) = rjk.nama
SET r.id_jenis_kelamin = rjk.id; 