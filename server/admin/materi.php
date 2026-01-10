<?php
header("Content-Type: application/json");
include "../config/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = mysqli_query($conn, "SELECT DISTINCT materi FROM quizzes WHERE materi IS NOT NULL AND materi != '' ORDER BY materi ASC");
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row['materi'];
    }
    echo json_encode($data);
    exit;
}
echo json_encode(["error" => "Invalid method"]);
