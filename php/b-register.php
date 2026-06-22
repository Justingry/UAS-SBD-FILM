<?php
// b-register.php — Proses pendaftaran pengguna baru
session_start();

$pdo = require 'b-koneksi.php';

// Validasi input
$nama     = trim($_POST['nama']  ?? '');
$email    = trim($_POST['email'] ?? '');
$pw_raw   = $_POST['pw'] ?? '';

if (empty($nama) || empty($email) || empty($pw_raw)) {
    header("Location: register.php?error=Semua+field+wajib+diisi");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: register.php?error=Format+email+tidak+valid");
    exit;
}

$password = password_hash($pw_raw, PASSWORD_DEFAULT);

try {
    // Cek email sudah terdaftar
    $cek = $pdo->prepare("SELECT id_pengguna FROM pengguna WHERE email = ?");
    $cek->execute([$email]);
    if ($cek->fetch()) {
        header("Location: register.php?error=Email+sudah+terdaftar");
        exit;
    }

    $stmt = $pdo->prepare(
        "INSERT INTO pengguna (nama, email, pw) VALUES (?, ?, ?)"
    );
    $stmt->execute([$nama, $email, $password]);

    // Simpan ke session lalu redirect
    $id = $pdo->lastInsertId();
    $_SESSION['id_pengguna'] = $id;
    $_SESSION['nama']        = $nama;
    $_SESSION['email']       = $email;

    header("Location: film.php?msg=register_sukses");
    exit;

} catch (PDOException $e) {
    header("Location: register.php?error=" . urlencode("Gagal: " . $e->getMessage()));
    exit;
}
