<?php
$host = 'localhost';
$db   = 'winsumweb';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die('LỖI KẾT NỐI DATABASE: ' . $conn->connect_error);
}

$conn->set_charset($charset);
