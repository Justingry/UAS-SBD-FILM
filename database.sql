--1.Tabel Kategori
CREATE TABLE kategori (
    id_kategori INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(50) NOT NULL,
    deskripsi TEXT
);

--2. Tabel Rating Usia
CREATE TABLE rating_usia (
    id_rating INT AUTO_INCREMENT PRIMARY KEY,
    kode_rating VARCHAR(10) NOT NULL UNIQUE,
    deskripsi VARCHAR(255)
);

--3. Tabel Kualitas
CREATE TABLE kualitas_video (
    id_kualitas INT AUTO_INCREMENT PRIMARY KEY,
    resolusi VARCHAR(20) NOT NULL,
    deskripsi VARCHAR(100)
);

--4. Tabel Metode Pembayaran
CREATE TABLE metode_pembayaran (
    id_metode INT AUTO_INCREMENT PRIMARY KEY,
    nama_metode VARCHAR(50) NOT NULL,
    tipe VARCHAR(30)
);

--5. Tabel Status Transaksi
CREATE TABLE status_transaksi (
    id_status INT AUTO_INCREMENT PRIMARY KEY,
    nama_status VARCHAR(30) NOT NULL UNIQUE
);

--6. Tabel Pengguna/User
CREATE TABLE pengguna (
    id_pengguna INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    pw VARCHAR(255) NOT NULL,
    tanggal_daftar DATETIME DEFAULT CURRENT_TIMESTAMP,
    status_akun VARCHAR(20) DEFAULT 'Aktif'
);

--7. Tabel Voucher
CREATE TABLE voucher (
    id_voucher INT AUTO_INCREMENT PRIMARY KEY,
    kode_voucher VARCHAR(20) NOT NULL UNIQUE,
    potongan_persen INT NOT NULL,
    tanggal_kadaluarsa DATE
);

--8. Tabel Studio Produksi
CREATE TABLE studio (
    id_studio INT AUTO_INCREMENT PRIMARY KEY,
    nama_studio VARCHAR(100) NOT NULL,
);


--9. Tabel Sutradara
CREATE TABLE sutradara (
    id_sutradara INT AUTO_INCREMENT PRIMARY KEY,
    nama_sutradara VARCHAR(100) NOT NULL,
);


--10. Tabel Aktor
CREATE TABLE aktor (
    id_aktor INT AUTO_INCREMENT PRIMARY KEY,
    nama_aktor VARCHAR(100) NOT NULL,
    negara VARCHAR(50)
);

--11. Tabel Film
CREATE TABLE film (
    id_film INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(150) NOT NULL,
    tahun_rilis INT,
    durasi_menit INT,
    harga_beli DECIMAL(10,2) NOT NULL,
    harga_sewa DECIMAL(10,2) NOT NULL,
    id_kategori INT,
    id_rating INT,
    id_studio INT,
    id_sutradara INT,
    FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori),
    FOREIGN KEY (id_rating) REFERENCES rating_usia(id_rating),
    FOREIGN KEY (id_studio) REFERENCES studio(id_studio),
    FOREIGN KEY (id_sutradara) REFERENCES sutradara(id_sutradara)
);

--12. Tabel Penghubung Film dan Aktor
CREATE TABLE film_aktor (
    id_film INT,
    id_aktor INT,
    peran VARCHAR(100),
    PRIMARY KEY (id_film, id_aktor),
    FOREIGN KEY (id_film) REFERENCES film(id_film) ON DELETE CASCADE,
    FOREIGN KEY (id_aktor) REFERENCES aktor(id_aktor) ON DELETE CASCADE
);

--13. Tabel Stok Ketersediaan Kualitas Film
CREATE TABLE film_kualitas (
    id_film INT,
    id_kualitas INT,
    tersedia TINYINT(1) DEFAULT 1,
    PRIMARY KEY (id_film, id_kualitas),
    FOREIGN KEY (id_film) REFERENCES film(id_film),
    FOREIGN KEY (id_kualitas) REFERENCES kualitas_video(id_kualitas)
);

--14. Tabel Invoice/Transaksi Utama
CREATE TABLE transaksi (
    id_transaksi INT AUTO_INCREMENT PRIMARY KEY,
    id_pengguna INT,
    id_metode INT,
    id_status INT,
    id_voucher INT NULL,
    tanggal_transaksi DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_bayar DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_pengguna) REFERENCES pengguna(id_pengguna),
    FOREIGN KEY (id_metode) REFERENCES metode_pembayaran(id_metode),
    FOREIGN KEY (id_status) REFERENCES status_transaksi(id_status),
    FOREIGN KEY (id_voucher) REFERENCES voucher(id_voucher)
);

