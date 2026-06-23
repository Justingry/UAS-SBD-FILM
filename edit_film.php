<?php
// edit_film.php - FRONTEND
session_start();
require "koneksi.php";

// Ambil ID dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: film.php?error=ID film tidak valid");
    exit;
}

// Ambil data film berdasarkan ID
$query = "SELECT id_film, judul, harga_sewa, harga_beli FROM film WHERE id_film = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$film = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$film) {
    header("Location: film.php?error=Film tidak ditemukan");
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Film</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">
            <span>🎬</span> Bioskop Film Digital
        </div>
    </nav>

    <div class="container">
        <div class="card" style="max-width: 600px; margin: 40px auto;">
            <div class="card-header">
                <h1 class="card-title">Edit Film</h1>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] === 'success' ? 'sukses' : 'error'; ?>">
                    <?= $flash['message']; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="proses_edit_film.php">
                <!-- Kirim ID film sebagai hidden field -->
                <input type="hidden" name="id_film" value="<?= $film['id_film'] ?>">

                <div class="form-group">
                    <label for="judul">Judul Film <span class="required">*</span></label>
                    <input type="text" id="judul" name="judul" class="form-control" 
                           value="<?= htmlspecialchars($film['judul']) ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="harga_sewa">Harga Sewa (Rp) <span class="required">*</span></label>
                        <input type="text" id="harga_sewa" name="harga_sewa" class="form-control" 
                               value="<?= number_format($film['harga_sewa'], 0, ',', '.') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="harga_beli">Harga Beli (Rp) <span class="required">*</span></label>
                        <input type="text" id="harga_beli" name="harga_beli" class="form-control" 
                               value="<?= number_format($film['harga_beli'], 0, ',', '.') ?>" required>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-edit" style="padding: 12px 30px;">Simpan Perubahan</button>
                    <a href="index.php" class="btn btn-kembali">Kembali</a>
                </div>
            </form>
        </div>
    </div>

    <footer>
        &copy; 2026 Proyek UAS Sistem Basis Data
    </footer>
</body>
</html>