<?php
// Legacy shim: redirect old page to new module
session_start();
require_once '../../server/auth_check.php';
checkLogin();
header('Location: lesson/index.php');
exit;
?>
