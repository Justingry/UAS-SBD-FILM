<?php
$host = "localhost";
$port = 3306;
$username = "root"; 
$password = "";     
$database = "UAS"; // Sesuaikan dengan nama database Anda

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi ke database gagal: " . $e->getMessage());
}
?>