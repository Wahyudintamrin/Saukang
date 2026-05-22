<?php
/**
 * MOTORPART DEV MENTOR - DATABASE CONNECTION
 * File: config/database.php
 * Deskripsi: Menghubungkan koding PHP ke MySQL Database menggunakan PDO (aman dari SQL Injection)
 */

$host     = "localhost";      // Alamat server database lokal XAMPP
$db_name  = "db_motorpart";   // Nama database yang kita buat di phpMyAdmin
$username = "root";           // Username default XAMPP
$password = "";               // Password default XAMPP (kosong)

try {
    // Membuat koneksi PDO (PHP Data Objects)
    // Analogi: Seperti menancapkan kabel soket utama ke ECU motor agar kelistrikan terhubung
    $pdo_connection = new PDO("mysql:host=" . $host . ";dbname=" . $db_name, $username, $password);
    
    // Set agar PDO menampilkan error jika ada kueri SQL yang rusak/salah ketik
    $pdo_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode ke associative array (agar data dipanggil dengan nama kolom database)
    $pdo_connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $exception) {
    // Jika koneksi gagal (misal XAMPP belum dinyalakan), matikan program dan tampilkan pesan error
    die("Koneksi ke database gagal! Pastikan MySQL di XAMPP sudah aktif. Pesan Error: " . $exception->getMessage());
}