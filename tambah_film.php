<?php
session_start();
require 'b-koneksi.php'; // asumsikan mengembalikan $pdo

// Ambil data kategori yang sudah ada untuk dropdown
$kategori = $pdo->query("SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori")->fetchAll();

$errors = [];
$success = false;

// Proses tambah film jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dasar
    $judul = trim($_POST['judul'] ?? '');
    $tahun = (int)($_POST['tahun'] ?? 0);
    $durasi = (int)($_POST['durasi'] ?? 0);
    $harga_beli = (float)($_POST['harga_beli'] ?? 0);
    $harga_sewa = (float)($_POST['harga_sewa'] ?? 0);
    $id_rating = (int)($_POST['id_rating'] ?? 0);

    // Kategori: bisa dari dropdown atau input baru
    $id_kategori = (int)($_POST['id_kategori'] ?? 0);
    $kategori_baru = trim($_POST['kategori_baru'] ?? '');
    if (!empty($kategori_baru)) {
        // Cek apakah kategori sudah ada
        $stmt = $pdo->prepare("SELECT id_kategori FROM kategori WHERE nama_kategori = ?");
        $stmt->execute([$kategori_baru]);
        $existing = $stmt->fetch();
        if ($existing) {
            $id_kategori = $existing['id_kategori'];
        } else {
            // Insert kategori baru (deskripsi kosong)
            $stmt = $pdo->prepare("INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?, '')");
            $stmt->execute([$kategori_baru]);
            $id_kategori = $pdo->lastInsertId();
        }
    }
    if ($id_kategori <= 0) {
        $errors[] = "Kategori wajib dipilih atau diisi.";
    }

    // Studio: input teks
    $nama_studio = trim($_POST['nama_studio'] ?? '');
    $negara_studio = trim($_POST['negara_studio'] ?? '');
    if (empty($nama_studio)) {
        $errors[] = "Nama studio wajib diisi.";
    } else {
        // Cek apakah studio sudah ada berdasarkan nama
        $stmt = $pdo->prepare("SELECT id_studio FROM studio WHERE nama_studio = ?");
        $stmt->execute([$nama_studio]);
        $existing = $stmt->fetch();
        if ($existing) {
            $id_studio = $existing['id_studio'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO studio (nama_studio, negara_asal) VALUES (?, ?)");
            $stmt->execute([$nama_studio, $negara_studio]);
            $id_studio = $pdo->lastInsertId();
        }
    }

    // Sutradara: input teks
    $nama_sutradara = trim($_POST['nama_sutradara'] ?? '');
    $tgl_lahir_sutradara = $_POST['tgl_lahir_sutradara'] ?? null;
    if (empty($nama_sutradara)) {
        $errors[] = "Nama sutradara wajib diisi.";
    } else {
        $stmt = $pdo->prepare("SELECT id_sutradara FROM sutradara WHERE nama_sutradara = ?");
        $stmt->execute([$nama_sutradara]);
        $existing = $stmt->fetch();
        if ($existing) {
            $id_sutradara = $existing['id_sutradara'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO sutradara (nama_sutradara, tanggal_lahir) VALUES (?, ?)");
            $stmt->execute([$nama_sutradara, $tgl_lahir_sutradara ?: null]);
            $id_sutradara = $pdo->lastInsertId();
        }
    }

    // Aktor: input teks (bisa dipisah koma)
    $aktor_raw = trim($_POST['aktor'] ?? '');
    $negara_aktor = trim($_POST['negara_aktor'] ?? '');
    $aktor_list = [];
    if (!empty($aktor_raw)) {
        // Split berdasarkan koma, bersihkan spasi
        $names = array_map('trim', explode(',', $aktor_raw));
        $names = array_filter($names); // buang yang kosong
        foreach ($names as $name) {
            // Cek apakah aktor sudah ada
            $stmt = $pdo->prepare("SELECT id_aktor FROM aktor WHERE nama_aktor = ?");
            $stmt->execute([$name]);
            $existing = $stmt->fetch();
            if ($existing) {
                $aktor_list[] = $existing['id_aktor'];
            } else {
                // Insert aktor baru dengan negara (jika diisi)
                $stmt = $pdo->prepare("INSERT INTO aktor (nama_aktor, negara) VALUES (?, ?)");
                $stmt->execute([$name, $negara_aktor]);
                $aktor_list[] = $pdo->lastInsertId();
            }
        }
    }
    if (empty($aktor_list)) {
        $errors[] = "Minimal satu aktor wajib diisi.";
    }

    // Kualitas video: tetap dari dropdown (multiple)
    $kualitas_terpilih = $_POST['kualitas'] ?? [];
    if (empty($kualitas_terpilih)) {
        $errors[] = "Pilih minimal satu kualitas video.";
    }

    // Validasi dasar lainnya
    if (empty($judul)) $errors[] = "Judul film wajib diisi.";
    if ($tahun < 1900 || $tahun > date('Y')+1) $errors[] = "Tahun rilis tidak valid.";
    if ($durasi <= 0) $errors[] = "Durasi harus lebih dari 0 menit.";
    if ($harga_beli < 0) $errors[] = "Harga beli tidak boleh negatif.";
    if ($harga_sewa < 0) $errors[] = "Harga sewa tidak boleh negatif.";
    if ($id_rating <= 0) $errors[] = "Pilih rating usia.";

    // Jika tidak ada error, insert ke database
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Insert film
            $stmt = $pdo->prepare("INSERT INTO film (judul, tahun_rilis, durasi_menit, harga_beli, harga_sewa, id_kategori, id_rating, id_studio, id_sutradara) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$judul, $tahun, $durasi, $harga_beli, $harga_sewa, $id_kategori, $id_rating, $id_studio, $id_sutradara]);
            $id_film = $pdo->lastInsertId();

            // Insert film_aktor
            $stmtAktor = $pdo->prepare("INSERT INTO film_aktor (id_film, id_aktor) VALUES (?, ?)");
            foreach ($aktor_list as $id_aktor) {
                $stmtAktor->execute([$id_film, $id_aktor]);
            }

            // Insert film_kualitas
            $stmtKualitas = $pdo->prepare("INSERT INTO film_kualitas (id_film, id_kualitas, tersedia) VALUES (?, ?, 1)");
            foreach ($kualitas_terpilih as $id_kualitas) {
                $stmtKualitas->execute([$id_film, $id_kualitas]);
            }

            $pdo->commit();
            $success = true;
            // Redirect ke film.php dengan pesan sukses
            header("Location: film.php?msg=added");
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Gagal menyimpan data: " . $e->getMessage();
        }
    }
}

