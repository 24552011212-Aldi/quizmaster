<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/koneksi.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$userId = $_SESSION['user_id'];

// Get user progress
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    $query = "SELECT lesson_id FROM progress WHERE user_id = ? AND completed = 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $completedLessons = [];
    while ($row = $result->fetch_assoc()) {
        $completedLessons[] = intval($row['lesson_id']);
    }
    
    echo json_encode([
        'success' => true,
        'progress' => $completedLessons
    ]);
    
    $stmt->close();
}
// Save progress
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lesson_id'])) {
    $lessonId = intval($_POST['lesson_id']);
    
    // Check if progress already exists
    $checkStmt = $conn->prepare("SELECT id FROM progress WHERE user_id = ? AND lesson_id = ?");
    $checkStmt->bind_param("ii", $userId, $lessonId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) {
        // Insert new progress
        $insertStmt = $conn->prepare("INSERT INTO progress (user_id, lesson_id, completed, completed_at) VALUES (?, ?, 1, NOW())");
        $insertStmt->bind_param("ii", $userId, $lessonId);
        $success = $insertStmt->execute();
        $insertStmt->close();
    } else {
        // Update existing
        $updateStmt = $conn->prepare("UPDATE progress SET completed = 1, completed_at = NOW() WHERE user_id = ? AND lesson_id = ?");
        $updateStmt->bind_param("ii", $userId, $lessonId);
        $success = $updateStmt->execute();
        $updateStmt->close();
    }
    
    $checkStmt->close();
    
    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Progress saved' : 'Failed to save progress'
    ]);
}
else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
}

mysqli_close($conn);
?>
