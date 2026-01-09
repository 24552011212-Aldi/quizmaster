<?php
header("Content-Type: application/json");
include "../config/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = mysqli_query($conn, "SELECT * FROM quizzes ORDER BY id DESC");
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['id'] = (int)$row['id'];
        $row['score'] = (int)$row['score'];
        $data[] = $row;
    }
    echo json_encode($data);
    exit;
}
echo json_encode(["error" => "Invalid method"]);