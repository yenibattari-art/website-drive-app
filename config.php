<?php
$mysqli = new mysqli("localhost", "root", "", "drive_app");

if ($mysqli->connect_errno) {
    die("Koneksi database gagal: " . $mysqli->connect_error);
}
?>
