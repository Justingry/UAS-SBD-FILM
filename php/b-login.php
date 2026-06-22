<?php
// b-login.php — Proses autentikasi pengguna
session_start();

$pdo = require 'b-koneksi.php';

$email  = trim($_POST['email'] ?? '');
$pw_raw = $_POST['pw'] ?? '';

if (empty($email) || empty($pw_raw)) {
    header("Location: login.php?error=Email+dan+password+wajib+diisi");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM pengguna WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $data = $stmt->fetch();

    if ($data && password_verify($pw_raw, $data['pw'])) {
        // Login berhasil
        session_regenerate_id(true);
        $_SESSION['id_pengguna'] = $data['id_pengguna'];
        $_SESSION['nama']        = $data['nama'];
        $_SESSION['email']       = $data['email'];

        header("Location: film.php");
        exit;
    } else {
        header("Location: login.php?error=Email+atau+password+salah");
        exit;
    }
} catch (PDOException $e) {
    header("Location: login.php?error=" . urlencode("Gagal: " . $e->getMessage()));
    exit;
}
