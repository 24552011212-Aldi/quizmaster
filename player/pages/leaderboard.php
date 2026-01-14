<?php
(function(){
session_start();
include_once "../../server/auth_check.php";
checkLogin();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'player') {
	header("Location: ../../login.php");
	exit();
}
})();
