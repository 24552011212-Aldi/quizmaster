<?php
include "../config/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Data dari form POST
    $judul = $_POST['judul'] ?? '';
    $soal = $_POST['soal'] ?? '';
    $snippet = $_POST['snippet'] ?? '';
    $jawaban_benar = $_POST['jawaban_benar'] ?? '';
    $score = (int)($_POST['score'] ?? 10);
    $opsi_a = $_POST['opsi_a'] ?? '';
    $opsi_b = $_POST['opsi_b'] ?? '';
    $opsi_c = $_POST['opsi_c'] ?? '';
    $opsi_d = $_POST['opsi_d'] ?? '';
    $id_materi = (int)($_POST['id_materi'] ?? 0);

    // Ambil nama materi dari tabel
    $materi = '';
    if ($id_materi) {
        $res = $conn->query("SELECT nama FROM materi WHERE id = $id_materi LIMIT 1");
        if ($row = $res->fetch_assoc()) {
            $materi = $row['nama'];
        }
    }

    $stmt = $conn->prepare("INSERT INTO quizzes (judul, materi, soal, snippet, jawaban_benar, score, opsi_a, opsi_b, opsi_c, opsi_d, id_materi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "ssssisssssi",
        $judul,
        $materi,
        $soal,
        $snippet,
        $jawaban_benar,
        $score,
        $opsi_a,
        $opsi_b,
        $opsi_c,
        $opsi_d,
        $id_materi
    );
    if ($stmt->execute()) {
        header('Location: ../../admin/index.php?success=1');
        exit;
    } else {
        echo json_encode(["success" => false, "error" => $stmt->error]);
        exit;
    }
}
echo json_encode(["error" => "Invalid method"]);
