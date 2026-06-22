-- =============================================================
--  UAS SISTEM BASIS DATA – "Bioskop Film Digital"
--  Program Studi Sistem Informasi | TA 2025/2026
-- =============================================================
--  File ini berisi:
--  1. DDL  – Pembuatan 20 tabel
--  2. DML  – Data dummy (100+ baris)
--  3. Trigger  (3 trigger)
--  4. Function (2 UDF)
--  5. Aggregate Function (5 query)
--  6. TCL  – COMMIT & ROLLBACK
--  7. Table Locking
--  8. Stored Procedure (3 procedure)
--  9. Cursor (1 cursor)
-- 10. Reporting (5 laporan)
-- =============================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP DATABASE IF EXISTS UAS;
CREATE DATABASE UAS CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE UAS;
SET FOREIGN_KEY_CHECKS = 1;

-- ==================================================
-- BAB IV – DDL : PEMBUATAN TABEL
-- ==================================================

-- 1. Tabel Kategori
CREATE TABLE kategori (
    id_kategori INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(50) NOT NULL,
    deskripsi TEXT
);

-- 2. Tabel Rating Usia
CREATE TABLE rating_usia (
    id_rating INT AUTO_INCREMENT PRIMARY KEY,
    kode_rating VARCHAR(10) NOT NULL UNIQUE,
    deskripsi VARCHAR(255)
);

-- 3. Tabel Kualitas Video
CREATE TABLE kualitas_video (
    id_kualitas INT AUTO_INCREMENT PRIMARY KEY,
    resolusi VARCHAR(20) NOT NULL,
    deskripsi VARCHAR(100)
);

-- 4. Tabel Metode Pembayaran
CREATE TABLE metode_pembayaran (
    id_metode INT AUTO_INCREMENT PRIMARY KEY,
    nama_metode VARCHAR(50) NOT NULL,
    tipe VARCHAR(30)
);

-- 5. Tabel Status Transaksi
CREATE TABLE status_transaksi (
    id_status INT AUTO_INCREMENT PRIMARY KEY,
    nama_status VARCHAR(30) NOT NULL UNIQUE
);

-- 6. Tabel Pengguna
CREATE TABLE pengguna (
    id_pengguna INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    pw VARCHAR(255) NOT NULL,
    tanggal_daftar DATETIME DEFAULT CURRENT_TIMESTAMP,
    status_akun VARCHAR(20) DEFAULT 'Aktif'
);

-- 7. Tabel Voucher
CREATE TABLE voucher (
    id_voucher INT AUTO_INCREMENT PRIMARY KEY,
    kode_voucher VARCHAR(20) NOT NULL UNIQUE,
    potongan_persen INT NOT NULL,
    tanggal_kadaluarsa DATE
);

-- 8. Tabel Studio Produksi
CREATE TABLE studio (
    id_studio INT AUTO_INCREMENT PRIMARY KEY,
    nama_studio VARCHAR(100) NOT NULL,
    negara_asal VARCHAR(50)
);

-- 9. Tabel Sutradara
CREATE TABLE sutradara (
    id_sutradara INT AUTO_INCREMENT PRIMARY KEY,
    nama_sutradara VARCHAR(100) NOT NULL,
    tanggal_lahir DATE
);

-- 10. Tabel Aktor
CREATE TABLE aktor (
    id_aktor INT AUTO_INCREMENT PRIMARY KEY,
    nama_aktor VARCHAR(100) NOT NULL,
    negara VARCHAR(50)
);

-- 11. Tabel Film
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
    FOREIGN KEY (id_rating)   REFERENCES rating_usia(id_rating),
    FOREIGN KEY (id_studio)   REFERENCES studio(id_studio),
    FOREIGN KEY (id_sutradara) REFERENCES sutradara(id_sutradara)
);

-- 12. Tabel Film–Aktor (many-to-many)
CREATE TABLE film_aktor (
    id_film  INT,
    id_aktor INT,
    peran    VARCHAR(100),
    PRIMARY KEY (id_film, id_aktor),
    FOREIGN KEY (id_film)  REFERENCES film(id_film)  ON DELETE CASCADE,
    FOREIGN KEY (id_aktor) REFERENCES aktor(id_aktor) ON DELETE CASCADE
);

-- 13. Tabel Ketersediaan Kualitas Film
CREATE TABLE film_kualitas (
    id_film     INT,
    id_kualitas INT,
    tersedia    TINYINT(1) DEFAULT 1,
    PRIMARY KEY (id_film, id_kualitas),
    FOREIGN KEY (id_film)     REFERENCES film(id_film),
    FOREIGN KEY (id_kualitas) REFERENCES kualitas_video(id_kualitas)
);

-- 14. Tabel Transaksi Utama
CREATE TABLE transaksi (
    id_transaksi      INT AUTO_INCREMENT PRIMARY KEY,
    id_pengguna       INT,
    id_metode         INT,
    id_status         INT,
    id_voucher        INT NULL,
    tanggal_transaksi DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_bayar       DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_pengguna) REFERENCES pengguna(id_pengguna),
    FOREIGN KEY (id_metode)   REFERENCES metode_pembayaran(id_metode),
    FOREIGN KEY (id_status)   REFERENCES status_transaksi(id_status),
    FOREIGN KEY (id_voucher)  REFERENCES voucher(id_voucher)
);

-- 15. Tabel Detail Transaksi
CREATE TABLE detail_transaksi (
    id_detail    INT AUTO_INCREMENT PRIMARY KEY,
    id_transaksi INT,
    id_film      INT,
    jenis_layanan ENUM('Sewa','Beli') NOT NULL,
    subtotal     DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_transaksi) REFERENCES transaksi(id_transaksi) ON DELETE CASCADE,
    FOREIGN KEY (id_film)      REFERENCES film(id_film)
);

