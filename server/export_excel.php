<?php
include 'config/koneksi.php';

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Leaderboard.xls");

echo "Rank \t Username \t Skor \t Tanggal \n";

$query = mysqli_query($conn, "SELECT users.username, leaderboard.skor, leaderboard.tanggal 
                              FROM leaderboard 
                              JOIN users ON leaderboard.user_id = users.id 
                              ORDER BY skor DESC");

while ($data = mysqli_fetch_assoc($query)) {
    echo $data['username'] . " \t " . $data['skor'] . " \t " . $data['tanggal'] . " \n";
}
