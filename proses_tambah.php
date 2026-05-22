-- --------------------------------------------------------
-- MOTORPART DEV MENTOR - DATABASE SETUP (CETAK BIRU GUDANG)
-- File: database_setup.sql
-- Deskripsi: Membuat tabel produk, tipe motor, dan tabel jembatan kompatibilitas
-- --------------------------------------------------------

-- 1. Buat Tabel Produk (tb_products) untuk menyimpan info sparepart
CREATE TABLE IF NOT EXISTS `tb_products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `price` DECIMAL(12, 2) NOT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `brand` VARCHAR(50) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Buat Tabel Tipe Motor (tb_motor_models) untuk daftar motor di Indonesia
CREATE TABLE IF NOT EXISTS `tb_motor_models` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `brand` VARCHAR(50) NOT NULL, -- Contoh: Honda, Yamaha, Suzuki, dll
  `model_name` VARCHAR(100) NOT NULL -- Contoh: Beat, NMAX, Vario, dll
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Buat Tabel Jembatan Kompatibilitas (tb_compatibility) - Relasi Many-to-Many
-- Jika produk dihapus, otomatis catatan kecocokannya ikut bersih terhapus karena 'ON DELETE CASCADE'
CREATE TABLE IF NOT EXISTS `tb_compatibility` (
  `product_id` INT NOT NULL,
  `motor_id` INT NOT NULL,
  PRIMARY KEY (`product_id`, `motor_id`),
  FOREIGN KEY (`product_id`) REFERENCES `tb_products` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`motor_id`) REFERENCES `tb_motor_models` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Masukkan Data Contoh untuk Tipe Motor (Master Data) agar admin tinggal memilih tipe ini
INSERT INTO `tb_motor_models` (`id`, `brand`, `model_name`) VALUES
(1, 'Honda', 'Beat'),
(2, 'Honda', 'Vario 150'),
(3, 'Honda', 'PCX 160'),
(4, 'Yamaha', 'NMAX'),
(5, 'Yamaha', 'Aerox'),
(6, 'Yamaha', 'Mio M3'),
(7, 'Suzuki', 'Satria F150'),
(8, 'Suzuki', 'Address'),
(9, 'Kawasaki', 'KLX 150'),
(10, 'Kawasaki', 'Ninja ZX-25R')
ON DUPLICATE KEY UPDATE `model_name`=VALUES(`model_name`);