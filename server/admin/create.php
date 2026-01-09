<?php
header("Content-Type: application/json");
include "../config/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $stmt = $conn->prepare("INSERT INTO quizzes (judul, materi, soal, snippet, jawaban_benar, score, opsi_a, opsi_b, opsi_c, opsi_d) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $skor = (int)($input['score'] ?? 10);
    $stmt->bind_param(
        "sssssissss",
        $input['judul'],
        $input['materi'],
        $input['soal'],
        $input['snippet'],
        $input['jawaban_benar'],
        $skor,
        $input['opsi_a'],
        $input['opsi_b'],
        $input['opsi_c'],
        $input['opsi_d']
    );
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => $stmt->error]);
    }
    exit;
}
echo json_encode(["error" => "Invalid method"]);