-- 16. Tabel Kontrak Sewa Aktif
CREATE TABLE sewa_film (
    id_sewa          INT AUTO_INCREMENT PRIMARY KEY,
    id_pengguna      INT,
    id_film          INT,
    tanggal_mulai    DATETIME NOT NULL,
    tanggal_selesai  DATETIME NOT NULL,
    status_akses     ENUM('Aktif','Kadaluarsa') DEFAULT 'Aktif',
    FOREIGN KEY (id_pengguna) REFERENCES pengguna(id_pengguna),
    FOREIGN KEY (id_film)     REFERENCES film(id_film)
);

-- 17. Tabel Koleksi Pribadi (setelah beli)
CREATE TABLE pustaka_film (
    id_pustaka        INT AUTO_INCREMENT PRIMARY KEY,
    id_pengguna       INT,
    id_film           INT,
    tanggal_perolehan DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pengguna) REFERENCES pengguna(id_pengguna),
    FOREIGN KEY (id_film)     REFERENCES film(id_film)
);

-- 18. Tabel Ulasan
CREATE TABLE ulasan (
    id_ulasan      INT AUTO_INCREMENT PRIMARY KEY,
    id_pengguna    INT,
    id_film        INT,
    rating_skor    INT CHECK (rating_skor BETWEEN 1 AND 5),
    komentar       TEXT,
    tanggal_ulasan DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pengguna) REFERENCES pengguna(id_pengguna),
    FOREIGN KEY (id_film)     REFERENCES film(id_film)
);

-- 19. Tabel Watchlist
CREATE TABLE watchlist (
    id_watchlist       INT AUTO_INCREMENT PRIMARY KEY,
    id_pengguna        INT,
    id_film            INT,
    tanggal_ditambahkan DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pengguna) REFERENCES pengguna(id_pengguna),
    FOREIGN KEY (id_film)     REFERENCES film(id_film)
);

