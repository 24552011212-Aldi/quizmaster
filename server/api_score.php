<?php
session_start();
include "config/koneksi.php";
include_once "auth_check.php";
header('Content-Type: application/json');
checkLogin();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'player') {
    echo json_encode(['status' => 'error', 'message' => 'User tidak terautentikasi']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $user_id    = $_SESSION['user_id'];
    $skor_akhir = mysqli_real_escape_string($conn, $data['skor_akhir']);
    $tanggal    = date('Y-m-d H:i:s');

    $quiz_ids   = $data['quiz_ids'];

    // Simpan ke tabel Leaderboard
    $query_leaderboard = "INSERT INTO leaderboard (user_id, skor_akhir, tanggal_main) 
                          VALUES ('$user_id', '$skor_akhir', '$tanggal')";
    $res_leaderboard = mysqli_query($conn, $query_leaderboard);

    // Simpan semua soal yang ada di materi tersebut ke Quiz History
    $success_history = true;
    if (is_array($quiz_ids)) {
        foreach ($quiz_ids as $q_id) {
            $q_id = mysqli_real_escape_string($conn, $q_id);

            $cek = mysqli_query($conn, "SELECT id FROM quiz_history WHERE user_id = '$user_id' AND quiz_id = '$q_id'");

            if (mysqli_num_rows($cek) == 0) {
                $query_history = "INSERT INTO quiz_history (user_id, quiz_id, selesai_pada) 
                                  VALUES ('$user_id', '$q_id', '$tanggal')";
                if (!mysqli_query($conn, $query_history)) {
                    $success_history = false;
                }
            }
        }
    }

    if ($res_leaderboard && $success_history) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Mission Cleared! Progress dan skor berhasil diperbarui.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal memperbarui database: ' . mysqli_error($conn)
        ]);
    }
    exit();
}
