<?php
session_start();
if (isset($_SESSION['id_pengguna'])) {
    header("Location: film.php");
    exit;
}
$error = htmlspecialchars($_GET['error'] ?? '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar – Bioskop Film Digital</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .auth-container { max-width: 460px; margin: 60px auto; padding: 0 20px; }
        .auth-card {
            background: #fff; border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 40px 36px; border: 1px solid #eef2f5;
        }
        .auth-title { text-align:center; font-size:24px; font-weight:700; color:#2c3e50; margin-bottom:6px; }
        .auth-subtitle { text-align:center; color:#7f8c8d; font-size:14px; margin-bottom:28px; }
        .auth-footer { text-align:center; margin-top:20px; font-size:14px; color:#7f8c8d; }
        .auth-footer a { color:#3498db; text-decoration:none; font-weight:600; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand"><span>🎬</span> Bioskop Film Digital</div>
        <ul class="navbar-nav">
            <li><a href="kategori.php">Kategori Film</a></li>
            <li><a href="film.php">Data Film</a></li>
            <li><a href="transaksi.php">Transaksi</a></li>
        </ul>
        <div class="navbar-user"><a href="login.php" class="btn-login">Login</a></div>
    </nav>

    <div class="auth-container">
        <div class="auth-card">
            <h1 class="auth-title">🎬 Daftar Akun</h1>
            <p class="auth-subtitle">Buat akun untuk mulai menikmati film</p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" action="b-register.php">
                <div class="form-group">
                    <label>Nama Lengkap <span class="required">*</span></label>
                    <input type="text" name="nama" placeholder="Nama Anda" required>
                </div>
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" placeholder="nama@email.com" required>
                </div>
                <div class="form-group">
                    <label>Password <span class="required">*</span></label>
                    <input type="password" name="pw" placeholder="Minimal 8 karakter" minlength="8" required>
                </div>
                <div class="form-actions" style="margin-top:24px;">
                    <button type="submit" class="btn-primary" style="width:100%;justify-content:center;">Buat Akun</button>
                </div>
            </form>

            <div class="auth-footer">
                Sudah punya akun? <a href="login.php">Login di sini</a>
            </div>
        </div>
    </div>

    <footer>&copy; 2026 Proyek UAS Sistem Basis Data</footer>
</body>
</html>
