-- Database SMK IGASAR PINDAD Bandung
CREATE DATABASE ppdb_smk_igasar;
USE ppdb_smk_igasar;

-- Drop existing tables if they exist
DROP TABLE IF EXISTS monitoring_penerimaan;
DROP TABLE IF EXISTS laporan_penerimaan;
DROP TABLE IF EXISTS pendaftar;
DROP TABLE IF EXISTS biaya_tahunan;
DROP TABLE IF EXISTS jurusan;
DROP TABLE IF EXISTS petugas;
DROP TABLE IF EXISTS login;
DROP TABLE IF EXISTS registrasi;

-- Create tables
CREATE TABLE registrasi (
    id_registrasi INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    email VARCHAR(100) UNIQUE,
    role ENUM('pendaftar', 'petugas', 'admin', 'tu', 'kepala_sekolah') DEFAULT 'pendaftar',
    status_aktif BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE login (
    id_login INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    password VARCHAR(255),
    status_login BOOLEAN DEFAULT FALSE,
    last_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE petugas (
    kd_petugas VARCHAR(10) PRIMARY KEY,
    nama_petugas VARCHAR(100),
    role ENUM('petugas', 'admin', 'tu', 'kepala_sekolah'),
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES registrasi(id_registrasi)
);

CREATE TABLE jurusan (
    kode_jur VARCHAR(10) PRIMARY KEY,
    nama_jurusan VARCHAR(100),
    kuota INT DEFAULT 30,
    status VARCHAR(20) DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pendaftar (
    no_daftar VARCHAR(20) PRIMARY KEY,
    nama VARCHAR(100),
    alamat TEXT,
    jk CHAR(1),
    kode_jur VARCHAR(10),
    kd_petugas VARCHAR(10),
    tahun_ajaran VARCHAR(10),
    nisn VARCHAR(20),
    tempat_lahir VARCHAR(100),
    tanggal_lahir DATE,
    nama_ayah VARCHAR(100),
    nama_ibu VARCHAR(100),
    no_hp VARCHAR(20),
    asal_sekolah VARCHAR(100),
    nilai_rata DECIMAL(5,2),
    status_pendaftaran ENUM('pending', 'diterima', 'ditolak') DEFAULT 'pending',
    user_id INT,
    tanggal_daftar TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kode_jur) REFERENCES jurusan(kode_jur),
    FOREIGN KEY (kd_petugas) REFERENCES petugas(kd_petugas),
    FOREIGN KEY (user_id) REFERENCES registrasi(id_registrasi)
);

CREATE TABLE biaya_tahunan (
    tahun_ajaran VARCHAR(10) PRIMARY KEY,
    b_pendaftaran INT,
    b_awal_tahun INT,
    b_seragam INT,
    b_spp INT,
    status VARCHAR(20) DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE laporan_penerimaan (
    id_laporan INT AUTO_INCREMENT PRIMARY KEY,
    tanggal_cetak DATE,
    tahun_ajaran VARCHAR(10),
    jumlah_pendaftar INT,
    total_biaya FLOAT,
    created_by VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE monitoring_penerimaan (
    id_monitoring INT AUTO_INCREMENT PRIMARY KEY,
    tahun_ajaran VARCHAR(10) UNIQUE,
    jumlah_pendaftar INT,
    jumlah_per_jurusan JSON,
    total_biaya_pendaftaran FLOAT,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO registrasi (username, password, email, role) VALUES 
('admin', MD5('admin123'), 'admin@smkigasar.sch.id', 'admin'),
('petugas1', MD5('petugas123'), 'asep@smkigasar.sch.id', 'petugas'),
('petugas2', MD5('petugas123'), 'susanti@smkigasar.sch.id', 'petugas'),
('tu1', MD5('tu123'), 'tu@smkigasar.sch.id', 'tu'),
('kepsek', MD5('kepsek123'), 'kepsek@smkigasar.sch.id', 'kepala_sekolah'),
('siswa1', MD5('siswa123'), 'agus@email.com', 'pendaftar'),
('siswa2', MD5('siswa123'), 'bendy@email.com', 'pendaftar');

INSERT INTO petugas (kd_petugas, nama_petugas, role, user_id) VALUES 
('P001', 'Asep Sunandar', 'petugas', 2),
('P002', 'Susanti', 'petugas', 3),
('P003', 'Ali Syakip', 'admin', 1),
('P004', 'Sania Marwah', 'tu', 4),
('P005', 'Aria Kamandanu', 'kepala_sekolah', 5);

INSERT INTO jurusan (kode_jur, nama_jurusan, kuota) VALUES 
('IGAPIN_1', 'Teknik Komputer Jaringan', 36),
('IGAPIN_2', 'Rekayasa Perangkat Lunak', 36),
('IGAPIN_3', 'Teknik Pemesinan', 30),
('IGAPIN_4', 'Teknik Kendaraan Ringan', 30),
('IGAPIN_5', 'Teknik Bisnis Sepeda Motor', 30);

INSERT INTO biaya_tahunan (tahun_ajaran, b_pendaftaran, b_awal_tahun, b_seragam, b_spp) VALUES 
('2024/2025', 100000, 950000, 750000, 290000),
('2025/2026', 100000, 1000000, 850000, 300000),
('2026/2027', 150000, 1050000, 900000, 300000);

-- Sample pendaftar data
INSERT INTO pendaftar (no_daftar, nama, alamat, jk, kode_jur, kd_petugas, tahun_ajaran, nisn, tempat_lahir, tanggal_lahir, nama_ayah, nama_ibu, no_hp, asal_sekolah, nilai_rata, user_id) VALUES 
('1112001', 'Agus Rohimat', 'Jl. Bogor 123', 'L', 'IGAPIN_1', 'P001', '2024/2025', '0123456789', 'Bandung', '2008-05-15', 'Bapak Agus', 'Ibu Rohimat', '081234567890', 'SMP Negeri 1 Bandung', 85.5, 6),
('1112002', 'Agus Rahmat', 'Jl. Cikajang', 'L', 'IGAPIN_3', 'P002', '2024/2025', '0123456788', 'Bandung', '2008-03-20', 'Bapak Rahmat', 'Ibu Agus', '081234567891', 'SMP Negeri 2 Bandung', 82.0, NULL),
('1112003', 'Bendy', 'Jl. Surabaya', 'P', 'IGAPIN_1', 'P001', '2024/2025', '0123456787', 'Bandung', '2008-07-10', 'Bapak Bendy', 'Ibu Bendy', '081234567892', 'SMP Negeri 3 Bandung', 88.0, 7);