-- 20. Tabel Audit Log Aktivitas
CREATE TABLE audit_log_aktivitas (
    id_log        INT AUTO_INCREMENT PRIMARY KEY,
    id_pengguna   INT NULL,
    aktivitas     VARCHAR(255) NOT NULL,
    waktu_kejadian DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ==================================================
-- BAB IV – DML : DATA AWAL
-- ==================================================

-- Kategori
INSERT INTO kategori (nama_kategori, deskripsi) VALUES
('Aksi',   'Film dengan adu fisik dan ketegangan tinggi'),
('Komedi',  'Film yang mengundang gelak tawa'),
('Drama',   'Fokus pada pengembangan karakter dan emosional'),
('Sci-Fi',  'Fiksi ilmiah dan teknologi masa depan'),
('Horor',   'Memicu rasa takut dan adrenalin');

-- Rating Usia
INSERT INTO rating_usia (kode_rating, deskripsi) VALUES
('SU',   'Semua Umur'),
('R13+', 'Remaja 13 tahun ke atas'),
('D17+', 'Dewasa 17 tahun ke atas'),
('D21+', 'Khusus Dewasa 21 tahun ke atas');

-- Kualitas Video
INSERT INTO kualitas_video (resolusi, deskripsi) VALUES
('SD',         'Standard Definition 480p'),
('HD',         'High Definition 1080p'),
('4K Ultra HD','Ultra High Definition 2160p');

-- Metode Pembayaran
INSERT INTO metode_pembayaran (nama_metode, tipe) VALUES
('Dompet Digital OVO',             'E-Wallet'),
('GoPay',                          'E-Wallet'),
('Transfer Virtual Account Mandiri','Bank Transfer'),
('Kartu Kredit Visa',              'Credit Card');

-- Status Transaksi
INSERT INTO status_transaksi (nama_status) VALUES
('Pending'),('Sukses'),('Gagal');

-- Studio
INSERT INTO studio (nama_studio, negara_asal) VALUES
('Warner Bros Pictures','Amerika Serikat'),
('Marvel Studios',      'Amerika Serikat'),
('Studio Ghibli',       'Jepang'),
('CJ Entertainment',    'Korea Selatan'),
('Falcon Pictures',     'Indonesia');

-- Sutradara
INSERT INTO sutradara (nama_sutradara, tanggal_lahir) VALUES
('Christopher Nolan','1970-07-30'),
('Steven Spielberg', '1946-12-18'),
('Hayao Miyazaki',   '1941-01-05'),
('Bong Joon Ho',     '1969-09-14'),
('Joko Anwar',       '1976-01-03');

-- Aktor
INSERT INTO aktor (nama_aktor, negara) VALUES
('Leonardo DiCaprio',  'Amerika Serikat'),
('Robert Downey Jr.',  'Amerika Serikat'),
('Tom Holland',        'Inggris'),
('Song Kang Ho',       'Korea Selatan'),
('Reza Rahadian',      'Indonesia'),
('Pevita Pearce',      'Indonesia'),
('Christian Bale',     'Inggris'),
('Scarlett Johansson', 'Amerika Serikat'),
('Ken Watanabe',       'Jepang'),
('Tara Basro',         'Indonesia');

-- Pengguna (password di-hash ber-bcrypt)
INSERT INTO pengguna (nama, email, pw, tanggal_daftar) VALUES
('Budi Sudarsono', 'budi@gmail.com',  '$2y$10$e0MYzXyABcd1234567890uABCDEFGHIJKLMNOPQRSTUVWXYZabcde', '2026-01-10 08:00:00'),
('Siti Aminah',    'siti@yahoo.com',  '$2y$10$kJS81xLBBcd1234567890uABCDEFGHIJKLMNOPQRSTUVWXYZabcde', '2026-01-15 09:30:00'),
('Andi Wijaya',    'andi@gmail.com',  '$2y$10$mN92kJzCBcd1234567890uABCDEFGHIJKLMNOPQRSTUVWXYZabcde', '2026-02-01 14:15:00'),
('Dewi Lestari',   'dewi@gmail.com',  '$2y$10$pL91mKqDBcd1234567890uABCDEFGHIJKLMNOPQRSTUVWXYZabcde', '2026-02-12 19:45:00'),
('Rian Hidayat',   'rian@hotmail.com','$2y$10$qO02nLxEBcd1234567890uABCDEFGHIJKLMNOPQRSTUVWXYZabcde', '2026-03-01 11:00:00'),
('Eka Putri',      'eka@gmail.com',   '$2y$10$rP13oMzFBcd1234567890uABCDEFGHIJKLMNOPQRSTUVWXYZabcde', '2026-03-20 15:22:00'),
('Feri Setiawan',  'feri@gmail.com',  '$2y$10$sQ24pNxGBcd1234567890uABCDEFGHIJKLMNOPQRSTUVWXYZabcde', '2026-04-05 10:10:00'),
('Gita Gutawa',    'gita@yahoo.com',  '$2y$10$tR35qOxHBcd1234567890uABCDEFGHIJKLMNOPQRSTUVWXYZabcde', '2026-04-18 16:40:00'),
('Hendra Kurnia',  'hendra@gmail.com','$2y$10$uS46rPxIBcd1234567890uABCDEFGHIJKLMNOPQRSTUVWXYZabcde', '2026-05-02 13:05:00'),
('Indah Permata',  'indah@gmail.com', '$2y$10$vT57sQxJBcd1234567890uABCDEFGHIJKLMNOPQRSTUVWXYZabcde', '2026-05-25 21:12:00');

-- Voucher
INSERT INTO voucher (kode_voucher, potongan_persen, tanggal_kadaluarsa) VALUES
('SERUSTREAMING', 10, '2026-12-31'),
('NOPANIKUNTUNG', 20, '2026-08-30'),
('FILMKEREN',     15, '2026-10-15');

-- Film
INSERT INTO film (judul, tahun_rilis, durasi_menit, harga_beli, harga_sewa, id_kategori, id_rating, id_studio, id_sutradara) VALUES
('Inception',          2010, 148, 120000, 35000, 4, 2, 1, 1),
('The Dark Knight',    2008, 152, 110000, 30000, 1, 2, 1, 1),
('Parasite',           2019, 132,  95000, 25000, 3, 3, 4, 4),
('Spirited Away',      2001, 125,  85000, 20000, 3, 1, 3, 3),
('Pengabdi Setan',     2017, 107,  75000, 18000, 5, 2, 5, 5),
('Interstellar',       2014, 169, 130000, 40000, 4, 2, 1, 1),
('Avengers: Endgame',  2019, 181, 150000, 45000, 1, 2, 2, 1),
('Gundala',            2019, 123,  70000, 15000, 1, 2, 5, 5),
('Comic 8',            2014,  94,  50000, 12000, 2, 2, 5, 5),
('Shutter Island',     2010, 138, 100000, 28000, 3, 3, 1, 2);

-- Film–Aktor
INSERT INTO film_aktor (id_film, id_aktor, peran) VALUES
(1, 1, 'Cobb'),(1, 9, 'Saito'),
(2, 7, 'Bruce Wayne'),
(3, 4, 'Ki-taek'),
(5, 10,'Ibu'),
(6, 1, 'Cooper'),
(7, 2, 'Tony Stark / Iron Man'),(7, 3, 'Peter Parker / Spider-Man'),
(8, 5, 'Sancaka / Gundala'),(8, 6, 'Wulan');

-- Film–Kualitas
INSERT INTO film_kualitas (id_film, id_kualitas, tersedia) VALUES
(1,2,1),(1,3,1),(2,2,1),(3,1,1),(5,2,1),
(6,3,1),(7,2,1),(7,3,1),(8,1,1),(10,2,1);

-- Transaksi (12 baris)
INSERT INTO transaksi (id_pengguna, id_metode, id_status, id_voucher, tanggal_transaksi, total_bayar) VALUES
(1, 1, 2, NULL, '2026-01-12 10:00:00',  35000),
(2, 2, 2,    1, '2026-01-16 11:30:00', 108000),
(3, 3, 2, NULL, '2026-02-05 15:00:00',  25000),
(4, 1, 2, NULL, '2026-02-20 20:10:00', 150000),
(5, 4, 3, NULL, '2026-03-02 09:00:00',  18000),
(6, 2, 2, NULL, '2026-03-22 16:45:00',  20000),
(1, 2, 2, NULL, '2026-04-01 19:00:00', 130000),
(7, 1, 2, NULL, '2026-04-10 14:00:00',  18000),
(8, 3, 2,    2, '2026-04-20 18:25:00',  56000),
(9, 1, 1, NULL, '2026-05-05 12:00:00',  40000),
(10,2, 2, NULL, '2026-05-26 22:00:00',  12000),
(2, 1, 2, NULL, '2026-06-01 10:00:00',  30000);

-- Detail Transaksi
INSERT INTO detail_transaksi (id_transaksi, id_film, jenis_layanan, subtotal) VALUES
(1,  1, 'Sewa',  35000),
(2,  1, 'Beli', 120000),
(3,  3, 'Sewa',  25000),
(4,  7, 'Beli', 150000),
(5,  5, 'Sewa',  18000),
(6,  4, 'Sewa',  20000),
(7,  6, 'Beli', 130000),
(8,  5, 'Sewa',  18000),
(9,  8, 'Beli',  70000),
(10, 6, 'Sewa',  40000),
(11, 9, 'Sewa',  12000),
(12, 2, 'Sewa',  30000);

-- Sewa Film
INSERT INTO sewa_film (id_pengguna, id_film, tanggal_mulai, tanggal_selesai, status_akses) VALUES
(1, 1, '2026-01-12 10:00:00','2026-01-14 10:00:00','Kadaluarsa'),
(3, 3, '2026-02-05 15:00:00','2026-02-07 15:00:00','Kadaluarsa'),
(6, 4, '2026-03-22 16:45:00','2026-03-24 16:45:00','Kadaluarsa'),
(7, 5, '2026-04-10 14:00:00','2026-04-12 14:00:00','Kadaluarsa'),
(10,9, '2026-05-26 22:00:00','2026-05-28 22:00:00','Kadaluarsa'),
(2, 2, '2026-06-01 10:00:00','2026-06-03 10:00:00','Kadaluarsa');

-- Pustaka Film
INSERT INTO pustaka_film (id_pengguna, id_film, tanggal_perolehan) VALUES
(2, 1, '2026-01-16 11:30:00'),
(4, 7, '2026-02-20 20:10:00'),
(1, 6, '2026-04-01 19:00:00');

-- Ulasan (100 baris)
INSERT INTO ulasan (id_pengguna, id_film, rating_skor, komentar) VALUES
(1,1,5,'Alur ceritanya mind-blowing banget!'),
(2,1,4,'Visualnya luar biasa, tapi plotnya cukup rumit untuk ditonton sekali.'),
(5,1,5,'Karya masterpiece dari Nolan! Musik latar dan aktingnya sempurna.'),
(3,1,5,'Konsep mimpi dalam mimpi yang sangat jenius.'),
(4,1,4,'Butuh fokus tinggi nontonnya, tapi sangat memuaskan di akhir.'),
(6,1,4,'Akting Leonardo DiCaprio sangat mendalam di sini.'),
(7,1,5,'Efek visual kota yang melipat itu bener-bener ikonik.'),
(8,1,3,'Bagus sih, cuma bikin pusing kalau nonton pas lagi capek.'),
(9,1,4,'Ending-nya menggantung dan bikin debat sama temen.'),
(10,1,5,'Salah satu film fiksi ilmiah terbaik sepanjang masa.'),
(1,2,5,'Joker versi Heath Ledger tidak ada tandingannya. Sangat ikonik!'),
(3,2,5,'Film pahlawan super terbaik yang pernah dibuat. Realistis dan kelam.'),
(6,2,4,'Bagus banget, durasinya lumayan panjang tapi tidak terasa membosankan.'),
(2,2,5,'Sutradara Nolan bener-bener bikin standar film superhero jadi tinggi.'),
(4,2,5,'Pengembangan karakter Harvey Dent menjadi Two-Face sangat rapi.'),
(5,2,4,'Ketegangan terjaga dari awal sampai akhir film.'),
(7,2,5,'Setiap dialog Joker sangat bermakna dan filosofis.'),
(8,2,4,'Sisi detektif Batman di film ini sangat ditonjolkan.'),
(9,2,5,'Skoring musik dari Hans Zimmer membuat atmosfer Gotham begitu hidup.'),
(10,2,4,'Film yang wajib ditonton minimal sekali seumur hidup.'),
(3,3,4,'Kritik sosial yang sangat tajam dan jenius.'),
(4,3,5,'Layak menang Oscar. Komedi hitam yang berubah jadi ketegangan murni.'),
(8,3,3,'Bagus, tapi endingnya membuat saya agak sedikit tidak nyaman.'),
(1,3,5,'Sengkarut konflik kelas sosial yang dikemas dengan sangat satir.'),
(2,3,4,'Sinematografi dan simbolisme di film ini luar biasa detail.'),
(5,3,4,'Awalnya ketawa-tawa, pertengahan ke akhir bikin jantungan.'),
(6,3,5,'Pacing cerita sangat pas, tidak ada adegan yang mubazir.'),
(7,3,4,'Akting seluruh keluarga miskinnya sangat natural dan dapet banget.'),
(9,3,5,'Plot twist di ruang bawah tanah itu bener-bener tidak terduga.'),
(10,3,4,'Film Korea Selatan yang sukses mendobrak pasar global.'),
(1,4,5,'Animasinya sangat indah dan penuh makna mendalam tentang kehidupan.'),
(7,4,4,'Sangat seru ditonton bersama keluarga, dunianya sangat ajaib.'),
(9,4,5,'Ghibli tidak pernah gagal membawa imajinasi penonton terbang tinggi.'),
(2,4,5,'Dunia magis yang diciptakan Hayao Miyazaki sangat detail dan indah.'),
(3,4,4,'Karakter No-Face sangat ikonik dan penuh dengan metafora.'),
(4,4,5,'Musik latarnya sangat menenangkan dan bikin nostalgia.'),
(5,4,4,'Petualangan Chihiro yang sangat menyentuh hati penonton.'),
(6,4,4,'Visual klasik gambar tangan yang tak lekang oleh waktu.'),
(8,4,5,'Menang Oscar emang gak bohong, ini film animasi terbaik.'),
(10,4,4,'Pesan moral tentang keserakahan manusia disampaikan dengan halus.'),
(7,5,4,'Serem banget set film dan skoring suaranya.'),
(2,5,5,'Horor Indonesia terbaik di dekade ini! Teror Ibu sangat membekas.'),
(6,5,3,'Seram di paruh pertama, tapi menjelang akhir tensinya agak menurun.'),
(1,5,4,'Suara lonceng Ibu bener-bener bikin merinding sampai kebawa mimpi.'),
(3,5,4,'Joko Anwar berhasil membangun atmosfer rumah tua yang sangat mencekam.'),
(4,5,4,'Akting Tara Basro sangat solid sebagai anak sulung.'),
(5,5,5,'Jump scare di film ini tidak murahan, eksekusinya cerdas.'),
(8,5,3,'Bagus, tapi saya lebih suka versi originalnya tahun 1980.'),
(9,5,4,'Sinematografinya juara untuk ukuran film horor lokal.'),
(10,5,4,'Berhasil menaikkan standar film horor di Indonesia.'),
(3,6,5,'Sains dan emosinya seimbang. Bagian perpisahan ayah dan anak bikin nangis.'),
(10,6,4,'Secara visual bintang lima, tapi penjelasan fisikanya agak berat.'),
(1,6,5,'Teori relativitas waktu dikemas jadi cerita keluarga yang mengharukan.'),
(2,6,5,'Adegan di planet air dengan ombak raksasa itu sangat menegangkan.'),
(4,6,4,'Visual lubang hitam (Gargantua) yang sangat akurat secara ilmiah.'),
(5,6,5,'Nonton ini bikin kita merasa betapa kecilnya manusia di alam semesta.'),
(6,6,4,'Robot TARS memberikan sedikit humor segar di tengah situasi tegang.'),
(7,6,5,'Soundtrack organ dari Hans Zimmer bener-bener bikin merinding.'),
(8,6,4,'Ceritanya kompleks tapi emosinya nembus langsung ke penonton.'),
(9,6,4,'Endingnya fiksi banget, tapi secara keseluruhan ini film hebat.'),
(4,7,5,'I love you 3000! Epik banget tamatnya.'),
(5,7,5,'Penutup fase Marvel yang luar biasa emosional bagi para penggemar lama.'),
(9,7,4,'Pertempuran akhirnya sangat megah, walaupun ada beberapa plot hole kecil.'),
(1,7,5,'Adegan "Avengers Assemble" bikin merinding satu bioskop!'),
(2,7,4,'Konsep penjelajahan waktunya agak membingungkan, tapi tertutup aksi yang keren.'),
(3,7,5,'Pemberian konklusi cerita untuk Iron Man dan Captain America yang sangat pas.'),
(6,7,4,'Durasi 3 jam tidak terasa lama karena tensi ceritanya terjaga.'),
(7,7,5,'Air mata tumpah pas adegan pengorbanan di Vormir dan akhir film.'),
(8,7,4,'Pertarungan melawan Thanos kali ini terasa jauh lebih personal.'),
(10,7,5,'Puncak kejayaan Marvel Studios yang susah ditandingi lagi.'),
(4,8,4,'Awal yang menjanjikan untuk jagat sinema pahlawan super lokal.'),
(10,8,3,'Koreografi laganya keren, tapi transisi ceritanya terasa agak terburu-buru.'),
(1,8,4,'Suka dengan penggambaran Jakarta yang kumuh dan penuh ketimpangan sosial.'),
(2,8,3,'Asal-usul Sancaka diceritakan dengan baik, tapi pas jadi Gundala kurang porsinya.'),
(3,8,4,'Efek petirnya sudah lumayan rapi untuk ukuran industri lokal.'),
(5,8,4,'Abimana aktingnya matang sekali sebagai tokoh utama.'),
(6,8,3,'Karakter musuhnya terlalu banyak, jadi kurang fokus perkembangannya.'),
(7,8,4,'Sisi sosiopolitik yang dimasukkan Joko Anwar membuat film ini berbobot.'),
(8,8,4,'Adegan tarung di pabrik bener-bener intens dan kreatif.'),
(9,8,3,'Potensinya besar, semoga sekuelnya bisa lebih rapi lagi.'),
(7,9,4,'Komedi yang segar dengan aksi tembak-tembakan yang gak masuk akal tapi seru!'),
(1,9,4,'Nonton ini gak usah mikir keras, nikmati aja banyolan para komika.'),
(2,9,3,'Banyak joke yang lucu, tapi beberapa terasa agak garing.'),
(3,9,4,'Konsep perampokan bank dengan twist berlapis-lapis yang menghibur.'),
(4,9,4,'Perpaduan stand up comedy dan aksi yang cukup unik di Indonesia.'),
(5,9,3,'Pacing-nya terlalu cepat, kadang perpindahan adegannya bikin bingung.'),
(6,9,4,'Cameo dari artis-artis seniornya lumayan mengejutkan dan menghibur.'),
(8,9,4,'Gaya penyutradaraan Anggy Umbara yang komikal sangat terasa di sini.'),
(9,9,3,'Aksinya seru, efek ledakannya lumayan, pas buat hiburan ringan.'),
(10,9,4,'Salah satu pelopor film komedi aksi modern yang sukses di pasaran.'),
(8,10,5,'Plot twist di akhir film bener-bener membuat saya tercengang dan berpikir keras.'),
(1,10,5,'Suasana pulau dan rumah sakit jiwanya bener-bener bikin paranoid.'),
(2,10,4,'Akting Leonardo DiCaprio pas jadi detektif yang frustrasi keren banget.'),
(3,10,5,'Setiap petunjuk kecil di awal film baru terasa masuk akal pas nonton kedua kali.'),
(4,10,4,'Scorsese berhasil mengaduk-aduk psikologis penonton sepanjang film.'),
(5,10,4,'Sinematografi yang suram mendukung nuansa misteri neo-noir filmnya.'),
(6,10,5,'Salah satu psikologikal thriller terbaik dengan ending paling membekas.'),
(7,10,4,'Ketegangan dibangun lewat dialog dan tatapan mata para karakternya.'),
(9,10,4,'Butuh diskusi panjang sama temen buat paham kebenaran cerita aslinya.'),
(10,10,5,'Pertanyaan akhir dari Teddy bener-bener menutup film dengan sangat cerdas.');

-- Watchlist
INSERT INTO watchlist (id_pengguna, id_film) VALUES
(1,3),(1,5),(2,6),(5,1),(8,7);

-- Audit Log awal
INSERT INTO audit_log_aktivitas (id_pengguna, aktivitas) VALUES
(1, 'Melakukan pendaftaran akun baru via aplikasi'),
(2, 'Melakukan pembaharuan kata sandi');


-- ==================================================
-- BAB V – TRIGGER (Minimal 3)
-- ==================================================

DELIMITER $$

-- TRIGGER 1: Audit Log – catat setiap transaksi baru
CREATE TRIGGER trg_audit_transaksi_baru
AFTER INSERT ON transaksi
FOR EACH ROW
BEGIN
    INSERT INTO audit_log_aktivitas (id_pengguna, aktivitas, waktu_kejadian)
    VALUES (
        NEW.id_pengguna,
        CONCAT('Transaksi baru ID=', NEW.id_transaksi,
               ' sebesar Rp ', FORMAT(NEW.total_bayar, 0),
               ' dengan status ID=', NEW.id_status),
        NOW()
    );
END$$

-- TRIGGER 2: Setelah transaksi 'Beli' sukses, otomatis masukkan ke pustaka_film
CREATE TRIGGER trg_pustaka_setelah_beli
AFTER INSERT ON detail_transaksi
FOR EACH ROW
BEGIN
    DECLARE v_status INT;
    SELECT id_status INTO v_status
    FROM transaksi
    WHERE id_transaksi = NEW.id_transaksi;

    IF NEW.jenis_layanan = 'Beli' AND v_status = 2 THEN
        -- Cek agar tidak dobel
        IF NOT EXISTS (
            SELECT 1 FROM pustaka_film p
            JOIN transaksi t ON t.id_pengguna = p.id_pengguna
            WHERE p.id_film = NEW.id_film
              AND t.id_transaksi = NEW.id_transaksi
        ) THEN
            INSERT INTO pustaka_film (id_pengguna, id_film, tanggal_perolehan)
            SELECT id_pengguna, NEW.id_film, NOW()
            FROM transaksi
            WHERE id_transaksi = NEW.id_transaksi;
        END IF;
    END IF;
END$$

-- TRIGGER 3: Setelah INSERT sewa_film, catat ke audit log
CREATE TRIGGER trg_audit_sewa_baru
AFTER INSERT ON sewa_film
FOR EACH ROW
BEGIN
    INSERT INTO audit_log_aktivitas (id_pengguna, aktivitas, waktu_kejadian)
    VALUES (
        NEW.id_pengguna,
        CONCAT('Sewa film ID=', NEW.id_film,
               ' mulai ', NEW.tanggal_mulai,
               ' s.d. ', NEW.tanggal_selesai),
        NOW()
    );
END$$

DELIMITER ;


-- ==================================================
-- BAB V – FUNCTION (Minimal 2 UDF)
-- ==================================================

DELIMITER $$

-- FUNCTION 1: Hitung total pendapatan dari satu film tertentu
CREATE FUNCTION fn_total_pendapatan_film(p_id_film INT)
RETURNS DECIMAL(15,2)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE v_total DECIMAL(15,2) DEFAULT 0;
    SELECT COALESCE(SUM(dt.subtotal), 0)
    INTO   v_total
    FROM   detail_transaksi dt
    JOIN   transaksi         t  ON t.id_transaksi = dt.id_transaksi
    WHERE  dt.id_film   = p_id_film
      AND  t.id_status  = 2;   -- hanya transaksi Sukses
    RETURN v_total;
END$$

-- FUNCTION 2: Hitung rata-rata rating ulasan sebuah film
CREATE FUNCTION fn_rata_rating_film(p_id_film INT)
RETURNS DECIMAL(3,2)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE v_avg DECIMAL(3,2) DEFAULT 0;
    SELECT COALESCE(AVG(rating_skor), 0)
    INTO   v_avg
    FROM   ulasan
    WHERE  id_film = p_id_film;
    RETURN v_avg;
END$$

DELIMITER ;

-- Contoh pemanggilan UDF:
-- SELECT judul,
--        fn_total_pendapatan_film(id_film) AS total_pendapatan,
--        fn_rata_rating_film(id_film)      AS avg_rating
-- FROM film;


-- ==================================================
-- BAB V – AGGREGATE FUNCTION (Minimal 5 Query)
-- ==================================================

-- AGG 1: Total pendapatan seluruh transaksi sukses
SELECT SUM(total_bayar) AS total_pendapatan_keseluruhan
FROM transaksi
WHERE id_status = 2;

-- AGG 2: Rata-rata harga sewa film
SELECT AVG(harga_sewa) AS rata_harga_sewa,
       AVG(harga_beli) AS rata_harga_beli
FROM film;

-- AGG 3: Film termahal dan termurah (harga beli)
SELECT MAX(harga_beli) AS harga_beli_tertinggi,
       MIN(harga_beli) AS harga_beli_terendah
FROM film;

-- AGG 4: Jumlah total pengguna terdaftar
SELECT COUNT(*) AS total_pengguna FROM pengguna;

-- AGG 5: Jumlah transaksi per status
SELECT st.nama_status,
       COUNT(t.id_transaksi) AS jumlah_transaksi
FROM transaksi t
JOIN status_transaksi st ON st.id_status = t.id_status
GROUP BY st.nama_status
ORDER BY jumlah_transaksi DESC;


-- ==================================================
-- BAB V – TCL : COMMIT & ROLLBACK
-- ==================================================

-- Contoh 1: COMMIT – transaksi berhasil (proses pembelian film)
START TRANSACTION;
    INSERT INTO transaksi (id_pengguna, id_metode, id_status, tanggal_transaksi, total_bayar)
    VALUES (3, 1, 2, NOW(), 95000);
    SET @new_trx = LAST_INSERT_ID();
    INSERT INTO detail_transaksi (id_transaksi, id_film, jenis_layanan, subtotal)
    VALUES (@new_trx, 3, 'Beli', 95000);
COMMIT;

-- Contoh 2: ROLLBACK – pembatalan transaksi (misal validasi gagal)
START TRANSACTION;
    INSERT INTO transaksi (id_pengguna, id_metode, id_status, tanggal_transaksi, total_bayar)
    VALUES (5, 4, 3, NOW(), 0);   -- status 3 = Gagal
ROLLBACK;


-- ==================================================
-- BAB V – TABLE LOCKING (Demonstrasi Concurrency)
-- ==================================================

-- Lock WRITE: mencegah sesi lain membaca/menulis saat proses kritis
LOCK TABLES transaksi WRITE, detail_transaksi WRITE;
    -- operasi kritis: update status transaksi pending
    UPDATE transaksi SET id_status = 3
    WHERE id_status = 1
      AND tanggal_transaksi < DATE_SUB(NOW(), INTERVAL 1 HOUR);
UNLOCK TABLES;

-- Lock READ: sesi lain boleh baca, tapi tidak boleh tulis
LOCK TABLES film READ;
    -- operasi baca aman
    SELECT id_film, judul, harga_beli, harga_sewa FROM film;
UNLOCK TABLES;


-- ==================================================
-- BAB V – STORED PROCEDURE (Minimal 3)
-- ==================================================

DELIMITER $$

-- SP 1: Tambah Transaksi Pembelian / Penyewaan
CREATE PROCEDURE sp_tambah_transaksi(
    IN  p_id_pengguna  INT,
    IN  p_id_film      INT,
    IN  p_jenis        ENUM('Sewa','Beli'),
    IN  p_id_metode    INT,
    IN  p_id_voucher   INT,
    OUT p_id_transaksi INT,
    OUT p_pesan        VARCHAR(255)
)
BEGIN
    DECLARE v_harga     DECIMAL(10,2);
    DECLARE v_diskon    INT DEFAULT 0;
    DECLARE v_total     DECIMAL(10,2);
    DECLARE v_status    INT DEFAULT 2; -- Sukses

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_pesan = 'Transaksi gagal – terjadi kesalahan sistem.';
        SET p_id_transaksi = -1;
    END;

    -- Ambil harga sesuai jenis layanan
    IF p_jenis = 'Beli' THEN
        SELECT harga_beli INTO v_harga FROM film WHERE id_film = p_id_film;
    ELSE
        SELECT harga_sewa INTO v_harga FROM film WHERE id_film = p_id_film;
    END IF;

    -- Terapkan voucher jika ada
    IF p_id_voucher IS NOT NULL THEN
        SELECT potongan_persen INTO v_diskon
        FROM voucher
        WHERE id_voucher = p_id_voucher
          AND tanggal_kadaluarsa >= CURDATE();
    END IF;

    SET v_total = v_harga - (v_harga * v_diskon / 100);

    START TRANSACTION;

    INSERT INTO transaksi (id_pengguna, id_metode, id_status, id_voucher, tanggal_transaksi, total_bayar)
    VALUES (p_id_pengguna, p_id_metode, v_status, NULLIF(p_id_voucher, 0), NOW(), v_total);
    SET p_id_transaksi = LAST_INSERT_ID();

    INSERT INTO detail_transaksi (id_transaksi, id_film, jenis_layanan, subtotal)
    VALUES (p_id_transaksi, p_id_film, p_jenis, v_total);

    -- Jika sewa, buat kontrak sewa 2 hari
    IF p_jenis = 'Sewa' THEN
        INSERT INTO sewa_film (id_pengguna, id_film, tanggal_mulai, tanggal_selesai)
        VALUES (p_id_pengguna, p_id_film, NOW(), DATE_ADD(NOW(), INTERVAL 2 DAY));
    END IF;

    COMMIT;
    SET p_pesan = CONCAT('Transaksi berhasil. Total bayar: Rp ', FORMAT(v_total, 0));
END$$


-- SP 2: Update Status Sewa yang Sudah Kadaluarsa
CREATE PROCEDURE sp_update_status_sewa()
BEGIN
    UPDATE sewa_film
    SET status_akses = 'Kadaluarsa'
    WHERE status_akses = 'Aktif'
      AND tanggal_selesai < NOW();

    SELECT ROW_COUNT() AS baris_diupdate,
           'Status sewa kadaluarsa berhasil diperbarui' AS keterangan;
END$$


-- SP 3: Generate Laporan Pendapatan per Bulan
CREATE PROCEDURE sp_laporan_pendapatan_bulanan(
    IN p_tahun INT
)
BEGIN
    SELECT
        MONTH(t.tanggal_transaksi)  AS bulan,
        MONTHNAME(t.tanggal_transaksi) AS nama_bulan,
        COUNT(t.id_transaksi)       AS jumlah_transaksi,
        SUM(t.total_bayar)          AS total_pendapatan
    FROM transaksi t
    WHERE t.id_status = 2
      AND YEAR(t.tanggal_transaksi) = p_tahun
    GROUP BY MONTH(t.tanggal_transaksi), MONTHNAME(t.tanggal_transaksi)
    ORDER BY bulan;
END$$

DELIMITER ;

-- Contoh memanggil stored procedure:
-- CALL sp_laporan_pendapatan_bulanan(2026);
-- CALL sp_update_status_sewa();


-- ==================================================
-- BAB V – CURSOR (Minimal 1)
-- ==================================================

DELIMITER $$

-- Cursor: iterasi film dan cetak pendapatan masing-masing ke tabel sementara
CREATE PROCEDURE sp_ringkasan_pendapatan_film()
BEGIN
    DECLARE v_done      INT DEFAULT FALSE;
    DECLARE v_id_film   INT;
    DECLARE v_judul     VARCHAR(150);
    DECLARE v_pendapatan DECIMAL(15,2);

    -- Deklarasi cursor
    DECLARE cur_film CURSOR FOR
        SELECT id_film, judul FROM film ORDER BY id_film;

    -- Handler saat cursor habis
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_done = TRUE;

    -- Tabel sementara untuk hasil
    DROP TEMPORARY TABLE IF EXISTS tmp_ringkasan_film;
    CREATE TEMPORARY TABLE tmp_ringkasan_film (
        id_film    INT,
        judul      VARCHAR(150),
        pendapatan DECIMAL(15,2)
    );

    OPEN cur_film;

    loop_film: LOOP
        FETCH cur_film INTO v_id_film, v_judul;
        IF v_done THEN
            LEAVE loop_film;
        END IF;

        -- Hitung pendapatan menggunakan UDF yang sudah dibuat
        SET v_pendapatan = fn_total_pendapatan_film(v_id_film);

        INSERT INTO tmp_ringkasan_film VALUES (v_id_film, v_judul, v_pendapatan);
    END LOOP;

    CLOSE cur_film;

    -- Tampilkan hasil
    SELECT * FROM tmp_ringkasan_film ORDER BY pendapatan DESC;
END$$

DELIMITER ;

-- Panggil: CALL sp_ringkasan_pendapatan_film();


-- ==================================================
-- BAB VII – REPORTING (Minimal 5 Laporan)
-- ==================================================

-- LAPORAN 1: Pendapatan Bulanan Tahun 2026
SELECT
    DATE_FORMAT(t.tanggal_transaksi, '%Y-%m') AS periode,
    COUNT(t.id_transaksi)                      AS jumlah_transaksi,
    SUM(t.total_bayar)                         AS total_pendapatan
FROM transaksi t
WHERE t.id_status = 2
  AND YEAR(t.tanggal_transaksi) = 2026
GROUP BY DATE_FORMAT(t.tanggal_transaksi, '%Y-%m')
ORDER BY periode;

-- LAPORAN 2: Film Terlaris (berdasarkan jumlah transaksi sukses)
SELECT
    f.id_film,
    f.judul,
    k.nama_kategori,
    COUNT(dt.id_detail) AS total_transaksi,
    SUM(dt.subtotal)    AS total_pendapatan
FROM film f
JOIN detail_transaksi dt ON dt.id_film      = f.id_film
JOIN transaksi        t  ON t.id_transaksi  = dt.id_transaksi
JOIN kategori         k  ON k.id_kategori   = f.id_kategori
WHERE t.id_status = 2
GROUP BY f.id_film, f.judul, k.nama_kategori
ORDER BY total_transaksi DESC, total_pendapatan DESC;

-- LAPORAN 3: Pengguna Paling Aktif (paling banyak transaksi sukses)
SELECT
    p.id_pengguna,
    p.nama,
    p.email,
    COUNT(t.id_transaksi) AS jumlah_transaksi,
    SUM(t.total_bayar)    AS total_belanja
FROM pengguna p
JOIN transaksi t ON t.id_pengguna = p.id_pengguna
WHERE t.id_status = 2
GROUP BY p.id_pengguna, p.nama, p.email
ORDER BY jumlah_transaksi DESC, total_belanja DESC;

-- LAPORAN 4: Rata-rata Rating per Film (beserta jumlah ulasan)
SELECT
    f.id_film,
    f.judul,
    COUNT(u.id_ulasan)         AS jumlah_ulasan,
    ROUND(AVG(u.rating_skor),2) AS rata_rating,
    MAX(u.rating_skor)          AS rating_tertinggi,
    MIN(u.rating_skor)          AS rating_terendah
FROM film f
LEFT JOIN ulasan u ON u.id_film = f.id_film
GROUP BY f.id_film, f.judul
ORDER BY rata_rating DESC;

-- LAPORAN 5: Penggunaan Voucher & Efek Diskon
SELECT
    v.kode_voucher,
    v.potongan_persen,
    COUNT(t.id_transaksi)  AS kali_dipakai,
    SUM(t.total_bayar)     AS total_pendapatan_setelah_diskon
FROM voucher v
JOIN transaksi t ON t.id_voucher = v.id_voucher
WHERE t.id_status = 2
GROUP BY v.kode_voucher, v.potongan_persen
ORDER BY kali_dipakai DESC;


-- ==================================================
-- SELESAI – semua komponen UAS sudah terimplementasi
-- ==================================================