--15. Detail Transaksi Item
CREATE TABLE detail_transaksi (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_transaksi INT,
    id_film INT,
    jenis_layanan ENUM('Sewa', 'Beli') NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_transaksi) REFERENCES transaksi(id_transaksi) ON DELETE CASCADE,
    FOREIGN KEY (id_film) REFERENCES film(id_film)
);

--16 Tabel Kontrak Sewa Aktif
CREATE TABLE sewa_film (
    id_sewa INT AUTO_INCREMENT PRIMARY KEY,
    id_pengguna INT,
    id_film INT,
    tanggal_mulai DATETIME NOT NULL,
    tanggal_selesai DATETIME NOT NULL,
    status_akses ENUM('Aktif', 'Kadaluarsa') DEFAULT 'Aktif',
    FOREIGN KEY (id_pengguna) REFERENCES pengguna(id_pengguna),
    FOREIGN KEY (id_film) REFERENCES film(id_film)
);

--17. Tabel Koleksi Setelah Dibeli
CREATE TABLE pustaka_film (
    id_pustaka INT AUTO_INCREMENT PRIMARY KEY,
    id_pengguna INT,
    id_film INT,
    tanggal_perolehan DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pengguna) REFERENCES pengguna(id_pengguna),
    FOREIGN KEY (id_film) REFERENCES film(id_film)
);

--18. Tabel Ulasan
CREATE TABLE ulasan (
    id_ulasan INT AUTO_INCREMENT PRIMARY KEY,
    id_pengguna INT,
    id_film INT,
    rating_skor INT CHECK (rating_skor BETWEEN 1 AND 5),
    komentar TEXT,
    tanggal_ulasan DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pengguna) REFERENCES pengguna(id_pengguna),
    FOREIGN KEY (id_film) REFERENCES film(id_film)
);

--19. Tabel Watchlist
CREATE TABLE watchlist (
    id_watchlist INT AUTO_INCREMENT PRIMARY KEY,
    id_pengguna INT,
    id_film INT,
    tanggal_ditambahkan DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pengguna) REFERENCES pengguna(id_pengguna),
    FOREIGN KEY (id_film) REFERENCES film(id_film)
);

