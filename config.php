<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_bapenda";

$conn = mysqli_connect("localhost", "root", "", "db_bapenda");
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
