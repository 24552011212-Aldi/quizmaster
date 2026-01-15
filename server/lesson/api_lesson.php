<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/koneksi.php';

// Cek jika user sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Tidak terautentikasi']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

// GET - Ambil lesson
if ($method === 'GET') {
    if (isset($_GET['id'])) {
        // Ambil lesson spesifik
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
        // Ambil semua lesson berdasarkan urutan
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

// POST - Buat lesson baru
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $title = $data['title'] ?? '';
    $materi_id = intval($data['materi_id'] ?? 0);
    $content = $data['content'] ?? '';
    $starter_code = $data['starter_code'] ?? '';
    $exp = intval($data['exp'] ?? 10);
    $order_no = intval($data['order_no'] ?? 1);
    
    // Konversi validation_rules dari array ke JSON string jika diperlukan
    $validation_rules = null;
    if (!empty($data['validation_rules'])) {
        if (is_array($data['validation_rules'])) {
            $validation_rules = json_encode($data['validation_rules']);
        } else if (is_string($data['validation_rules'])) {
            $validation_rules = $data['validation_rules'];
        }
    }
    
    if (empty($title) || $materi_id === 0) {
        echo json_encode(['success' => false, 'message' => 'Judul dan Materi wajib diisi']);
        exit();
    }
    
    $stmt = $conn->prepare("INSERT INTO lessons (title, materi_id, content, starter_code, validation_rules, exp, order_no) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sisssii", $title, $materi_id, $content, $starter_code, $validation_rules, $exp, $order_no);
    
    try {
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'lesson berhasil dibuat', 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error membuat lesson: ' . $stmt->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Exception: ' . $e->getMessage()]);
    }
    
    $stmt->close();
}

// PUT - Perbarui lesson
elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = intval($data['id'] ?? 0);
    $title = $data['title'] ?? '';
    $materi_id = intval($data['materi_id'] ?? 0);
    $content = $data['content'] ?? '';
    $starter_code = $data['starter_code'] ?? '';
    $exp = intval($data['exp'] ?? 10);
    $order_no = intval($data['order_no'] ?? 1);
    
    // Konversi validation_rules dari array ke JSON string jika diperlukan
    $validation_rules = null;
    if (!empty($data['validation_rules'])) {
        if (is_array($data['validation_rules'])) {
            $validation_rules = json_encode($data['validation_rules']);
        } else if (is_string($data['validation_rules'])) {
            $validation_rules = $data['validation_rules'];
        }
    }
    
    if ($id === 0 || empty($title) || $materi_id === 0) {
        echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
        exit();
    }
    
    $stmt = $conn->prepare("UPDATE lessons SET title = ?, materi_id = ?, content = ?, starter_code = ?, validation_rules = ?, exp = ?, order_no = ? WHERE id = ?");
    $stmt->bind_param("sisssiii", $title, $materi_id, $content, $starter_code, $validation_rules, $exp, $order_no, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'lesson berhasil diperbarui']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error memperbarui lesson: ' . $conn->error]);
    }
    
    $stmt->close();
}

// DELETE - Hapus lesson
elseif ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    
    if ($id === 0) {
        echo json_encode(['success' => false, 'message' => 'ID lesson tidak valid']);
        exit();
    }
    
    $stmt = $conn->prepare("DELETE FROM lessons WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'lesson berhasil dihapus']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error menghapus lesson: ' . $conn->error]);
    }
    
    $stmt->close();
}

mysqli_close($conn);
?>

