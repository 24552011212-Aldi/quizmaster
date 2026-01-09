<?php
function checkLogin()
{
    if (!isset($_SESSION['user_id'])) {
        // Cek cookie jika session belum ada
        if (isset($_COOKIE['user_login'])) {
            include_once __DIR__ . '/config/koneksi.php';
            $decoded_username = base64_decode($_COOKIE['user_login']);
            $query = "SELECT * FROM users WHERE username = '$decoded_username'";
            $result = mysqli_query($conn, $query);
            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                return;
            }
        }
        header("Location: ../login.php");
        exit();
    }
}

function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
