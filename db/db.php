<?php
$host = 'MySQL-8.0';
$user = 'root';
$password = '';
$dbname = 'phpDB';

// Создаем подключение
$conn = new mysqli($host, $user, $password, $dbname);

// Проверяем подключение
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);   
}

echo "Подключение успешно установлено";
?>