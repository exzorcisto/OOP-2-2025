<?php
session_start();
include('../db/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$course_id = $_POST['course_id'];

$stmt = $conn->prepare("INSERT INTO applications (user_id, course_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $course_id);

if ($stmt->execute()) {
    echo "Заявка успешно отправлена! <a href='../index.php'>На главную</a>";
} else {
    echo "Ошибка: " . $conn->error;
}
