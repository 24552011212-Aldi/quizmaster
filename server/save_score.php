<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

session_start();
include "config/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Forward request to api_score.php for unified logic
    $input = file_get_contents("php://input");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/api_score.php");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    // Pass session cookie
    if (isset($_COOKIE[session_name()])) {
        curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . $_COOKIE[session_name()]);
    }
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    http_response_code($httpcode);
    echo $response;
}
