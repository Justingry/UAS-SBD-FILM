<?php
// hapus_film.php
$conn = require "koneksi.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: film.php?error=ID tidak valid");
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
    header("Location: film.php?sukses=Data film berhasil dihapus");
} else {
    header("Location: film.php?error=Gagal menghapus film");
}
exit;