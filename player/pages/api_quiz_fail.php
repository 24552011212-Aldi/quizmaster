<?php
session_start();
include '../../server/config/koneksi.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $_SESSION['user_id'] ?? null;
    $quiz_id = $data['quiz_id'] ?? null;
    $gagal_pada = date('Y-m-d H:i:s');
    if ($user_id && $quiz_id) {
        $stmt = $conn->prepare("INSERT INTO quiz_fail_history (user_id, quiz_id, gagal_pada) VALUES (?, ?, ?)");
        $stmt->bind_param('iis', $user_id, $quiz_id, $gagal_pada);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'error' => $stmt->error]);
        }
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'error' => 'Missing user_id or quiz_id']);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'error' => 'Method not allowed']);
}
