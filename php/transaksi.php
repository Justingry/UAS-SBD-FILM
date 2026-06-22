<?php
session_start();
$pdo = require 'b-koneksi.php';

$user_nama = $_SESSION['nama'] ?? null;

try {
    // Ambil semua transaksi beserta detail pengguna, status, metode
    $stmt = $pdo->query("
        SELECT
            t.id_transaksi,
            t.tanggal_transaksi,
            t.total_bayar,
            p.nama            AS nama_pengguna,
            st.nama_status,
            mp.nama_metode,
            GROUP_CONCAT(
                CONCAT(f.judul, ' (', dt.jenis_layanan, ')')
                SEPARATOR ', '
            ) AS detail_film
        FROM transaksi t
        JOIN pengguna         p  ON p.id_pengguna   = t.id_pengguna
        JOIN status_transaksi st ON st.id_status     = t.id_status
        JOIN metode_pembayaran mp ON mp.id_metode    = t.id_metode
        LEFT JOIN detail_transaksi dt ON dt.id_transaksi = t.id_transaksi
        LEFT JOIN film         f  ON f.id_film       = dt.id_film
        GROUP BY t.id_transaksi
        ORDER BY t.tanggal_transaksi DESC
    ");
    $daftar_transaksi = $stmt->fetchAll();

    // Statistik ringkas
    $statistik = $pdo->query("
        SELECT
            COUNT(*)                            AS total_transaksi,
            SUM(CASE WHEN id_status=2 THEN total_bayar ELSE 0 END) AS total_pendapatan,
            SUM(CASE WHEN id_status=1 THEN 1 ELSE 0 END)           AS pending,
            SUM(CASE WHEN id_status=2 THEN 1 ELSE 0 END)           AS sukses,
            SUM(CASE WHEN id_status=3 THEN 1 ELSE 0 END)           AS gagal
        FROM transaksi
    ")->fetch();

} catch (PDOException $e) {
    die("Gagal memuat data transaksi: " . $e->getMessage());
}

function badge_status(string $status): string {
    return match($status) {
        'Sukses'  => '<span class="badge" style="background:#e2fbe8;color:#27ae60;border:1px solid #cbf7d5;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">✓ Sukses</span>',
        'Pending' => '<span class="badge" style="background:#fff8e1;color:#f39c12;border:1px solid #ffe082;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">⏳ Pending</span>',
        default   => '<span class="badge" style="background:#fdecea;color:#e74c3c;border:1px solid #f5c6cb;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">✗ Gagal</span>',
    };
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi – Bioskop Film Digital</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            padding: 20px 24px;
            border-left: 4px solid #3498db;
        }
        .stat-card.green  { border-left-color: #2ecc71; }
        .stat-card.orange { border-left-color: #f39c12; }
        .stat-card.red    { border-left-color: #e74c3c; }
        .stat-label { font-size: 12px; color: #7f8c8d; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }
        .stat-value { font-size: 26px; font-weight: 800; color: #2c3e50; margin-top: 4px; }
        .badge-info { background:#eaf4fb; color:#2980b9; border:1px solid #d6eaf8;
                      padding:3px 10px; border-radius:20px; font-size:12px; font-weight:700; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand"><span>🎬</span> Bioskop Film Digital</div>
        <ul class="navbar-nav">
            <li><a href="kategori.php">Kategori Film</a></li>
            <li><a href="film.php">Data Film</a></li>
            <li><a href="transaksi.php" class="active">Transaksi</a></li>
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

        <!-- Statistik Ringkas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Transaksi</div>
                <div class="stat-value"><?= number_format($statistik['total_transaksi']) ?></div>
            </div>
            <div class="stat-card green">
                <div class="stat-label">Total Pendapatan</div>
                <div class="stat-value" style="font-size:18px;">Rp <?= number_format($statistik['total_pendapatan'], 0, ',', '.') ?></div>
            </div>
            <div class="stat-card green">
                <div class="stat-label">Sukses</div>
                <div class="stat-value"><?= $statistik['sukses'] ?></div>
            </div>
            <div class="stat-card orange">
                <div class="stat-label">Pending</div>
                <div class="stat-value"><?= $statistik['pending'] ?></div>
            </div>
            <div class="stat-card red">
                <div class="stat-label">Gagal</div>
                <div class="stat-value"><?= $statistik['gagal'] ?></div>
            </div>
        </div>

        <!-- Tabel Transaksi -->
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Riwayat Transaksi</h1>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:5%">NO</th>
                            <th style="width:14%">TANGGAL</th>
                            <th style="width:16%">PENGGUNA</th>
                            <th style="width:28%">FILM</th>
                            <th style="width:12%">METODE</th>
                            <th style="width:12%">TOTAL</th>
                            <th style="width:13%">STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($daftar_transaksi): ?>
                            <?php $no = 1; foreach ($daftar_transaksi as $trx): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td style="font-size:13px;color:#7f8c8d;">
                                    <?= date('d M Y', strtotime($trx['tanggal_transaksi'])) ?><br>
                                    <small><?= date('H:i', strtotime($trx['tanggal_transaksi'])) ?></small>
                                </td>
                                <td class="text-bold"><?= htmlspecialchars($trx['nama_pengguna']) ?></td>
                                <td style="font-size:13px;"><?= htmlspecialchars($trx['detail_film'] ?? '-') ?></td>
                                <td><span class="badge-info"><?= htmlspecialchars($trx['nama_metode']) ?></span></td>
                                <td class="text-price">Rp <?= number_format($trx['total_bayar'], 0, ',', '.') ?></td>
                                <td><?= badge_status($trx['nama_status']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" style="text-align:center;color:#95a5a6;padding:30px;">Belum ada data transaksi.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer>&copy; 2026 Proyek UAS Sistem Basis Data</footer>
</body>
</html>
