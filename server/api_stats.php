<?php
session_start();
include "config/koneksi.php";

// Proteksi Admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Pastikan nama tabel 'leaderboard' atau 'scores' sesuai dengan di database Anda
$query = "SELECT 
            u.id, 
            u.username, 
            COUNT(l.id) as total_kuis, 
            IFNULL(SUM(l.skor_akhir), 0) as total_score,
            MAX(l.tanggal_main) as terakhir_main
          FROM users u
          LEFT JOIN leaderboard l ON u.id = l.user_id
          WHERE u.role = 'player'
          GROUP BY u.id
          ORDER BY total_score DESC";

$result = mysqli_query($conn, $query);
$data = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            'id' => $row['id'],
            'username' => $row['username'],
            'total_kuis' => (int)$row['total_kuis'],
            'total_score' => (int)$row['total_score'],
            'terakhir_main' => $row['terakhir_main'] ?? '-'
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($data);
