<?php

ob_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

error_reporting(0);
ini_set('display_errors', 0);

include "config/koneksi.php";

// cek koneksi berhasil
if (!$conn) {
    if (ob_get_length()) ob_clean();
    echo json_encode(["error" => "Koneksi database gagal"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// Bersihkan buffer sebelum output
if (ob_get_length()) ob_clean();

switch ($method) {
    case 'GET':
        $result = mysqli_query($conn, "SELECT * FROM quizzes ORDER BY id DESC");
        $data = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $row['id'] = (int)$row['id'];
                $row['score'] = (int)$row['score'];
                $data[] = $row;
            }
        }
        echo json_encode($data);
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);

        $stmt = $conn->prepare("INSERT INTO quizzes (judul, materi, soal, snippet, jawaban_benar, score, opsi_a, opsi_b, opsi_c, opsi_d) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $skor = (int)($input['score'] ?? 10);

        // "ssssssissi" artinya string, string, string, string, string, integer, string, string, string, string
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
        break;

    case 'DELETE':
        $id = (int)$_GET['id'];

        mysqli_query($conn, "DELETE FROM quiz_history WHERE quiz_id = $id");

        if (mysqli_query($conn, "DELETE FROM quizzes WHERE id = $id")) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => mysqli_error($conn)]);
        }
        break;
}
exit;
