<?php
$host = 'localhost';
$user = 'root';
$password = '';  // пароль
$database = 'exr_studio';  // Название  БД
$port = 3306;  // Порт MySQL

$conn = new mysqli($host, $user, $password, $database, $port);

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    $pdo = null;
}
?>