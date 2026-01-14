<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/koneksi.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

// GET - Retrieve lessons
if ($method === 'GET') {
    if (isset($_GET['id'])) {
        // Get specific lesson
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
}

// POST - Create new lesson
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $title = $data['title'] ?? '';
    $materi_id = intval($data['materi_id'] ?? 0);
    $content = $data['content'] ?? '';
    $starter_code = $data['starter_code'] ?? '';
    $exp = intval($data['exp'] ?? 10);
    $order_no = intval($data['order_no'] ?? 1);
    
    // Handle validation_rules - convert array to JSON string
    $validation_rules = null;
    if (!empty($data['validation_rules'])) {
        if (is_array($data['validation_rules'])) {
            $validation_rules = json_encode($data['validation_rules']);
        } else if (is_string($data['validation_rules'])) {
            $validation_rules = $data['validation_rules'];
        }
    }
    
    if (empty($title) || $materi_id === 0) {
        echo json_encode(['success' => false, 'message' => 'Title and Materi are required']);
        exit();
    }
    
    $stmt = $conn->prepare("INSERT INTO lessons (title, materi_id, content, starter_code, validation_rules, exp, order_no) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sisssii", $title, $materi_id, $content, $starter_code, $validation_rules, $exp, $order_no);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Lesson created successfully', 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error creating lesson: ' . $stmt->error]);
    }
    
    $stmt->close();
}

// PUT - Update lesson
elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = intval($data['id'] ?? 0);
    $title = $data['title'] ?? '';
    $materi_id = intval($data['materi_id'] ?? 0);
    $content = $data['content'] ?? '';
    $starter_code = $data['starter_code'] ?? '';
    $exp = intval($data['exp'] ?? 10);
    $order_no = intval($data['order_no'] ?? 1);
    
    // Handle validation_rules - convert array to JSON string
    $validation_rules = null;
    if (!empty($data['validation_rules'])) {
        if (is_array($data['validation_rules'])) {
            $validation_rules = json_encode($data['validation_rules']);
        } else if (is_string($data['validation_rules'])) {
            $validation_rules = $data['validation_rules'];
        }
    }
    
    if ($id === 0 || empty($title) || $materi_id === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        exit();
    }
    
    $stmt = $conn->prepare("UPDATE lessons SET title = ?, materi_id = ?, content = ?, starter_code = ?, validation_rules = ?, exp = ?, order_no = ? WHERE id = ?");
    $stmt->bind_param("sisssiii", $title, $materi_id, $content, $starter_code, $validation_rules, $exp, $order_no, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Lesson updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating lesson: ' . $conn->error]);
    }
    
    $stmt->close();
}

// DELETE - Delete lesson
elseif ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    
    if ($id === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid lesson ID']);
        exit();
    }
    
    $stmt = $conn->prepare("DELETE FROM lessons WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Lesson deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting lesson: ' . $conn->error]);
    }
    
    $stmt->close();
}

mysqli_close($conn);
?>

