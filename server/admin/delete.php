<?php
header("Content-Type: application/json");
include "../config/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    mysqli_query($conn, "DELETE FROM quiz_history WHERE quiz_id = $id");
    if (mysqli_query($conn, "DELETE FROM quizzes WHERE id = $id")) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => mysqli_error($conn)]);
    }
    exit;
}
echo json_encode(["error" => "Invalid method"]);