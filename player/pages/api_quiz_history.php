
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
include '../../server/config/koneksi.php';

// Gabungkan keberhasilan dan kegagalan quiz
$sql = "
    SELECT u.username, q.materi, q.judul, qh.selesai_pada as waktu, 'DONE' as status
    FROM quiz_history qh
    JOIN users u ON qh.user_id = u.id
    JOIN quizzes q ON qh.quiz_id = q.id
    UNION ALL
    SELECT u.username, q.materi, q.judul, qf.gagal_pada as waktu, 'FAILED' as status
    FROM quiz_fail_history qf
    JOIN users u ON qf.user_id = u.id
    JOIN quizzes q ON qf.quiz_id = q.id
    ORDER BY waktu DESC LIMIT 5
";
$result = mysqli_query($conn, $sql);
if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => mysqli_error($conn)]);
    exit;
}
$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = [
        'username' => $row['username'],
        'materi' => $row['materi'],
        'judul' => $row['judul'],
        'waktu' => $row['waktu'],
        'status' => $row['status']
    ];
}
echo json_encode($data);