<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Tambah Film Baru</title>
        <link rel="stylesheet" href="style.css">
    </head>

    <body>
        <nav class="navbar">
            <div class="navbar-brand">
                <span>🎬</span> Bioskop Film Digital
            </div>
            <ul class="navbar-nav">
                <li><a href="kategori.php">Kategori Film</a></li>
                <li><a href="film.php">Data Film</a></li>
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
        </nav>
    </body>

    <footer>
        &copy; 2026 Proyek UAS Sistem Basis Data
    </footer>

</html>