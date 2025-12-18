<?php
session_start();
include('../db/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$course_id = $_POST['course_id'];
$start_date = $_POST['start_date']; // Получаем дату из POST-запроса

// Обновляем запрос: добавляем колонку start_date и подготавливаем 3 параметра
$stmt = $conn->prepare("INSERT INTO applications (user_id, course_id, start_date) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $user_id, $course_id, $start_date);

if ($stmt->execute()) {
    echo "Заявка успешно отправлена! <a href='../index.php'>На главную</a>";
} else {
    echo "Ошибка: " . $conn->error;
}
