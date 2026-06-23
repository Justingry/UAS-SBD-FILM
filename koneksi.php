<?php
// Konfigurasi Database
$host = 'localhost';
$port = 3306;
$username = 'root';
$password = '';
$database = 'uas';

// Membuat Koneksi
$conn = mysqli_connect($host, $username, $password, $database, $port);

// Memeriksa Koneksi
if (!$conn) {
    die('Koneksi ke database gagal: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');

return $conn;
