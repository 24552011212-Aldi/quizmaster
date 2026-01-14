<?php
session_start();
session_unset();
setcookie('user_login', '', time() - 3600, '/'); // Hapus cookie
session_destroy();
header("Location: index.php");
exit;
