-- ============================================
-- DATABASE: Stock Gudang UKK
-- Created: 2024
-- ============================================

CREATE DATABASE IF NOT EXISTS stock_gudang CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE stock_gudang;

-- ============================================
-- TABLE: users
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    role ENUM('admin', 'gudang', 'viewer') NOT NULL DEFAULT 'gudang',
    foto VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- TABLE: kategori
-- ============================================
CREATE TABLE IF NOT EXISTS kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_kategori VARCHAR(20) NOT NULL UNIQUE,
    nama_kategori VARCHAR(100) NOT NULL,
    deskripsi TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- TABLE: supplier
-- ============================================
CREATE TABLE IF NOT EXISTS supplier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_supplier VARCHAR(20) NOT NULL UNIQUE,
    nama_supplier VARCHAR(150) NOT NULL,
    alamat TEXT DEFAULT NULL,
    telepon VARCHAR(20) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- TABLE: barang
-- ============================================
CREATE TABLE IF NOT EXISTS barang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_barang VARCHAR(30) NOT NULL UNIQUE,
    nama_barang VARCHAR(150) NOT NULL,
    id_kategori INT DEFAULT NULL,
    satuan VARCHAR(30) NOT NULL DEFAULT 'pcs',
    harga_beli DECIMAL(15,2) NOT NULL DEFAULT 0,
    harga_jual DECIMAL(15,2) NOT NULL DEFAULT 0,
    stok INT NOT NULL DEFAULT 0,
    stok_minimum INT NOT NULL DEFAULT 5,
    foto VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kategori) REFERENCES kategori(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- TABLE: barang_masuk
-- ============================================
CREATE TABLE IF NOT EXISTS barang_masuk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_transaksi VARCHAR(30) NOT NULL UNIQUE,
    id_supplier INT DEFAULT NULL,
    id_barang INT NOT NULL,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(15,2) NOT NULL DEFAULT 0,
    tanggal DATE NOT NULL,
    keterangan TEXT DEFAULT NULL,
    id_user INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_supplier) REFERENCES supplier(id) ON DELETE SET NULL,
    FOREIGN KEY (id_barang) REFERENCES barang(id) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- TABLE: barang_keluar
-- ============================================
CREATE TABLE IF NOT EXISTS barang_keluar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_transaksi VARCHAR(30) NOT NULL UNIQUE,
    id_barang INT NOT NULL,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(15,2) NOT NULL DEFAULT 0,
    tanggal DATE NOT NULL,
    tujuan VARCHAR(150) DEFAULT NULL,
    keterangan TEXT DEFAULT NULL,
    id_user INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_barang) REFERENCES barang(id) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- TABLE: stok_opname
-- ============================================
CREATE TABLE IF NOT EXISTS stok_opname (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_opname VARCHAR(30) NOT NULL UNIQUE,
    id_barang INT NOT NULL,
    stok_sistem INT NOT NULL DEFAULT 0,
    stok_fisik INT NOT NULL DEFAULT 0,
    selisih INT NOT NULL DEFAULT 0,
    keterangan TEXT DEFAULT NULL,
    tanggal DATE NOT NULL,
    id_user INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_barang) REFERENCES barang(id) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- SEED DATA
-- ============================================

-- Default users (password: password)
INSERT INTO users (username, password, nama, role) VALUES
('admin', '$2y$10$kfXEApYJdZcw0XQFWLWyDuyCNzQKAwyQXINujNeEDAw8iRkAhool2', 'Administrator', 'admin'),
('gudang', '$2y$10$kfXEApYJdZcw0XQFWLWyDuyCNzQKAwyQXINujNeEDAw8iRkAhool2', 'Petugas Gudang', 'gudang');

-- Kategori
INSERT INTO kategori (kode_kategori, nama_kategori, deskripsi) VALUES
('KAT001', 'Elektronik', 'Barang elektronik dan perangkat digital'),
('KAT002', 'Alat Tulis', 'Perlengkapan alat tulis kantor'),
('KAT003', 'Furniture', 'Perabot dan furniture kantor'),
('KAT004', 'Konsumsi', 'Barang konsumsi dan makanan'),
('KAT005', 'Kebersihan', 'Perlengkapan kebersihan');

-- Supplier
INSERT INTO supplier (kode_supplier, nama_supplier, alamat, telepon, email) VALUES
('SUP001', 'PT. Maju Bersama', 'Jl. Raya Sudirman No. 10, Jakarta', '021-5551234', 'info@majubersama.co.id'),
('SUP002', 'CV. Sejahtera Jaya', 'Jl. Ahmad Yani No. 25, Surabaya', '031-7891234', 'supply@sejahterajaya.com'),
('SUP003', 'UD. Berkah Abadi', 'Jl. Diponegoro No. 5, Bandung', '022-4567890', 'berkah@abadi.net'),
('SUP004', 'PT. Nusantara Supplier', 'Jl. Gatot Subroto No. 88, Jakarta', '021-9876543', 'order@nusantara.co.id');

