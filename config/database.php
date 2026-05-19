<?php
$host = 'localhost';
$dbname = 'kelompok_5';
$username = 'root';  // Ganti jika beda
$password = '';      // Ganti jika ada password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>