<?php
session_start();
$pdo = require 'b-koneksi.php';
$user_nama = $_SESSION['nama'] ?? null;

$id_film = (int)($_GET['id'] ?? 0);
if (!$id_film) { header("Location: film.php"); exit; }

// Ambil data film
$stmt = $pdo->prepare("SELECT * FROM film WHERE id_film = ?");
$stmt->execute([$id_film]);
$film = $stmt->fetch();
if (!$film) { header("Location: film.php"); exit; }

// Data untuk dropdown
$kategori    = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();
$rating_usia = $pdo->query("SELECT * FROM rating_usia ORDER BY kode_rating")->fetchAll();
$kualitas    = $pdo->query("SELECT * FROM kualitas_video ORDER BY resolusi")->fetchAll();

// Kualitas yang sudah dipilih
$kualitas_terpilih_db = $pdo->prepare("SELECT id_kualitas FROM film_kualitas WHERE id_film = ?");
$kualitas_terpilih_db->execute([$id_film]);
$kualitas_terpilih = array_column($kualitas_terpilih_db->fetchAll(), 'id_kualitas');

// Sutradara & studio saat ini
$sutradara = $pdo->prepare("SELECT s.* FROM sutradara s JOIN film f ON f.id_sutradara=s.id_sutradara WHERE f.id_film=?");
$sutradara->execute([$id_film]);
$sut = $sutradara->fetch();

$studio_q = $pdo->prepare("SELECT st.* FROM studio st JOIN film f ON f.id_studio=st.id_studio WHERE f.id_film=?");
$studio_q->execute([$id_film]);
$stu = $studio_q->fetch();

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul      = trim($_POST['judul'] ?? '');
    $tahun      = (int)($_POST['tahun'] ?? 0);
    $durasi     = (int)($_POST['durasi'] ?? 0);
    $harga_beli = (float)($_POST['harga_beli'] ?? 0);
    $harga_sewa = (float)($_POST['harga_sewa'] ?? 0);
    $id_rating  = (int)($_POST['id_rating'] ?? 0);
    $id_kategori= (int)($_POST['id_kategori'] ?? 0);

    if (empty($judul))         $errors[] = "Judul wajib diisi.";
    if ($tahun < 1900)         $errors[] = "Tahun tidak valid.";
    if ($durasi <= 0)          $errors[] = "Durasi harus > 0.";
    if ($id_rating <= 0)       $errors[] = "Pilih rating usia.";
    if ($id_kategori <= 0)     $errors[] = "Pilih kategori.";
    if ($harga_beli < 0)       $errors[] = "Harga beli tidak boleh negatif.";
    if ($harga_sewa < 0)       $errors[] = "Harga sewa tidak boleh negatif.";

    $kualitas_post = $_POST['kualitas'] ?? [];
    if (empty($kualitas_post)) $errors[] = "Pilih minimal satu kualitas.";

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $pdo->prepare("UPDATE film SET judul=?,tahun_rilis=?,durasi_menit=?,harga_beli=?,harga_sewa=?,id_kategori=?,id_rating=? WHERE id_film=?")
                ->execute([$judul,$tahun,$durasi,$harga_beli,$harga_sewa,$id_kategori,$id_rating,$id_film]);

            // Update kualitas
            $pdo->prepare("DELETE FROM film_kualitas WHERE id_film=?")->execute([$id_film]);
            $sk = $pdo->prepare("INSERT INTO film_kualitas(id_film,id_kualitas,tersedia) VALUES(?,?,1)");
            foreach ($kualitas_post as $kid) $sk->execute([$id_film, $kid]);

            $pdo->commit();
            $success = true;
            $kualitas_terpilih = $kualitas_post;
            // Refresh film data
            $stmt->execute([$id_film]);
            $film = $stmt->fetch();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Gagal update: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Film – <?= htmlspecialchars($film['judul']) ?></title>
    <link rel="stylesheet" href="style.css">
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
            <?php if ($user_nama): ?>
                <span class="user-greeting">Halo, <?= htmlspecialchars(explode(' ',$user_nama)[0]) ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn-login">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Edit Film</h1>
                <a href="film.php" class="btn-secondary" style="padding:8px 16px;">← Kembali</a>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">Film berhasil diperbarui! <a href="film.php">Kembali ke daftar</a></div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger"><ul style="margin:0;padding-left:20px;">
                    <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
                </ul></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Judul Film <span class="required">*</span></label>
                    <input type="text" name="judul" value="<?= htmlspecialchars($_POST['judul'] ?? $film['judul']) ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tahun Rilis <span class="required">*</span></label>
                        <input type="number" name="tahun" value="<?= $_POST['tahun'] ?? $film['tahun_rilis'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Durasi (menit) <span class="required">*</span></label>
                        <input type="number" name="durasi" value="<?= $_POST['durasi'] ?? $film['durasi_menit'] ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Harga Beli (Rp) <span class="required">*</span></label>
                        <input type="number" step="1000" name="harga_beli" value="<?= $_POST['harga_beli'] ?? $film['harga_beli'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Harga Sewa (Rp) <span class="required">*</span></label>
                        <input type="number" step="1000" name="harga_sewa" value="<?= $_POST['harga_sewa'] ?? $film['harga_sewa'] ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Kategori <span class="required">*</span></label>
                    <select name="id_kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($kategori as $kat): ?>
                            <option value="<?= $kat['id_kategori'] ?>"
                                <?= (($_POST['id_kategori'] ?? $film['id_kategori']) == $kat['id_kategori']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kat['nama_kategori']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Rating Usia <span class="required">*</span></label>
                    <select name="id_rating" required>
                        <option value="">-- Pilih Rating --</option>
                        <?php foreach ($rating_usia as $r): ?>
                            <option value="<?= $r['id_rating'] ?>"
                                <?= (($_POST['id_rating'] ?? $film['id_rating']) == $r['id_rating']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($r['kode_rating']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Kualitas Video <span class="required">*</span></label>
                    <select name="kualitas[]" multiple required>
                        <?php
                        $kp = $_POST['kualitas'] ?? $kualitas_terpilih;
                        foreach ($kualitas as $kual):
                        ?>
                            <option value="<?= $kual['id_kualitas'] ?>"
                                <?= in_array($kual['id_kualitas'], $kp) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kual['resolusi']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">💾 Simpan Perubahan</button>
                    <a href="film.php" class="btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <footer>&copy; 2026 Proyek UAS Sistem Basis Data</footer>
</body>
</html>
