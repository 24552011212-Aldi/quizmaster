
<?php
header('Content-Type: application/json');
include '../config/koneksi.php';

// Only allow DELETE method
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Invalid method"]);
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "ID user tidak valid"]);
    exit;
}

$errors = [];

// leaderboard
$stmt = $conn->prepare("DELETE FROM leaderboard WHERE user_id = ?");
if ($stmt) {
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) {
        $errors[] = "leaderboard: " . $stmt->error;
    }
    $stmt->close();
} else {
    $errors[] = "leaderboard: " . $conn->error;
}

// quiz_history
$stmt = $conn->prepare("DELETE FROM quiz_history WHERE user_id = ?");
if ($stmt) {
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) {
        $errors[] = "quiz_history: " . $stmt->error;
    }
    $stmt->close();
} else {
    $errors[] = "quiz_history: " . $conn->error;
}

// ...existing code...

// users
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
if ($stmt) {
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) {
        $errors[] = "users: " . $stmt->error;
    }
    $stmt->close();
} else {
    $errors[] = "users: " . $conn->error;
}

$conn->close();

if (empty($errors)) {
    echo json_encode(["success" => true]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $errors]);
}
