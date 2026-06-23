<?php
session_start();
require "koneksi.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'ID tidak valid'];
    header("Location: index.php");
    exit;
}

$id_film = (int)$_GET['id'];

// Nonaktifkan foreign key checks agar DELETE berjalan lancar
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");

// Hapus data dari tabel terkait
$tables = [
    'watchlist',
    'ulasan',
    'pustaka_film',
    'sewa_film',
    'detail_transaksi',
    'film_aktor',
    'film_kualitas'
];

foreach ($tables as $table) {
    $sql = "DELETE FROM $table WHERE id_film = $id_film";
    mysqli_query($conn, $sql);
}

// Hapus film utama
$sql = "DELETE FROM film WHERE id_film = $id_film";
$result = mysqli_query($conn, $sql);

// Aktifkan kembali foreign key checks
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");

if ($result && mysqli_affected_rows($conn) > 0) {
    $_SESSION['flash'] = ['type' => 'success', 'message' => '✅ Data film berhasil dihapus'];
} else {
    $_SESSION['flash'] = ['type' => 'error', 'message' => '❌ Gagal menghapus film'];
}

header("Location: index.php");
exit;