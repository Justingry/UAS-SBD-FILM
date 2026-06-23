<?php

session_start();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$conn = require "koneksi.php";

$query = "
    SELECT film.*, rating_usia.kode_rating 
    FROM film 
    LEFT JOIN rating_usia ON film.id_rating = rating_usia.id_rating 
    ORDER BY film.id_film
";
$result = mysqli_query($conn, $query);
$daftar_film = mysqli_fetch_all($result, MYSQLI_ASSOC);

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
    </nav>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Daftar Koleksi Film</h1>
                <a href="tambah_film.php" class="btn btn-tambah">+ Tambah Film Baru</a>
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
                                            <?= htmlspecialchars($film['kode_rating'] ?? 'Tidak tersedia'); ?>
                                        </span>
                                    </td>
                                    <td class="text-price">Rp <?= number_format($film['harga_sewa'], 0, ',', '.'); ?></td>
                                    <td class="text-price">Rp <?= number_format($film['harga_beli'], 0, ',', '.'); ?></td>
                                    <td>
                                        <div class="td-aksi">
                                            <a href="edit_film.php?id=<?= $film['id_film']; ?>" class="btn btn-edit">Edit</a>
                                            <a href="hapus_film.php?id=<?= $film['id_film']; ?>" 
                                               class="btn btn-hapus" 
                                               onclick="return confirm('Yakin ingin menghapus film ini?')">Hapus</a>
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