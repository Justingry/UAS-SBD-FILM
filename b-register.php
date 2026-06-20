<?php

$conn = require 'b-koneksi.php';

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

$nama = $_POST('nama');
$email = $_POST('email');
$password = password_hash($_POST['pw'], PASSWORD_DEFAULT);

$sql = "INSERT INTO pengguna (nama, email, pw)
        VALUE ('$nama', '$email', '$password')";

if (mysqli_query($conn, $sql)) {
    echo "Registrasi berhasil";
} else {
    echo "Error: " . mysqli_error($conn);
}

mysqli_close($conn);

?>