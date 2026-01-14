<?php
header("Content-Type: application/json");
include "../config/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = mysqli_query($conn, "SELECT q.*, m.nama as nama_materi, q.id_materi FROM quizzes q LEFT JOIN materi m ON q.id_materi = m.id ORDER BY q.id DESC");
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['id'] = (int)$row['id'];
        $row['score'] = (int)$row['score'];
        $row['id_materi'] = isset($row['id_materi']) ? (int)$row['id_materi'] : null;
        $data[] = $row;
    }
    echo json_encode($data);
    exit;
}
echo json_encode(["error" => "Invalid method"]);
