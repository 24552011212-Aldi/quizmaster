<?php
header("Content-Type: application/json");
include_once "../../../server/config/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)($input['id'] ?? 0);
    $stmt = $conn->prepare("UPDATE quizzes SET judul=?, materi=?, soal=?, snippet=?, jawaban_benar=?, score=?, opsi_a=?, opsi_b=?, opsi_c=?, opsi_d=? WHERE id=?");
    $skor = (int)($input['score'] ?? 10);
    $stmt->bind_param(
        "sssssissssi",
        $input['judul'],
        $input['materi'],
        $input['soal'],
        $input['snippet'],
        $input['jawaban_benar'],
        $skor,
        $input['opsi_a'],
        $input['opsi_b'],
        $input['opsi_c'],
        $input['opsi_d'],
        $id
    );
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => $stmt->error]);
    }
    exit;
}
echo json_encode(["error" => "Invalid method"]);