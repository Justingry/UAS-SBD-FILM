<?php
session_start();
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: login.php?error=Silakan+login+terlebih+dahulu");
    exit;
}

$pdo = require 'b-koneksi.php';
$id_film = (int)($_GET['id'] ?? 0);

if (!$id_film) {
    header("Location: film.php");
    exit;
}

// Ambil data film
$stmt = $pdo->prepare("
    SELECT f.*, r.kode_rating, k.nama_kategori
    FROM film f
    LEFT JOIN rating_usia r ON r.id_rating = f.id_rating
    LEFT JOIN kategori k ON k.id_kategori = f.id_kategori
    WHERE f.id_film = ?
");
$stmt->execute([$id_film]);
$film = $stmt->fetch();

if (!$film) {
    header("Location: film.php");
    exit;
}

// Ambil metode pembayaran & voucher
$metode  = $pdo->query("SELECT * FROM metode_pembayaran ORDER BY tipe")->fetchAll();
$voucher = $pdo->query("SELECT * FROM voucher WHERE tanggal_kadaluarsa >= CURDATE() ORDER BY potongan_persen DESC")->fetchAll();

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis      = $_POST['jenis']      ?? '';
    $id_metode  = (int)($_POST['id_metode']  ?? 0);
    $id_voucher = (int)($_POST['id_voucher'] ?? 0);

    if (!in_array($jenis, ['Sewa', 'Beli'])) $errors[] = "Pilih jenis layanan.";
    if ($id_metode <= 0) $errors[] = "Pilih metode pembayaran.";

    if (empty($errors)) {
        try {
            $out_id    = 0;
            $out_pesan = '';

            $call = $pdo->prepare("CALL sp_tambah_transaksi(?, ?, ?, ?, ?, @out_id, @out_pesan)");
            $call->execute([
                $_SESSION['id_pengguna'],
                $id_film,
                $jenis,
                $id_metode,
                $id_voucher ?: null
            ]);

            $row = $pdo->query("SELECT @out_id AS id, @out_pesan AS pesan")->fetch();
            if ($row['id'] > 0) {
                $success = true;
                $pesan_sukses = $row['pesan'];
            } else {
                $errors[] = $row['pesan'] ?? 'Terjadi kesalahan.';
            }
        } catch (PDOException $e) {
            $errors[] = "Gagal: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sewa / Beli Film – <?= htmlspecialchars($film['judul']) ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .film-info-box {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: #fff; border-radius: 8px; padding: 24px 28px;
            margin-bottom: 28px; display: flex; gap: 20px; align-items: flex-start;
        }
        .film-info-box .film-icon { font-size: 48px; }
        .film-info-box h2 { font-size: 22px; font-weight: 800; margin-bottom: 6px; }
        .film-info-box p  { font-size: 13px; color: #bdc3c7; margin: 2px 0; }
        .price-box {
            display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 24px;
        }
        .price-card {
            flex: 1; min-width: 140px; background: #f8f9fa;
            border: 2px solid #eee; border-radius: 8px; padding: 16px;
            text-align: center; cursor: pointer; transition: border-color .2s, background .2s;
        }
        .price-card:has(input:checked),
        .price-card.selected {
            border-color: #3498db; background: #eaf4fb;
        }
        .price-card input { display: none; }
        .price-card .label { font-size: 12px; color: #7f8c8d; font-weight: 700; text-transform: uppercase; }
        .price-card .amount { font-size: 22px; font-weight: 800; color: #2c3e50; margin: 6px 0; }
        .price-card .desc { font-size: 12px; color: #95a5a6; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand"><span>🎬</span> Bioskop Film Digital</div>
        <ul class="navbar-nav">
            <li><a href="kategori.php">Kategori Film</a></li>
            <li><a href="film.php" class="active">Data Film</a></li>
            <li><a href="transaksi.php">Transaksi</a></li>
        </ul>
        <div class="navbar-user">
            <span class="user-greeting">Halo, <?= htmlspecialchars(explode(' ', $_SESSION['nama'])[0]) ?></span>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container" style="max-width:700px;">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Sewa / Beli Film</h1>
                <a href="film.php" class="btn-secondary" style="padding:8px 16px;">← Kembali</a>
            </div>

            <!-- Info Film -->
            <div class="film-info-box">
                <div class="film-icon">🎬</div>
                <div>
                    <h2><?= htmlspecialchars($film['judul']) ?></h2>
                    <p>📅 <?= $film['tahun_rilis'] ?> &nbsp;|&nbsp; ⏱ <?= $film['durasi_menit'] ?> menit</p>
                    <p>🎭 <?= htmlspecialchars($film['nama_kategori'] ?? '-') ?> &nbsp;|&nbsp;
                       🔞 Rating: <?= htmlspecialchars($film['kode_rating'] ?? 'SU') ?></p>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    ✅ <?= htmlspecialchars($pesan_sukses) ?><br>
                    <a href="transaksi.php">Lihat Riwayat Transaksi</a> | <a href="film.php">Kembali ke Daftar Film</a>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul style="margin:0;padding-left:20px;">
                        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form method="POST">
                <!-- Pilih Jenis Layanan -->
                <div class="form-group">
                    <label>Pilih Layanan <span class="required">*</span></label>
                    <div class="price-box">
                        <label class="price-card">
                            <input type="radio" name="jenis" value="Sewa" required
                                   <?= (($_POST['jenis'] ?? '') === 'Sewa') ? 'checked' : '' ?>>
                            <div class="label">🕐 Sewa (2 Hari)</div>
                            <div class="amount">Rp <?= number_format($film['harga_sewa'], 0, ',', '.') ?></div>
                            <div class="desc">Akses selama 48 jam</div>
                        </label>
                        <label class="price-card">
                            <input type="radio" name="jenis" value="Beli"
                                   <?= (($_POST['jenis'] ?? '') === 'Beli') ? 'checked' : '' ?>>
                            <div class="label">🏠 Beli (Selamanya)</div>
                            <div class="amount">Rp <?= number_format($film['harga_beli'], 0, ',', '.') ?></div>
                            <div class="desc">Masuk ke pustaka pribadi</div>
                        </label>
                    </div>
                </div>

                <!-- Metode Pembayaran -->
                <div class="form-group">
                    <label>Metode Pembayaran <span class="required">*</span></label>
                    <select name="id_metode" required>
                        <option value="">-- Pilih Metode --</option>
                        <?php foreach ($metode as $m): ?>
                            <option value="<?= $m['id_metode'] ?>"
                                <?= (($_POST['id_metode'] ?? '') == $m['id_metode']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['nama_metode']) ?> (<?= $m['tipe'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Voucher (opsional) -->
                <div class="form-group">
                    <label>Kode Voucher (opsional)</label>
                    <select name="id_voucher">
                        <option value="">-- Tidak Pakai Voucher --</option>
                        <?php foreach ($voucher as $v): ?>
                            <option value="<?= $v['id_voucher'] ?>"
                                <?= (($_POST['id_voucher'] ?? '') == $v['id_voucher']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($v['kode_voucher']) ?> – Diskon <?= $v['potongan_persen'] ?>%
                                (s.d. <?= $v['tanggal_kadaluarsa'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">✅ Proses Transaksi</button>
                    <a href="film.php" class="btn-secondary">Batal</a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <footer>&copy; 2026 Proyek UAS Sistem Basis Data</footer>

    <script>
    // Highlight pilihan sewa/beli
    document.querySelectorAll('.price-card input').forEach(r => {
        r.addEventListener('change', () => {
            document.querySelectorAll('.price-card').forEach(c => c.classList.remove('selected'));
            r.closest('.price-card').classList.add('selected');
        });
        if (r.checked) r.closest('.price-card').classList.add('selected');
    });
    </script>
</body>
</html>
