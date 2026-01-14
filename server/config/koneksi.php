<?php
$host = "localhost";
$user = "Aldi";
$pass = "Nk!*710Apollo"; 
$db   = "db_quiz_coding"; 

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