--20. Tabel Audit
CREATE TABLE audit_log_aktivitas (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_pengguna INT NULL,
    aktivitas VARCHAR(255) NOT NULL,
    waktu_kejadian DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 1. Kategori (5 Data)
INSERT INTO kategori (nama_kategori, deskripsi) VALUES
('Aksi', 'Film dengan adu fisik dan ketegangan tinggi'),
('Komedi', 'Film yang mengundang gelak tawa'),
('Drama', 'Fokus pada pengembangan karakter dan emosional'),
('Sci-Fi', 'Fiksi ilmiah dan teknologi masa depan'),
('Horor', 'Memicu rasa takut dan adrenalin');

-- 2. Rating Usia (4 Data)
INSERT INTO rating_usia (kode_rating, deskripsi) VALUES
('SU', 'Semua Umur'),
('R13+', 'Remaja 13 tahun ke atas'),
('D17+', 'Dewasa 17 tahun ke atas'),
('D21+', 'Khusus Dewasa 21 tahun ke atas');

-- 3. Kualitas Video (3 Data)
INSERT INTO kualitas_video (resolusi, deskripsi) VALUES
('SD', 'Standard Definition 480p'),
('HD', 'High Definition 1080p'),
('4K Ultra HD', 'Ultra High Definition 2160p');

-- 4. Metode Pembayaran (4 Data)
INSERT INTO metode_pembayaran (nama_metode, tipe) VALUES
('Dompet Digital OVO', 'E-Wallet'),
('GoPay', 'E-Wallet'),
('Transfer Virtual Account Mandiri', 'Bank Transfer'),
('Kartu Kredit Visa', 'Credit Card');

-- 5. Status Transaksi (3 Data)
INSERT INTO status_transaksi (nama_status) VALUES
('Pending'),
('Sukses'),
('Gagal');

-- 6. Studio (5 Data)
INSERT INTO studio (nama_studio, negara_asal) VALUES
('Warner Bros Pictures', 'Amerika Serikat'),
('Marvel Studios', 'Amerika Serikat'),
('Studio Ghibli', 'Jepang'),
('CJ Entertainment', 'Korea Selatan'),
('Falcon Pictures', 'Indonesia');

-- 7. Sutradara (5 Data)
INSERT INTO sutradara (nama_sutradara) VALUES
('Christopher Nolan'),
('Steven Spielberg'),
('Hayao Miyazaki'),
('Bong Joon Ho'),
('Joko Anwar');

-- 8. Aktor (10 Data)
INSERT INTO aktor (nama_aktor, negara) VALUES
('Leonardo DiCaprio', 'Amerika Serikat'),
('Robert Downey Jr.', 'Amerika Serikat'),
('Tom Holland', 'Inggris'),
('Song Kang Ho', 'Korea Selatan'),
('Reza Rahadian', 'Indonesia'),
('Pevita Pearce', 'Indonesia'),
('Christian Bale', 'Inggris'),
('Scarlett Johansson', 'Amerika Serikat'),
('Ken Watanabe', 'Jepang'),
('Tara Basro', 'Indonesia');

-- 9. Pengguna (10 Data)
INSERT INTO pengguna (nama, email, pw, tanggal_daftar) VALUES
('Budi Sudarsono', 'budi@gmail.com', '$2y$10$e0MYzXy...', '2026-01-10 08:00:00'),
('Siti Aminah', 'siti@yahoo.com', '$2y$10$kJS81xL...', '2026-01-15 09:30:00'),
('Andi Wijaya', 'andi@gmail.com', '$2y$10$mN92kJz...', '2026-02-01 14:15:00'),
('Dewi Lestari', 'dewi@gmail.com', '$2y$10$pL91mKq...', '2026-02-12 19:45:00'),
('Rian Hidayat', 'rian@hotmail.com', '$2y$10$qO02nLx...', '2026-03-01 11:00:00'),
('Eka Putri', 'eka@gmail.com', '$2y$10$rP13oMz...', '2026-03-20 15:22:00'),
('Feri Setiawan', 'feri@gmail.com', '$2y$10$sQ24pNx...', '2026-04-05 10:10:00'),
('Gita Gutawa', 'gita@yahoo.com', '$2y$10$tR35qOx...', '2026-04-18 16:40:00'),
('Hendra Kurnia', 'hendra@gmail.com', '$2y$10$uS46rPx...', '2026-05-02 13:05:00'),
('Indah Permata', 'indah@gmail.com', '$2y$10$vT57sQx...', '2026-05-25 21:12:00');


-- 10. Voucher (3 Data)
INSERT INTO voucher (kode_voucher, potongan_persen, tanggal_kadaluarsa) VALUES
('SERUSTREAMING', 10, '2026-12-31'),
('NOPANIKUNTUNG', 20, '2026-08-30'),
('FILMKEREN', 15, '2026-10-15');

-- 11. Film (10 Data)
INSERT INTO film (judul, tahun_rilis, durasi_menit, harga_beli, harga_sewa, id_kategori, id_rating, id_studio, id_sutradara) VALUES
('Inception', 2010, 148, 120000.00, 35000.00, 4, 2, 1, 1),
('The Dark Knight', 2008, 152, 110000.00, 30000.00, 1, 2, 1, 1),
('Parasite', 2019, 132, 95000.00, 25000.00, 3, 3, 4, 4),
('Spirited Away', 2001, 125, 85000.00, 20000.00, 3, 1, 3, 3),
('Pengabdi Setan', 2017, 107, 75000.00, 18000.00, 5, 2, 5, 5),
('Interstellar', 2014, 169, 130000.00, 40000.00, 4, 2, 1, 1),
('Avengers: Endgame', 2019, 181, 150000.00, 45000.00, 1, 2, 2, 1),
('Gundala', 2019, 123, 70000.00, 15000.00, 1, 2, 5, 5),
('Comic 8', 2014, 94, 50000.00, 12000.00, 2, 2, 5, 5),
('Shutter Island', 2010, 138, 100000.00, 28000.00, 3, 3, 1, 2);

-- 12. Film Aktor (10 Data)
INSERT INTO film_aktor (id_film, id_aktor, peran) VALUES
(1, 1, 'Cobb'),
(1, 9, 'Saito'),
(2, 7, 'Bruce Wayne'),
(3, 4, 'Ki-taek'),
(5, 10, 'Ibu'),
(6, 1, 'Cooper'),
(7, 2, 'Tony Stark / Iron Man'),
(7, 3, 'Peter Parker / Spider-Man'),
(8, 5, 'Sancaka / Gundala'),
(8, 6, 'Wulan');

-- 13. Film Kualitas (10 Data)
INSERT INTO film_kualitas (id_film, id_kualitas, tersedia) VALUES
(1, 2, 1), (1, 3, 1),
(2, 2, 1), (3, 1, 1),
(5, 2, 1), (6, 3, 1),
(7, 2, 1), (7, 3, 1),
(8, 1, 1), (10, 2, 1);

-- 14. Transaksi (12 Data)
INSERT INTO transaksi (id_pengguna, id_metode, id_status, id_voucher, tanggal_transaksi, total_bayar) VALUES
(1, 1, 2, NULL, '2026-01-12 10:00:00', 35000.00),
(2, 2, 2, 1, '2026-01-16 11:30:00', 108000.00),
(3, 3, 2, NULL, '2026-02-05 15:00:00', 25000.00),
(4, 1, 2, NULL, '2026-02-20 20:10:00', 150000.00),
(5, 4, 3, NULL, '2026-03-02 09:00:00', 18000.00),
(6, 2, 2, NULL, '2026-03-22 16:45:00', 20000.00),
(1, 2, 2, NULL, '2026-04-01 19:00:00', 130000.00),
(7, 1, 2, NULL, '2026-04-10 14:00:00', 18000.00),
(8, 3, 2, 2, '2026-04-20 18:25:00', 56000.00),
(9, 1, 1, NULL, '2026-05-05 12:00:00', 40000.00),
(10, 2, 2, NULL, '2026-05-26 22:00:00', 12000.00),
(2, 1, 2, NULL, '2026-06-01 10:00:00', 30000.00);

-- 15. Detail Transaksi (12 Data)
INSERT INTO detail_transaksi (id_transaksi, id_film, jenis_layanan, subtotal) VALUES
(1, 1, 'Sewa', 35000.00),
(2, 1, 'Beli', 120000.00),
(3, 3, 'Sewa', 25000.00),
(4, 7, 'Beli', 150000.00),
(5, 5, 'Sewa', 18000.00),
(6, 4, 'Sewa', 20000.00),
(7, 6, 'Beli', 130000.00),
(8, 5, 'Sewa', 18000.00),
(9, 8, 'Beli', 70000.00),
(10, 6, 'Sewa', 40000.00),
(11, 9, 'Sewa', 12000.00),
(12, 2, 'Sewa', 30000.00);

-- 16. Sewa Film (8 Data)
INSERT INTO sewa_film (id_pengguna, id_film, tanggal_mulai, tanggal_selesai, status_akses) VALUES
(1, 1, '2026-01-12 10:00:00', '2026-01-14 10:00:00', 'Kadaluarsa'),
(3, 3, '2026-02-05 15:00:00', '2026-02-07 15:00:00', 'Kadaluarsa'),
(6, 4, '2026-03-22 16:45:00', '2026-03-24 16:45:00', 'Kadaluarsa'),
(7, 5, '2026-04-10 14:00:00', '2026-04-12 14:00:00', 'Kadaluarsa'),
(10, 9, '2026-05-26 22:00:00', '2026-05-28 22:00:00', 'Kadaluarsa'),
(2, 2, '2026-06-01 10:00:00', '2026-06-03 10:00:00', 'Kadaluarsa');

-- 17. Pustaka Film (3 Data)
INSERT INTO pustaka_film (id_pengguna, id_film, tanggal_perolehan) VALUES
(2, 1, '2026-01-16 11:30:00'),
(4, 7, '2026-02-20 20:10:00'),
(1, 6, '2026-04-01 19:00:00');

-- 18. Ulasan (100 Data)
INSERT INTO ulasan (id_pengguna, id_film, rating_skor, komentar) VALUES
(1, 1, 5, 'Film yang sangat mengesankan! Plotnya rumit tapi memuaskan.'),
(2, 2, 4, 'Bagus, tapi durasinya agak kepanjangan.'),
(3, 3, 5, 'Sangat cerdas dan penuh kejutan.'),
(4, 4, 4, 'Animasi yang indah dan menyentuh hati.'),
(5, 5, 5, 'Horor lokal yang menegangkan, saya merinding.'),
(6, 6, 4, 'Visual luar biasa, tapi agak rumit secara ilmiah.'),
(7, 7, 5, 'Epik! Akhir yang memuaskan untuk para penggemar.'),
(8, 8, 3, 'Cukup bagus, tapi menurut saya biasa saja.'),
(9, 9, 4, 'Komedi ringan yang menghibur.'),
(10, 10, 5, 'Plot twist yang mengejutkan!');

-- 19. Watchlist (5 Data)
INSERT INTO watchlist (id_pengguna, id_film) VALUES
(1, 3), (1, 5), (2, 6), (5, 1), (8, 7);

-- 20. Audit Log Aktivitas (2 Data)
INSERT INTO audit_log_aktivitas (id_pengguna, aktivitas) VALUES
(1, 'Melakukan pendaftaran akun baru via aplikasi'),
(2, 'Melakukan pembaharuan kata sandi');