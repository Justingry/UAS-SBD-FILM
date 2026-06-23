<?php
// proses_tambah_film.php - BACKEND
session_start();
require "koneksi.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: tambah_film.php");
    exit;
}

// Ambil input
$judul = trim($_POST['judul'] ?? '');
$tahun = (int)($_POST['tahun_rilis'] ?? 0);
$durasi = (int)($_POST['durasi_menit'] ?? 0);
$harga_beli = (float)str_replace(',', '', $_POST['harga_beli'] ?? 0);
$harga_sewa = (float)str_replace(',', '', $_POST['harga_sewa'] ?? 0);
$id_kategori = (int)($_POST['id_kategori'] ?? 0);
$id_rating = (int)($_POST['id_rating'] ?? 0);

// Studio: ambil nilai asli (bisa 'new' atau angka)
$id_studio_raw = $_POST['id_studio'] ?? '';
$studio_baru = trim($_POST['studio_baru'] ?? '');

if ($id_studio_raw === 'new') {
    // Pilih opsi tambah baru
    if (empty($studio_baru)) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Silakan isi nama studio baru.'];
        header("Location: tambah_film.php");
        exit;
    }
    // Insert studio baru
    $sql = "INSERT INTO studio (nama_studio) VALUES (?)";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal prepare query studio: ' . mysqli_error($conn)];
        header("Location: tambah_film.php");
        exit;
    }
    mysqli_stmt_bind_param($stmt, 's', $studio_baru);
    if (mysqli_stmt_execute($stmt)) {
        $id_studio = mysqli_insert_id($conn);
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menambah studio: ' . mysqli_error($conn)];
        header("Location: tambah_film.php");
        exit;
    }
    mysqli_stmt_close($stmt);
} elseif (is_numeric($id_studio_raw) && (int)$id_studio_raw > 0) {
    $id_studio = (int)$id_studio_raw;
} else {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Studio harus dipilih atau diisi baru.'];
    header("Location: tambah_film.php");
    exit;
}

// Sutradara
$id_sutradara_raw = $_POST['id_sutradara'] ?? '';
$sutradara_baru = trim($_POST['sutradara_baru'] ?? '');

if ($id_sutradara_raw === 'new') {
    if (empty($sutradara_baru)) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Silakan isi nama sutradara baru.'];
        header("Location: tambah_film.php");
        exit;
    }
    $sql = "INSERT INTO sutradara (nama_sutradara) VALUES (?)";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal prepare query sutradara: ' . mysqli_error($conn)];
        header("Location: tambah_film.php");
        exit;
    }
    mysqli_stmt_bind_param($stmt, 's', $sutradara_baru);
    if (mysqli_stmt_execute($stmt)) {
        $id_sutradara = mysqli_insert_id($conn);
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menambah sutradara: ' . mysqli_error($conn)];
        header("Location: tambah_film.php");
        exit;
    }
    mysqli_stmt_close($stmt);
} elseif (is_numeric($id_sutradara_raw) && (int)$id_sutradara_raw > 0) {
    $id_sutradara = (int)$id_sutradara_raw;
} else {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Sutradara harus dipilih atau diisi baru.'];
    header("Location: tambah_film.php");
    exit;
}

// Validasi input lainnya
$errors = [];
if (empty($judul)) $errors[] = "Judul film wajib diisi.";
if ($tahun <= 0 || $tahun > date('Y')) $errors[] = "Tahun rilis tidak valid.";
if ($durasi <= 0) $errors[] = "Durasi harus lebih dari 0 menit.";
if ($harga_beli < 0) $errors[] = "Harga beli tidak valid.";
if ($harga_sewa < 0) $errors[] = "Harga sewa tidak valid.";
if ($id_kategori <= 0) $errors[] = "Kategori wajib dipilih.";
if ($id_rating <= 0) $errors[] = "Rating usia wajib dipilih.";

if (!empty($errors)) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => implode('<br>', $errors)];
    header("Location: tambah_film.php");
    exit;
}

// Insert film
$sql = "INSERT INTO film (judul, tahun_rilis, durasi_menit, harga_beli, harga_sewa, 
        id_kategori, id_rating, id_studio, id_sutradara) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal prepare query film: ' . mysqli_error($conn)];
    header("Location: tambah_film.php");
    exit;
}

mysqli_stmt_bind_param($stmt, 'siiiisiii', 
    $judul, $tahun, $durasi, $harga_beli, $harga_sewa,
    $id_kategori, $id_rating, $id_studio, $id_sutradara
);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['flash'] = ['type' => 'success', 'message' => '🎉 Film berhasil ditambahkan!'];
    header("Location: index.php");
} else {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menyimpan film: ' . mysqli_error($conn)];
    header("Location: tambah_film.php");
}
mysqli_stmt_close($stmt);
exit;