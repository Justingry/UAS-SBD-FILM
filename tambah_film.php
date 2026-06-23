<?php
// tambah_film.php - FRONTEND
session_start();
require "koneksi.php";

// Ambil data untuk dropdown
$kategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori");
$rating = mysqli_query($conn, "SELECT * FROM rating_usia ORDER BY kode_rating");
$studio = mysqli_query($conn, "SELECT * FROM studio ORDER BY nama_studio");
$sutradara = mysqli_query($conn, "SELECT * FROM sutradara ORDER BY nama_sutradara");

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Film</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">
            <span>🎬</span> Bioskop Film Digital
        </div>
    </nav>

    <div class="container">
        <div class="card" style="max-width: 800px; margin: 40px auto;">
            <div class="card-header">
                <h1 class="card-title">Tambah Film Baru</h1>
                <a href="index.php" class="btn btn-kembali">← Kembali</a>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] === 'success' ? 'sukses' : 'error'; ?>">
                    <?= $flash['message']; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="proses_tambah_film.php">
                <!-- Judul, tahun, durasi, harga -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="judul">Judul Film <span class="required">*</span></label>
                        <input type="text" id="judul" name="judul" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="tahun_rilis">Tahun Rilis <span class="required">*</span></label>
                        <input type="number" id="tahun_rilis" name="tahun_rilis" class="form-control" required min="1900" max="2099">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="durasi_menit">Durasi (menit) <span class="required">*</span></label>
                        <input type="number" id="durasi_menit" name="durasi_menit" class="form-control" required min="1">
                    </div>
                    <div class="form-group">
                        <label for="harga_beli">Harga Beli (Rp) <span class="required">*</span></label>
                        <input type="text" id="harga_beli" name="harga_beli" class="form-control" required placeholder="Contoh: 150000">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="harga_sewa">Harga Sewa (Rp) <span class="required">*</span></label>
                        <input type="text" id="harga_sewa" name="harga_sewa" class="form-control" required placeholder="Contoh: 35000">
                    </div>
                </div>

                <!-- Kategori & Rating -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="id_kategori">Kategori <span class="required">*</span></label>
                        <select id="id_kategori" name="id_kategori" class="form-control" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php while ($row = mysqli_fetch_assoc($kategori)): ?>
                                <option value="<?= $row['id_kategori'] ?>"><?= htmlspecialchars($row['nama_kategori']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="id_rating">Rating Usia <span class="required">*</span></label>
                        <select id="id_rating" name="id_rating" class="form-control" required>
                            <option value="">-- Pilih Rating --</option>
                            <?php while ($row = mysqli_fetch_assoc($rating)): ?>
                                <option value="<?= $row['id_rating'] ?>"><?= htmlspecialchars($row['kode_rating']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <!-- Studio -->
                <div class="form-group">
                    <label for="id_studio">Studio Produksi <span class="required">*</span></label>
                    <select id="id_studio" name="id_studio" class="form-control" required>
                        <option value="">-- Pilih Studio --</option>
                        <?php while ($row = mysqli_fetch_assoc($studio)): ?>
                            <option value="<?= $row['id_studio'] ?>"><?= htmlspecialchars($row['nama_studio']) ?></option>
                        <?php endwhile; ?>
                        <option value="new">+ Tambah Studio Baru</option>
                    </select>
                </div>
                <div class="form-group" id="studio_new_group" style="display:none;">
                    <label for="studio_baru">Nama Studio Baru</label>
                    <input type="text" id="studio_baru" name="studio_baru" class="form-control" placeholder="Ketik nama studio baru">
                </div>

                <!-- Sutradara -->
                <div class="form-group">
                    <label for="id_sutradara">Sutradara <span class="required">*</span></label>
                    <select id="id_sutradara" name="id_sutradara" class="form-control" required>
                        <option value="">-- Pilih Sutradara --</option>
                        <?php while ($row = mysqli_fetch_assoc($sutradara)): ?>
                            <option value="<?= $row['id_sutradara'] ?>"><?= htmlspecialchars($row['nama_sutradara']) ?></option>
                        <?php endwhile; ?>
                        <option value="new">+ Tambah Sutradara Baru</option>
                    </select>
                </div>
                <div class="form-group" id="sutradara_new_group" style="display:none;">
                    <label for="sutradara_baru">Nama Sutradara Baru</label>
                    <input type="text" id="sutradara_baru" name="sutradara_baru" class="form-control" placeholder="Ketik nama sutradara baru">
                </div>

                <div class="form-actions">
                    <button type="submit" href="index.php" class="btn btn-tambah">Simpan Film</button>
                    <a href="index.php" class="btn btn-kembali">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <footer>&copy; 2026 Proyek UAS Sistem Basis Data</footer>

    <!-- JavaScript untuk menampilkan input tambahan jika pilih "new" -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Studio
            const studioSelect = document.getElementById('id_studio');
            const studioNewGroup = document.getElementById('studio_new_group');
            const studioInput = document.getElementById('studio_baru');
            studioSelect.addEventListener('change', function() {
                if (this.value === 'new') {
                    studioNewGroup.style.display = 'block';
                    studioInput.setAttribute('required', true);
                } else {
                    studioNewGroup.style.display = 'none';
                    studioInput.removeAttribute('required');
                    studioInput.value = '';
                }
            });

            // Sutradara
            const sutradaraSelect = document.getElementById('id_sutradara');
            const sutradaraNewGroup = document.getElementById('sutradara_new_group');
            const sutradaraInput = document.getElementById('sutradara_baru');
            sutradaraSelect.addEventListener('change', function() {
                if (this.value === 'new') {
                    sutradaraNewGroup.style.display = 'block';
                    sutradaraInput.setAttribute('required', true);
                } else {
                    sutradaraNewGroup.style.display = 'none';
                    sutradaraInput.removeAttribute('required');
                    sutradaraInput.value = '';
                }
            });
        });
    </script>
</body>
</html>