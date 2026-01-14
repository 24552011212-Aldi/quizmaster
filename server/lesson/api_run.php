<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Get code from POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {
    $code = $_POST['code'];
    
    // Basic security: wrap in HTML template for proper rendering
    $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            margin: 0;
        }
    </style>
</head>
<body>
' . $code . '
</body>
</html>';
    
    echo json_encode([
        'success' => true,
        'html' => $html
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No code provided'
    ]);
}
?>
