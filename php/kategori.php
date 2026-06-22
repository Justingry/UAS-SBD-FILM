<?php
session_start();
$pdo = require 'b-koneksi.php';
$user_nama = $_SESSION['nama'] ?? null;

// Proses hapus kategori
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_id'])) {
    $id = (int)$_POST['hapus_id'];
    try {
        $pdo->prepare("DELETE FROM kategori WHERE id_kategori = ?")->execute([$id]);
        header("Location: kategori.php?msg=deleted");
        exit;
    } catch (PDOException $e) {
        $error = "Gagal menghapus: " . $e->getMessage();
    }
}

try {
    $daftar = $pdo->query("
        SELECT k.*, COUNT(f.id_film) AS jumlah_film
        FROM kategori k
        LEFT JOIN film f ON f.id_kategori = k.id_kategori
        GROUP BY k.id_kategori
        ORDER BY k.id_kategori
    ")->fetchAll();
} catch (PDOException $e) {
    die("Gagal memuat kategori: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori Film – Bioskop Film Digital</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .badge-count { background:#eaf4fb;color:#2980b9;border:1px solid #d6eaf8;
                       padding:3px 12px;border-radius:20px;font-size:12px;font-weight:700; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand"><span>🎬</span> Bioskop Film Digital</div>
        <ul class="navbar-nav">
            <li><a href="kategori.php" class="active">Kategori Film</a></li>
            <li><a href="film.php">Data Film</a></li>
            <li><a href="transaksi.php">Transaksi</a></li>
        </ul>
        <div class="navbar-user">
            <?php if ($user_nama): ?>
                <span class="user-greeting">Halo, <?= htmlspecialchars(explode(' ', $user_nama)[0]) ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn-login">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Kategori Film</h1>
                <a href="tambah_kategori.php" class="btn btn-success">+ Tambah Kategori</a>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success">
                    <?= $_GET['msg'] === 'added' ? 'Kategori berhasil ditambahkan.' : 'Kategori berhasil dihapus.' ?>
                </div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:5%">NO</th>
                            <th style="width:25%">NAMA KATEGORI</th>
                            <th style="width:45%">DESKRIPSI</th>
                            <th style="width:12%">JUMLAH FILM</th>
                            <th style="width:13%">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($daftar): ?>
                            <?php $no = 1; foreach ($daftar as $kat): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td class="text-bold"><?= htmlspecialchars($kat['nama_kategori']) ?></td>
                                <td class="text-muted" style="font-size:14px;"><?= htmlspecialchars($kat['deskripsi'] ?? '-') ?></td>
                                <td style="text-align:center;">
                                    <span class="badge-count"><?= $kat['jumlah_film'] ?> film</span>
                                </td>
                                <td>
                                    <div class="action-group" style="justify-content:center;">
                                        <a href="edit_kategori.php?id=<?= $kat['id_kategori'] ?>" class="btn-edit" style="padding:5px 10px;font-size:12px;">Edit</a>
                                        <?php if ($kat['jumlah_film'] == 0): ?>
                                        <form method="POST" onsubmit="return confirm('Hapus kategori ini?');" style="display:inline;">
                                            <input type="hidden" name="hapus_id" value="<?= $kat['id_kategori'] ?>">
                                            <button type="submit" class="btn-delete" style="padding:5px 10px;font-size:12px;min-width:auto;height:auto;">Hapus</button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center;color:#95a5a6;padding:30px;">Belum ada kategori.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer>&copy; 2026 Proyek UAS Sistem Basis Data</footer>
</body>
</html>
