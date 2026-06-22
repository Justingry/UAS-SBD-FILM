<?php
// b-koneksi.php — Koneksi PDO ke database UAS
$host     = "localhost";
$port     = 3306;
$username = "root";
$password = "";
$database = "UAS";

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi ke database gagal: " . $e->getMessage());
}

return $pdo; // wajib agar require 'b-koneksi.php' menghasilkan $pdo
