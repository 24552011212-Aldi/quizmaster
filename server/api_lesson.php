<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/config/koneksi.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Get specific lesson or all lessons
if (isset($_GET['id'])) {
    $lessonId = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT * FROM lessons WHERE id = ?");
    $stmt->bind_param("i", $lessonId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $lesson = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'lesson' => $lesson
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Lesson not found'
        ]);
    }
    
    $stmt->close();
} else {
    // Get all lessons ordered by order_no
    $query = "SELECT * FROM lessons ORDER BY order_no ASC";
    $result = mysqli_query($conn, $query);
    
    $lessons = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $lessons[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'lessons' => $lessons
    ]);
}

mysqli_close($conn);
?>
