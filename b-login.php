<?php

session_start();

$conn = require "b-koneksi.php";

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

$email = $_POST['email'];
$password = $_POST['pw'];

$query = "SELECT * FROM pengguna WHERE email = ?";
$stmt = mysqli_prepare($conn, $query);

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) == 1) {
    $data = mysqli_fetch_assoc($result);
    if (password_verify($password, $data['id_pengguna'])) {
        $_SESSION['id_pengguna'] = $data['id_pengguna'];
        $_SESSION['nama'] = $data['nama'];
        $_SESSION['email'] = $data['email'];

        header("Location: beranda.php");
        exit();
    } else {
        echo "Password salah.";
    }
} else {
    echo "Email salah.";
}

?>