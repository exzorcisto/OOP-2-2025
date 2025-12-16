<?php
include ('../db/db.php');

$login = trim($_POST['login']);
$fio_user = trim($_POST['fio_user']);
$number = trim($_POST['number']);
$password = $_POST['password'];

if (empty($login) || empty($fio_user) || empty($number) || empty($password)) {
    die("Все поля обязательны для заполнения.");
}

$passwordHash = password_hash($password, PASSWORD_BCRYPT);

// Подготовленное выражение
$stmt = $conn->prepare("INSERT INTO users (login, fio_user, number, password) VALUES (?, ?, ?, ?)");
$stmt -> bind_param("ssss", $login, $fio_user, $number, $passwordHash);

if ($stmt -> execute()) {
    header("Location: ./login.php");
    exit;
} else {
    echo "Error: " . $stmt->error;
}

$stmt -> close();
$conn -> close();