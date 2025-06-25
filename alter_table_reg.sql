-- Tambah kolom id_jenis_kelamin
ALTER TABLE reg ADD COLUMN id_jenis_kelamin INT AFTER jenis_kelamin;

-- Tambah foreign key
ALTER TABLE reg ADD CONSTRAINT fk_jenis_kelamin 
FOREIGN KEY (id_jenis_kelamin) REFERENCES ref_jenis_kelamin(id);

-- Update data yang sudah ada
UPDATE reg r 
JOIN ref_jenis_kelamin rjk ON UPPER(r.jenis_kelamin) = rjk.nama
SET r.id_jenis_kelamin = rjk.id;

-- Tambah kolom baru
ALTER TABLE reg
ADD COLUMN rm_npwp VARCHAR(32) NULL,
ADD COLUMN rm_tgl_masuk_kuliah DATE NULL,
ADD COLUMN rm_kode_pos VARCHAR(10) NULL,
ADD COLUMN rm_hp VARCHAR(20) NULL,
ADD COLUMN rm_terima_kps VARCHAR(5) NULL,
ADD COLUMN rm_no_kps VARCHAR(32) NULL,
ADD COLUMN prodi_kode VARCHAR(16) NULL,
ADD COLUMN rm_jenis_pembiayaan VARCHAR(32) NULL,
ADD COLUMN rm_biaya_masuk_kuliah DECIMAL(15,2) NULL,
ADD COLUMN rm_asal_perguruan_tinggi VARCHAR(128) NULL,
ADD COLUMN rm_asal_program_studi VARCHAR(128) NULL; 