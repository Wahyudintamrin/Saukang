<?php
/**
 * MOTORPART DEV MENTOR - BACKEND LEARNING (ECU UTAMA)
 * File: src/Controllers/PartController.php
 * Deskripsi: Menangani logika bisnis CRUD (Create, Read, Update, Delete) 
 *            dan menjamin data tersimpan aman ke database MySQL.
 */

class PartController {
    // Variabel privat untuk menampung koneksi database PDO
    private $db;

    // Konstruktor: Menerima soket koneksi database dari file config_database.php
    public function __construct($pdo_connection) {
        $this->db = $pdo_connection;
    }

    /**
     * Menyimpan Sparepart Baru ke tabel utama beserta relasi kecocokan motornya (Many-to-Many)
     */
    public function createPart($name, $price, $stock, $brand, $category, $compatible_motor_ids) {
        try {
            // 1. MULAI TRANSAKSI (Transaction)
            // Analogi: Seperti merakit mesin silinder. Semua baut harus terpasang kencang (Commit). 
            // Jika ada satu baut yang slek di tengah jalan, kita bongkar ulang semuanya (Rollback) agar mesin tidak pincang.
            $this->db->beginTransaction();

            // 2. QUERY INPUT PRODUK UTAMA (Gunakan Prepared Statement agar ANTI SQL INJECTION!)
            $sqlProduct = "INSERT INTO tb_products (name, price, stock, brand, category) 
                           VALUES (:name, :price, :stock, :brand, :category)";
            
            $stmtProduct = $this->db->prepare($sqlProduct);
            
            // Eksekusi pengisian data ke tabel tb_products
            $stmtProduct->execute([
                ':name'     => $name,
                ':price'    => $price,
                ':stock'    => $stock,
                ':brand'    => $brand,
                ':category' => $category
            ]);

            // 3. AMBIL ID BARU (Auto Increment ID yang barusan terbuat)
            $new_product_id = $this->db->lastInsertId();

            // 4. JIKA ADA PILIHAN MOTOR YANG COCOK, SIMPAN KE TABEL JEMBATAN (tb_compatibility)
            if (!empty($compatible_motor_ids) && is_array($compatible_motor_ids)) {
                $sqlCompat = "INSERT INTO tb_compatibility (product_id, motor_id) VALUES (:pid, :mid)";
                $stmtCompat = $this->db->prepare($sqlCompat);

                // Lakukan looping (perulangan) untuk mendaftarkan setiap motor yang dicentang oleh admin
                foreach ($compatible_motor_ids as $motor_id) {
                    $stmtCompat->execute([
                        ':pid' => $new_product_id,
                        ':mid' => (int)$motor_id
                    ]);
                }
            }

            // 5. TRANSAKSI SUKSES! Kunci mati data di database secara permanen.
            $this->db->commit();
            return [
                "status"  => "success", 
                "message" => "Suku cadang baru '{$name}' berhasil masuk rak gudang dan terhubung dengan tipe motor!"
            ];

        } catch (Exception $e) {
            // Jika di tengah jalan ada baris koding yang eror, batalkan semua perubahan sebelum database rusak!
            $this->db->rollBack();
            return [
                "status"  => "error", 
                "message" => "Gagal merakit data ke database. Pesan Error: " . $e->getMessage()
            ];
        }
    }

    /**
     * Memperbarui detail suku cadang dan menyegarkan relasi kecocokan motornya
     */
    public function updatePart($product_id, $name, $price, $stock, $brand, $category, $compatible_motor_ids) {
        try {
            $this->db->beginTransaction();

            // 1. UPDATE DATA UTAMA BARANG
            $sqlUpdate = "UPDATE tb_products 
                          SET name = :name, price = :price, stock = :stock, brand = :brand, category = :category 
                          WHERE id = :id";
            
            $stmtUpdate = $this->db->prepare($sqlUpdate);
            $stmtUpdate->execute([
                ':id'       => $product_id,
                ':name'     => $name,
                ':price'    => $price,
                ':stock'    => $stock,
                ':brand'    => $brand,
                ':category' => $category
            ]);

            // 2. Kuras & Bersihkan oli lama (Hapus semua kecocokan lama untuk ID produk ini)
            $sqlDeleteCompat = "DELETE FROM tb_compatibility WHERE product_id = :id";
            $stmtDeleteCompat = $this->db->prepare($sqlDeleteCompat);
            $stmtDeleteCompat->execute([':id' => $product_id]);

            // 3. Masukkan kembali daftar kecocokan baru yang dipilih admin
            if (!empty($compatible_motor_ids) && is_array($compatible_motor_ids)) {
                $sqlCompat = "INSERT INTO tb_compatibility (product_id, motor_id) VALUES (:pid, :mid)";
                $stmtCompat = $this->db->prepare($sqlCompat);

                foreach ($compatible_motor_ids as $motor_id) {
                    $stmtCompat->execute([
                        ':pid' => $product_id,
                        ':mid' => (int)$motor_id
                    ]);
                }
            }

            $this->db->commit();
            return [
                "status"  => "success", 
                "message" => "Detail suku cadang ID #{$product_id} berhasil diperbarui di rak gudang!"
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                "status"  => "error", 
                "message" => "Gagal memperbarui data. Pesan Error: " . $e->getMessage()
            ];
        }
    }

    /**
     * Menghapus produk dari database (Relasi kecocokan di tb_compatibility otomatis terhapus karena CASCADE)
     */
    public function deletePart($product_id) {
        try {
            // Query untuk menghapus barang utama
            $sqlDelete = "DELETE FROM tb_products WHERE id = :id";
            $stmtDelete = $this->db->prepare($sqlDelete);
            $stmtDelete->execute([':id' => $product_id]);

            return [
                "status"  => "success",
                "message" => "Sparepart dengan ID #{$product_id} telah dihapus dari sistem gudang!"
            ];
        } catch (Exception $e) {
            return [
                "status"  => "error",
                "message" => "Gagal menghapus suku cadang. Pesan Error: " . $e->getMessage()
            ];
        }
    }
}