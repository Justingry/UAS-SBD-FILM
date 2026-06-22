# 🎬 UAS-SBD-FILM — Bioskop Film Digital
**Proyek Akhir Mata Kuliah Sistem Basis Data**  
Program Studi Sistem Informasi | TA 2025/2026

---

## 📋 Deskripsi Proyek
Sistem manajemen bioskop digital berbasis web yang memungkinkan pengguna untuk **menyewa** dan **membeli** film secara online. Dibangun menggunakan **PHP + MySQL** sebagai bagian dari tugas UAS mata kuliah Sistem Basis Data.

---

## ✅ Checklist Spesifikasi UAS

| Komponen | Spesifikasi | Status |
|---|---|---|
| **Database** | Minimal 20 tabel | ✅ **20 tabel** |
| **Data** | Minimal 100 data | ✅ **100+ baris** (ulasan, pengguna, film, dll) |
| **Trigger** | Minimal 3 | ✅ **3 trigger** |
| **Function** | Minimal 2 UDF | ✅ **2 function** |
| **Aggregate Function** | Minimal 5 query | ✅ **5 query** |
| **TCL** | COMMIT & ROLLBACK | ✅ |
| **Table Locking** | Demonstrasi locking | ✅ |
| **Stored Procedure** | Minimal 3 | ✅ **3 procedure** |
| **Cursor** | Minimal 1 | ✅ **1 cursor** |
| **Reporting** | Minimal 5 laporan | ✅ **5 laporan** |

---

## 🗂️ Struktur Proyek

```
UAS-SBD-FILM/
├── sql/
│   └── uas_lengkap.sql        # DDL + DML + Trigger + SP + Cursor + Laporan
├── php/
│   ├── b-koneksi.php          # Koneksi database (PDO)
│   ├── b-login.php            # Proses autentikasi
│   ├── b-register.php         # Proses pendaftaran
│   ├── b-tambah-kategori.php  # AJAX tambah kategori
│   ├── login.php              # Halaman login
│   ├── register.php           # Halaman registrasi
│   ├── logout.php             # Proses logout
│   ├── film.php               # Daftar koleksi film
│   ├── tambah_film.php        # Form tambah film baru
│   ├── edit_film.php          # Form edit film
│   ├── kategori.php           # Manajemen kategori
│   ├── transaksi.php          # Riwayat & statistik transaksi
│   ├── proses_transaksi.php   # Form sewa/beli film
│   └── style.css              # Stylesheet
└── README.md
```

---

## 🗄️ Struktur Database (20 Tabel)

| No | Tabel | Deskripsi |
|----|-------|-----------|
| 1 | `kategori` | Genre/kategori film |
| 2 | `rating_usia` | Klasifikasi usia penonton |
| 3 | `kualitas_video` | Resolusi tersedia (SD/HD/4K) |
| 4 | `metode_pembayaran` | Cara bayar (e-wallet, transfer) |
| 5 | `status_transaksi` | Status (Pending/Sukses/Gagal) |
| 6 | `pengguna` | Akun pengguna |
| 7 | `voucher` | Kode diskon |
| 8 | `studio` | Studio produksi film |
| 9 | `sutradara` | Data sutradara |
| 10 | `aktor` | Data aktor |
| 11 | `film` | Data utama film |
| 12 | `film_aktor` | Relasi film ↔ aktor |
| 13 | `film_kualitas` | Ketersediaan resolusi tiap film |
| 14 | `transaksi` | Invoice transaksi |
| 15 | `detail_transaksi` | Item detail tiap transaksi |
| 16 | `sewa_film` | Kontrak sewa aktif |
| 17 | `pustaka_film` | Koleksi film yang dibeli |
| 18 | `ulasan` | Review & rating film |
| 19 | `watchlist` | Daftar tonton pengguna |
| 20 | `audit_log_aktivitas` | Log aktivitas sistem |

---

## ⚙️ Trigger

| # | Nama | Event | Fungsi |
|---|------|-------|--------|
| 1 | `trg_audit_transaksi_baru` | AFTER INSERT on `transaksi` | Catat setiap transaksi baru ke audit log |
| 2 | `trg_pustaka_setelah_beli` | AFTER INSERT on `detail_transaksi` | Otomatis masukkan film ke pustaka pribadi jika status Beli+Sukses |
| 3 | `trg_audit_sewa_baru` | AFTER INSERT on `sewa_film` | Catat kontrak sewa baru ke audit log |

---

## 🔧 Function

| # | Nama | Return | Fungsi |
|---|------|--------|--------|
| 1 | `fn_total_pendapatan_film(id_film)` | DECIMAL | Total pendapatan dari 1 film (transaksi sukses) |
| 2 | `fn_rata_rating_film(id_film)` | DECIMAL | Rata-rata skor ulasan 1 film |

---

## 📦 Stored Procedure

| # | Nama | Fungsi |
|---|------|--------|
| 1 | `sp_tambah_transaksi` | Proses sewa/beli dengan validasi voucher + commit/rollback |
| 2 | `sp_update_status_sewa` | Update otomatis sewa yang sudah kadaluarsa |
| 3 | `sp_laporan_pendapatan_bulanan` | Laporan pendapatan per bulan per tahun |

---

## 📊 Laporan (Reporting)

1. **Pendapatan Bulanan** — total transaksi sukses per bulan
2. **Film Terlaris** — ranking film berdasarkan jumlah transaksi & pendapatan
3. **Pengguna Paling Aktif** — ranking berdasarkan frekuensi & total belanja
4. **Rating Rata-rata Film** — avg, min, max skor ulasan tiap film
5. **Efektivitas Voucher** — berapa kali dipakai & pengaruh terhadap pendapatan

---

## 🚀 Cara Menjalankan

### 1. Persiapan Database
```bash
# Import file SQL ke MySQL
mysql -u root -p < sql/uas_lengkap.sql
```

### 2. Konfigurasi Koneksi
Edit `php/b-koneksi.php` sesuaikan:
```php
$host     = "localhost";
$username = "root";
$password = "";        // sesuaikan
$database = "UAS";
```

### 3. Jalankan dengan XAMPP / Laragon
- Letakkan folder `php/` di dalam `htdocs/uas-film/`
- Akses via browser: `http://localhost/uas-film/film.php`

---

## 🐛 Bug yang Diperbaiki

| File | Bug | Perbaikan |
|------|-----|-----------|
| `b-register.php` | `$_POST('nama')` — kurung biasa bukan array | Diganti `$_POST['nama']` |
| `b-register.php` | Menggunakan `mysqli_query()` tapi koneksi PDO | Diubah ke PDO `$pdo->prepare()` |
| `b-login.php` | `password_verify($password, $data['id_pengguna'])` — kolom salah | Diganti ke `$data['pw']` |
| `b-login.php` | Campur `mysqli_*` dengan koneksi PDO | Diubah konsisten ke PDO |
| `b-koneksi.php` | Tidak ada `return $pdo` | Ditambahkan agar `require` mengembalikan `$pdo` |

---

## 👥 Kontribusi Tim

| Nama | NIM | Tugas |
|------|-----|-------|
| _(isi nama)_ | _(isi NIM)_ | DDL & DML Database |
| _(isi nama)_ | _(isi NIM)_ | Trigger & Function |
| _(isi nama)_ | _(isi NIM)_ | Stored Procedure & Cursor |
| _(isi nama)_ | _(isi NIM)_ | PHP Frontend (film, transaksi) |
| _(isi nama)_ | _(isi NIM)_ | Reporting & Dokumentasi |

---

&copy; 2026 Proyek UAS Sistem Basis Data – Bioskop Film Digital
