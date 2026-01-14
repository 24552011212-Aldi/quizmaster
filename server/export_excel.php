<?php
session_start();
include 'config/koneksi.php';
include_once 'auth_check.php';
checkLogin();
if (!isAdmin()) {
    echo "Akses ditolak. Hanya admin yang dapat mengekspor data.";
    exit();
}

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Data_Player.xls");

echo "Rank\tUsername\tTotal Skor\tTotal Kuis\tTerakhir Main\n";

$query = mysqli_query($conn, "SELECT u.username, IFNULL(SUM(l.skor_akhir),0) as total_skor, COUNT(l.id) as total_kuis, MAX(l.tanggal_main) as terakhir_main
    FROM users u
    LEFT JOIN leaderboard l ON u.id = l.user_id
    WHERE u.role = 'player'
    GROUP BY u.id
    ORDER BY total_skor DESC");

$rank = 1;
while ($data = mysqli_fetch_assoc($query)) {
    echo $rank . "\t" . $data['username'] . "\t" . $data['total_skor'] . "\t" . $data['total_kuis'] . "\t" . ($data['terakhir_main'] ?? '-') . "\n";
    $rank++;
}
