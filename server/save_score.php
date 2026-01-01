<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

session_start();
include "config/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    // Ambil data dari session dan input
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    $skor = $data->score;

    // Simpan ke database
    $query = "INSERT INTO leaderboard (user_id, username, skor_akhir) VALUES ('$user_id', '$username', '$skor')";

    if (mysqli_query($conn, $query)) {
        echo json_encode(["message" => "Skor berhasil disimpan ke leaderboard!"]);
    } else {
        echo json_encode(["message" => "Gagal simpan skor"]);
    }
}
