<?php
// Letakkan ini di baris pertama, jangan ada spasi atau baris kosong di atas tag PHP ini
ob_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// Matikan laporan error ke layar agar tidak merusak format JSON
error_reporting(0);
ini_set('display_errors', 0);

include "config/koneksi.php";

// Pastikan koneksi berhasil
if (!$conn) {
    if (ob_get_length()) ob_clean();
    echo json_encode(["error" => "Koneksi database gagal"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// Bersihkan buffer agar tidak ada karakter liar
if (ob_get_length()) ob_clean();

switch ($method) {
    case 'GET':
        $result = mysqli_query($conn, "SELECT * FROM quizzes ORDER BY id DESC");
        $data = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                // Konversi tipe data agar sesuai dengan frontend
                $row['id'] = (int)$row['id'];
                $row['score'] = (int)$row['score'];
                $data[] = $row;
            }
        }
        echo json_encode($data);
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);

        // Gunakan statement yang disiapkan
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


    // ... bagian DELETE di api_quiz.php ...
    case 'DELETE':
        $id = (int)$_GET['id'];

        // 1. Hapus history kuis ini dulu agar tidak terjadi Foreign Key Error
        mysqli_query($conn, "DELETE FROM quiz_history WHERE quiz_id = $id");

        // 2. Hapus dari leaderboard jika skor kuis ini dicatat spesifik
        // mysqli_query($conn, "DELETE FROM leaderboard WHERE quiz_id = $id"); 

        // 3. Baru hapus kuis utama
        if (mysqli_query($conn, "DELETE FROM quizzes WHERE id = $id")) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => mysqli_error($conn)]);
        }
        break;
}
exit;
