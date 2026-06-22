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
    <title>Login – Bioskop Film Digital</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .auth-container {
            max-width: 420px;
            margin: 80px auto;
            padding: 0 20px;
        }
        .auth-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 40px 36px;
            border: 1px solid #eef2f5;
        }
        .auth-title {
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 6px;
        }
        .auth-subtitle {
            text-align: center;
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 28px;
        }
        .auth-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #7f8c8d;
        }
        .auth-footer a { color: #3498db; text-decoration: none; font-weight: 600; }
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
        <div class="navbar-user">
            <a href="login.php" class="btn-login active">Login</a>
        </div>
    </nav>

    <div class="auth-container">
        <div class="auth-card">
            <h1 class="auth-title">🎬 Masuk</h1>
            <p class="auth-subtitle">Silakan login untuk melanjutkan</p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'logout'): ?>
                <div class="alert alert-success">Anda berhasil logout.</div>
            <?php endif; ?>

            <form method="POST" action="b-login.php">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="nama@email.com" required autofocus>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="pw" placeholder="••••••••" required>
                </div>
                <div class="form-actions" style="margin-top:24px;">
                    <button type="submit" class="btn-primary" style="width:100%;justify-content:center;">Masuk</button>
                </div>
            </form>

            <div class="auth-footer">
                Belum punya akun? <a href="register.php">Daftar di sini</a>
            </div>
        </div>
    </div>

    <footer>&copy; 2026 Proyek UAS Sistem Basis Data</footer>
</body>
</html>