// Ambil data untuk dropdown lainnya
$rating_usia = $pdo->query("SELECT id_rating, kode_rating FROM rating_usia ORDER BY kode_rating")->fetchAll();
$kualitas = $pdo->query("SELECT id_kualitas, resolusi FROM kualitas_video ORDER BY resolusi")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Film Baru</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 5px; color: #2c3e50; }
        .form-group input, .form-group select { width: 100%; padding: 10px 12px; border: 1px solid #dce1e8; border-radius: 4px; font-size: 14px; box-sizing: border-box; }
        .form-group select[multiple] { height: 100px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-actions { display: flex; gap: 10px; margin-top: 20px; }
        .btn-primary { background-color: #3498db; color: white; padding: 10px 24px; border: none; border-radius: 4px; font-weight: 600; cursor: pointer; }
        .btn-primary:hover { background-color: #2980b9; }
        .btn-secondary { background-color: #95a5a6; color: white; padding: 10px 24px; border: none; border-radius: 4px; font-weight: 600; text-decoration: none; display: inline-block; text-align: center; }
        .btn-secondary:hover { background-color: #7f8c8d; }
        .alert { padding: 12px 20px; border-radius: 4px; margin-bottom: 20px; }
        .alert-danger { background-color: #fdecea; border: 1px solid #f5c6cb; color: #721c24; }
        .alert-success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .required { color: #e74c3c; }
        .kategori-group { display: flex; gap: 10px; align-items: center; }
        .kategori-group select { flex: 2; }
        .kategori-group input { flex: 1; }
        .kategori-group button { flex: 0 0 auto; padding: 10px 16px; background-color: #2ecc71; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .kategori-group button:hover { background-color: #27ae60; }
        .btn-sm { font-size: 13px; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">
            <span>🎬</span> Bioskop Film Digital
        </div>
        <div class="navbar-right">
            <ul class="navbar-nav">
                <li><a href="kategori.php">Kategori Film</a></li>
                <li><a href="film.php" class="active">Data Film</a></li>
                <li><a href="transaksi.php">Transaksi</a></li>
            </ul>
            <div class="navbar-user">
                <?php if (isset($_SESSION['nama'])): ?>
                    <span class="user-greeting">Halo, <?= htmlspecialchars(explode(' ', $_SESSION['nama'])[0]); ?></span>
                    <a href="logout.php" class="btn-logout">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn-login">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Tambah Film Baru</h1>
            </div>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul style="margin:0; padding-left:20px;">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">
                    Film berhasil ditambahkan! <a href="film.php">Kembali ke daftar</a>
                </div>
            <?php endif; ?>
            <form method="POST" action="">
                <!-- Judul -->
                <div class="form-group">
                    <label>Judul Film <span class="required">*</span></label>
                    <input type="text" name="judul" value="<?= htmlspecialchars($_POST['judul'] ?? '') ?>" required>
                </div>

                <!-- Tahun & Durasi -->
                <div class="form-row">
                    <div class="form-group">
                        <label>Tahun Rilis <span class="required">*</span></label>
                        <input type="number" name="tahun" value="<?= htmlspecialchars($_POST['tahun'] ?? date('Y')) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Durasi (menit) <span class="required">*</span></label>
                        <input type="number" name="durasi" value="<?= htmlspecialchars($_POST['durasi'] ?? '') ?>" required>
                    </div>
                </div>

                <!-- Harga -->
                <div class="form-row">
                    <div class="form-group">
                        <label>Harga Beli (Rp) <span class="required">*</span></label>
                        <input type="number" step="1000" name="harga_beli" value="<?= htmlspecialchars($_POST['harga_beli'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Harga Sewa (Rp) <span class="required">*</span></label>
                        <input type="number" step="1000" name="harga_sewa" value="<?= htmlspecialchars($_POST['harga_sewa'] ?? '') ?>" required>
                    </div>
                </div>

                <!-- Rating Usia -->
                <div class="form-group">
                    <label>Rating Usia <span class="required">*</span></label>
                    <select name="id_rating" required>
                        <option value="">-- Pilih Rating --</option>
                        <?php foreach ($rating_usia as $rating): ?>
                            <option value="<?= $rating['id_rating'] ?>" <?= (isset($_POST['id_rating']) && $_POST['id_rating'] == $rating['id_rating']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($rating['kode_rating']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Kategori: dropdown + input baru -->
                <div class="form-group">
                    <label>Kategori <span class="required">*</span></label>
                    <div class="kategori-group">
                        <select name="id_kategori" id="id_kategori">
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($kategori as $kat): ?>
                                <option value="<?= $kat['id_kategori'] ?>" <?= (isset($_POST['id_kategori']) && $_POST['id_kategori'] == $kat['id_kategori']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kat['nama_kategori']) ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="new">+ Tambah Baru</option>
                        </select>
                        <input type="text" name="kategori_baru" id="kategori_baru" placeholder="Nama kategori baru" style="display:none;">
                        <button type="button" id="tambah_kategori" class="btn-sm" style="display:none;">Tambah</button>
                    </div>
                    <small style="color:#7f8c8d;">Pilih dari daftar atau tulis nama baru lalu klik "Tambah"</small>
                </div>

                <!-- Studio: input teks + negara -->
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Studio <span class="required">*</span></label>
                        <input type="text" name="nama_studio" value="<?= htmlspecialchars($_POST['nama_studio'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Negara Asal Studio (opsional)</label>
                        <input type="text" name="negara_studio" value="<?= htmlspecialchars($_POST['negara_studio'] ?? '') ?>">
                    </div>
                </div>

                <!-- Sutradara: input teks + tanggal lahir -->
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Sutradara <span class="required">*</span></label>
                        <input type="text" name="nama_sutradara" value="<?= htmlspecialchars($_POST['nama_sutradara'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Lahir Sutradara (opsional)</label>
                        <input type="date" name="tgl_lahir_sutradara" value="<?= htmlspecialchars($_POST['tgl_lahir_sutradara'] ?? '') ?>">
                    </div>
                </div>

                <!-- Aktor: input teks (bisa banyak dipisah koma) + negara -->
                <div class="form-row">
                    <div class="form-group">
                        <label>Aktor (pisahkan dengan koma jika lebih dari satu) <span class="required">*</span></label>
                        <input type="text" name="aktor" value="<?= htmlspecialchars($_POST['aktor'] ?? '') ?>" placeholder="Contoh: Leonardo DiCaprio, Robert Downey Jr." required>
                    </div>
                    <div class="form-group">
                        <label>Negara Asal Aktor (opsional)</label>
                        <input type="text" name="negara_aktor" value="<?= htmlspecialchars($_POST['negara_aktor'] ?? '') ?>">
                    </div>
                </div>

                <!-- Kualitas Video: multiple select -->
                <div class="form-group">
                    <label>Kualitas Video (pilih satu atau lebih) <span class="required">*</span></label>
                    <select name="kualitas[]" multiple required>
                        <?php foreach ($kualitas as $kual): ?>
                            <option value="<?= $kual['id_kualitas'] ?>" <?= (isset($_POST['kualitas']) && in_array($kual['id_kualitas'], $_POST['kualitas'])) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kual['resolusi']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Simpan Film</button>
                    <a href="film.php" class="btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <footer>
        &copy; 2026 Proyek UAS Sistem Basis Data
    </footer>

    <script>
        // Toggle input kategori baru saat memilih opsi "Tambah Baru"
        document.addEventListener('DOMContentLoaded', function() {
            const selectKategori = document.getElementById('id_kategori');
            const inputBaru = document.getElementById('kategori_baru');
            const btnTambah = document.getElementById('tambah_kategori');

            selectKategori.addEventListener('change', function() {
                if (this.value === 'new') {
                    inputBaru.style.display = 'inline-block';
                    btnTambah.style.display = 'inline-block';
                    inputBaru.focus();
                } else {
                    inputBaru.style.display = 'none';
                    btnTambah.style.display = 'none';
                    inputBaru.value = '';
                }
            });

            // Tombol Tambah Kategori (AJAX)
            btnTambah.addEventListener('click', function() {
                const nama = inputBaru.value.trim();
                if (!nama) {
                    alert('Masukkan nama kategori baru.');
                    return;
                }
                // Kirim request ke server untuk menambahkan kategori
                fetch('b-tambah-kategori.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'nama=' + encodeURIComponent(nama)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Tambahkan opsi ke select
                        const opt = document.createElement('option');
                        opt.value = data.id;
                        opt.textContent = nama;
                        // Sisipkan sebelum opsi "Tambah Baru"
                        const newOpt = selectKategori.querySelector('option[value="new"]');
                        selectKategori.insertBefore(opt, newOpt);
                        // Pilih opsi yang baru
                        selectKategori.value = data.id;
                        // Sembunyikan input
                        inputBaru.style.display = 'none';
                        btnTambah.style.display = 'none';
                        inputBaru.value = '';
                        alert('Kategori berhasil ditambahkan!');
                    } else {
                        alert('Gagal menambahkan kategori: ' + data.message);
                    }
                })
                .catch(err => alert('Error: ' + err));
            });
        });
    </script>
</body>
</html>