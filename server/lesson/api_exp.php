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

// GET - Get user's current total XP
if ($method === 'GET') {
    $user_id = intval($_SESSION['user_id']);
    
    $stmt = $conn->prepare("SELECT total_exp FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'total_exp' => intval($row['total_exp'])
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
    }
    $stmt->close();
}

// POST - Add XP to user account
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $user_id = intval($_SESSION['user_id']);
    $exp_amount = intval($data['exp_amount'] ?? 0);
    $lesson_id = intval($data['lesson_id'] ?? 0);
    
    if ($exp_amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid XP amount']);
        exit();
    }
    
    // Update user's total exp
    $stmt = $conn->prepare("UPDATE users SET total_exp = total_exp + ? WHERE id = ?");
    $stmt->bind_param("ii", $exp_amount, $user_id);
    
    if ($stmt->execute()) {
        // Get updated total
        $stmt2 = $conn->prepare("SELECT total_exp FROM users WHERE id = ?");
        $stmt2->bind_param("i", $user_id);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $row = $result2->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'message' => 'XP added successfully',
            'exp_added' => $exp_amount,
            'total_exp' => intval($row['total_exp'])
        ]);
        $stmt2->close();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error updating XP: ' . $stmt->error
        ]);
    }
    $stmt->close();
}

else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
