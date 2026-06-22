<?php
session_start();
$pdo = require 'b-koneksi.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nama'])) {
    $nama = trim($_POST['nama']);
    if (empty($nama)) {
        echo json_encode(['success' => false, 'message' => 'Nama kategori tidak boleh kosong.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id_kategori FROM kategori WHERE nama_kategori = ?");
        $stmt->execute([$nama]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Kategori sudah ada.']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?, '')");
        $stmt->execute([$nama]);
        $id = $pdo->lastInsertId();
        echo json_encode(['success' => true, 'id' => $id]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Permintaan tidak valid.']);
}
