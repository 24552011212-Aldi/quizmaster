<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/config/koneksi.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lesson_id']) && isset($_POST['code'])) {
    $lessonId = intval($_POST['lesson_id']);
    $userCode = $_POST['code'];
    $userId = $_SESSION['user_id'];
    
    // Get expected output from database
    $stmt = $conn->prepare("SELECT expected_output FROM lessons WHERE id = ?");
    $stmt->bind_param("i", $lessonId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Lesson not found']);
        exit();
    }
    
    $lesson = $result->fetch_assoc();
    $expectedOutput = $lesson['expected_output'];
    
    // Normalize both strings for comparison
    // Remove extra whitespace, newlines, and tabs
    $normalizedUserCode = preg_replace('/\s+/', '', trim($userCode));
    $normalizedExpected = preg_replace('/\s+/', '', trim($expectedOutput));
    
    // Compare
    $isCorrect = ($normalizedUserCode === $normalizedExpected);
    
    if ($isCorrect) {
        // Save progress to database
        $checkStmt = $conn->prepare("SELECT id FROM progress WHERE user_id = ? AND lesson_id = ?");
        $checkStmt->bind_param("ii", $userId, $lessonId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows === 0) {
            // Insert new progress
            $insertStmt = $conn->prepare("INSERT INTO progress (user_id, lesson_id, completed, completed_at) VALUES (?, ?, 1, NOW())");
            $insertStmt->bind_param("ii", $userId, $lessonId);
            $insertStmt->execute();
            $insertStmt->close();
        } else {
            // Update existing progress
            $updateStmt = $conn->prepare("UPDATE progress SET completed = 1, completed_at = NOW() WHERE user_id = ? AND lesson_id = ?");
            $updateStmt->bind_param("ii", $userId, $lessonId);
            $updateStmt->execute();
            $updateStmt->close();
        }
        
        $checkStmt->close();
    }
    
    echo json_encode([
        'success' => true,
        'correct' => $isCorrect,
        'message' => $isCorrect ? 'Correct! Well done!' : 'Not quite right. Try again!'
    ]);
    
    $stmt->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters'
    ]);
}

mysqli_close($conn);
?>
