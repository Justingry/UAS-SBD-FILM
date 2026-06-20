<?php
session_start(); // Memulai session untuk cek login

// Koneksi database
$conn = require 'b-koneksi.php'; // Pastikan file ini mengembalikan $pdo

// Cek apakah user sudah login, ambil nama dari session
$user_nama = $_SESSION['nama'] ?? null; // Misal saat login kita simpan 'nama' di session

try {
    $query = "SELECT f.id_film, f.judul, f.harga_sewa, f.harga_beli, r.kode_rating 
              FROM film f
              LEFT JOIN rating_usia r ON f.id_rating = r.id_rating 
              ORDER BY f.id_film";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $daftar_film = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Gagal mengambil data film: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manajemen Data Film - Streaming App</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>

        <nav class="navbar">
            <div class="navbar-brand">
                <span>🎬</span> Bioskop Film Digital
            </div>
            <ul class="navbar-nav">
                <li><a href="kategori.php">Kategori Film</a></li>
                <li><a href="film.php" class="active">Data Film</a></li>
                <li><a href="transaksi.php">Transaksi</a></li>
            </ul>
            <div class="navbar-user">
                <?php if ($user_nama): ?>
                    <!-- Jika sudah login, tampilkan nama depan dan link logout -->
                    <span class="user-greeting">Halo, <?= htmlspecialchars(explode(' ', $user_nama)[0]); ?></span>
                    <a href="logout.php" class="btn-logout">Logout</a>
                <?php else: ?>
                    <!-- Jika belum login, tampilkan tombol login -->
                    <a href="login.php" class="btn-login">Login</a>
                <?php endif; ?>
            </div>
        </nav>

        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h1 class="card-title">Daftar Koleksi Film</h1>
                    <a href="tambah_film.php" class="btn btn-success">+ Tambah Film Baru</a>
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 5%;">NO</th>
                                <th style="width: 30%;">JUDUL FILM</th>
                                <th style="width: 10%;">RATING</th>
                                <th style="width: 15%;">HARGA SEWA</th>
                                <th style="width: 15%;">HARGA BELI</th>
                                <th style="width: 10%;">STOK</th>
                                <th style="width: 15%;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($daftar_film)): ?>
                                <?php $no = 1; ?> 
                                <?php foreach ($daftar_film as $film): ?>
                                    <tr>
                                        <td><?= $no++; ?></td> 
                                        <td class="text-bold"><?= htmlspecialchars($film['judul']); ?></td>
                                        <td>
                                            <span class="badge badge-info">
                                                <?= htmlspecialchars($film['kode_rating'] ?? 'SU'); ?>
                                            </span>
                                        </td>
                                        <td class="text-price">Rp <?= number_format($film['harga_sewa'], 0, ',', '.'); ?></td>
                                        <td class="text-price">Rp <?= number_format($film['harga_beli'], 0, ',', '.'); ?></td>
                                        <td>
                                            <span class="badge badge-info" style="background-color: #e2fbe8; color: #27ae60; border: 1px solid #cbf7d5;">Tersedia</span>
                                        </td>
                                        <td>
                                            <div class="action-group">
                                                <a href="proses_transaksi.php?id=<?= $film['id_film']; ?>" class="btn-action-primary">Sewa/Beli</a>
                                                <a href="edit_film.php?id=<?= $film['id_film']; ?>" class="btn-edit" style="padding: 5px 10px; font-size: 12px;">Edit</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: #95a5a6; padding: 30px;">
                                        Belum ada data film di database.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <footer>
            &copy; 2026 Proyek UAS Sistem Basis Data
        </footer>

    </body>
</html>