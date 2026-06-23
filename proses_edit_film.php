<?php
// proses_edit_film.php - BACKEND
session_start();
require "koneksi.php";

// Hanya menerima metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: film.php");
    exit;
}

// Ambil data dari form
$id = (int)($_POST['id_film'] ?? 0);
$judul = trim($_POST['judul'] ?? '');
$harga_sewa = (float)str_replace('.', '', $_POST['harga_sewa'] ?? 0); // hilangkan titik ribuan
$harga_beli = (float)str_replace('.', '', $_POST['harga_beli'] ?? 0);

// Validasi
$errors = [];
if ($id <= 0) $errors[] = "ID film tidak valid.";
if (empty($judul)) $errors[] = "Judul film wajib diisi.";
if ($harga_sewa < 0) $errors[] = "Harga sewa tidak boleh negatif.";
if ($harga_beli < 0) $errors[] = "Harga beli tidak boleh negatif.";

if (!empty($errors)) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => implode('<br>', $errors)
    ];
    header("Location: edit_film.php?id=$id");
    exit;
}

// Update database
$sql = "UPDATE film SET judul = ?, harga_sewa = ?, harga_beli = ? WHERE id_film = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'sddi', $judul, $harga_sewa, $harga_beli, $id);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['flash'] = [
        'type' => 'success',
        'message' => '✅ Data film berhasil diperbarui!'
    ];
} else {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => '❌ Gagal memperbarui: ' . mysqli_error($conn)
    ];
}
mysqli_stmt_close($stmt);

// Kembali ke halaman edit
header("Location: edit_film.php?id=$id");
exit;