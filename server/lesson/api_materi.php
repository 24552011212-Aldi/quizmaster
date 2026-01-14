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

// GET - Retrieve all materi
if ($method === 'GET') {
    $query = "SELECT id, nama, icon FROM materi_lesson ORDER BY nama ASC";
    $result = mysqli_query($conn, $query);
    
    $materi = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $materi[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $materi
    ]);
}

// POST - Create new materi
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $nama = $data['nama'] ?? '';
    $icon = $data['icon'] ?? '';
    
    if (empty($nama)) {
        echo json_encode(['success' => false, 'message' => 'Nama materi is required']);
        exit();
    }
    
    $stmt = $conn->prepare("INSERT INTO materi_lesson (nama, icon) VALUES (?, ?)");
    $stmt->bind_param("ss", $nama, $icon);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Materi created successfully', 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error creating materi: ' . $conn->error]);
    }
    
    $stmt->close();
}

// DELETE - Delete materi
elseif ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    
    if ($id === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid materi ID']);
        exit();
    }
    
    $stmt = $conn->prepare("DELETE FROM materi_lesson WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Materi deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting materi: ' . $conn->error]);
    }
    
    $stmt->close();
}

mysqli_close($conn);
?>
