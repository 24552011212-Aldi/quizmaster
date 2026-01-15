<?php
session_start();
header('Content-Type: application/json');

// Cek jika user sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Tidak terautentikasi']);
    exit();
}

// Ambil code/files dari POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Handle format lama (single code) dan format baru (multiple files)
    if (isset($input['files']) && is_array($input['files'])) {
        $files = $input['files'];
        
        // Ekstrak konten CSS
        $cssContent = '';
        foreach ($files as $filename => $content) {
            if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'css') {
                $cssContent .= "\n" . $content;
            }
        }
        
        // Ekstrak konten JavaScript
        $jsContent = '';
        foreach ($files as $filename => $content) {
            if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'js') {
                // Escape konten untuk keamanan ketika dimasukkan ke script tag
                $escapedJs = preg_replace('/<\/script>/i', '<\\/script>', $content);
                $jsContent .= "\n" . $escapedJs;
            }
        }
        
        // Ambil konten HTML body
        $bodyContent = '';
        if (isset($files['index.html'])) {
            $htmlFile = $files['index.html'];
            // Coba ekstrak konten body
            if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $htmlFile, $matches)) {
                $bodyContent = trim($matches[1]);
            } else {
                // Jika tidak ada body tag, hapus DOCTYPE dan tag html/head
                $bodyContent = preg_replace('/<\?.*?\?>/s', '', $htmlFile);
                $bodyContent = preg_replace('/<html[^>]*>|<\/html>/i', '', $bodyContent);
                $bodyContent = preg_replace('/<head[^>]*>.*?<\/head>/is', '', $bodyContent);
                $bodyContent = trim($bodyContent);
            }
        }
        
        // Buat HTML lengkap
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code Preview</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            padding: 20px;
            line-height: 1.6;
        }
    </style>';
        
        // Tambahkan CSS user
        if (!empty($cssContent)) {
            $html .= "\n    <style>\n" . $cssContent . "\n    </style>";
        }
        
        $html .= '
</head>
<body>
' . $bodyContent . '
</body>';
        
        // Tambahkan JavaScript user di akhir
        if (!empty($jsContent)) {
            $html .= "\n<script type=\"text/javascript\">" . $jsContent . "</script>";
        }
        
        $html .= "\n</html>";
        
        echo json_encode([
            'success' => true,
            'html' => $html
        ]);
    } elseif (isset($_POST['code'])) {
        // Fallback: format lama dengan single code
        $code = $_POST['code'];
        $html = '<!DOCTYPE html>
<html lang="id">
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
            'message' => 'Tidak ada code yang diberikan'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Request method tidak valid'
    ]);
}
?>