-- Barang
INSERT INTO barang (kode_barang, nama_barang, id_kategori, satuan, harga_beli, harga_jual, stok, stok_minimum) VALUES
('BRG001', 'Laptop ASUS VivoBook', 1, 'unit', 5500000, 7000000, 15, 3),
('BRG002', 'Mouse Wireless Logitech', 1, 'pcs', 150000, 220000, 50, 10),
('BRG003', 'Keyboard Mechanical', 1, 'pcs', 350000, 500000, 30, 5),
('BRG004', 'Kertas A4 80gsm', 2, 'rim', 45000, 65000, 100, 20),
('BRG005', 'Pulpen Pilot G2', 2, 'lusin', 36000, 55000, 80, 15),
('BRG006', 'Kursi Kantor Ergonomis', 3, 'unit', 850000, 1200000, 10, 2),
('BRG007', 'Meja Kerja L-Shape', 3, 'unit', 1200000, 1800000, 5, 2),
('BRG008', 'Teh Kotak 1L', 4, 'karton', 85000, 120000, 40, 10),
('BRG009', 'Sabun Cuci Tangan', 5, 'botol', 25000, 38000, 60, 10),
('BRG010', 'Cairan Pembersih Lantai', 5, 'galon', 35000, 55000, 3, 5);

-- Barang Masuk (sample data)
INSERT INTO barang_masuk (no_transaksi, id_supplier, id_barang, jumlah, harga_satuan, tanggal, keterangan, id_user) VALUES
('BM-2024-001', 1, 1, 5, 5500000, '2024-01-05', 'Pembelian laptop batch pertama', 1),
('BM-2024-002', 2, 4, 50, 45000, '2024-01-10', 'Restock kertas bulanan', 1),
('BM-2024-003', 1, 2, 20, 150000, '2024-01-15', 'Pembelian mouse wireless', 1),
('BM-2024-004', 3, 9, 30, 25000, '2024-02-03', 'Restock kebersihan', 2),
('BM-2024-005', 2, 5, 40, 36000, '2024-02-10', 'Restock alat tulis', 2),
('BM-2024-006', 4, 6, 5, 850000, '2024-02-20', 'Pengadaan kursi baru', 1),
('BM-2024-007', 1, 3, 15, 350000, '2024-03-05', 'Pembelian keyboard', 1),
('BM-2024-008', 3, 8, 20, 85000, '2024-03-12', 'Restock minuman', 2),
('BM-2024-009', 2, 10, 10, 35000, '2024-03-20', 'Restock cairan pembersih', 2),
('BM-2024-010', 4, 7, 3, 1200000, '2024-04-05', 'Pengadaan meja', 1);

-- Barang Keluar (sample data)
INSERT INTO barang_keluar (no_transaksi, id_barang, jumlah, harga_satuan, tanggal, tujuan, keterangan, id_user) VALUES
('BK-2024-001', 1, 2, 7000000, '2024-01-20', 'Divisi IT', 'Untuk kebutuhan tim IT', 1),
('BK-2024-002', 4, 10, 65000, '2024-01-25', 'Divisi HRD', 'Kebutuhan administrasi', 1),
('BK-2024-003', 2, 5, 220000, '2024-02-05', 'Divisi Marketing', 'Kebutuhan tim marketing', 2),
('BK-2024-004', 9, 10, 38000, '2024-02-15', 'Seluruh Divisi', 'Kebutuhan kebersihan', 2),
('BK-2024-005', 5, 15, 55000, '2024-02-28', 'Divisi Keuangan', 'Restock ATK keuangan', 1),
('BK-2024-006', 6, 2, 1200000, '2024-03-10', 'Ruang Meeting', 'Kursi ruang meeting baru', 1),
('BK-2024-007', 8, 8, 120000, '2024-03-18', 'Kantin', 'Kebutuhan minuman kantin', 2),
('BK-2024-008', 3, 5, 500000, '2024-04-02', 'Divisi IT', 'Keyboard untuk programmer', 1),
('BK-2024-009', 10, 7, 55000, '2024-04-10', 'Divisi Umum', 'Kebersihan lantai', 2),
('BK-2024-010', 4, 5, 65000, '2024-04-20', 'Divisi Operasional', 'Kebutuhan operasional', 1);

-- Stok Opname (sample)
INSERT INTO stok_opname (no_opname, id_barang, stok_sistem, stok_fisik, selisih, keterangan, tanggal, id_user) VALUES
('OPN-2024-001', 10, 3, 3, 0, 'Sesuai', '2024-03-31', 1),
('OPN-2024-002', 9, 60, 58, -2, 'Selisih 2 botol, kemungkinan rusak', '2024-03-31', 1),
('OPN-2024-003', 4, 100, 102, 2, 'Lebih 2 rim dari pencatatan', '2024-03-31', 1);
