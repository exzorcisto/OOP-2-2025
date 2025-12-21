<?php
session_start();
include('../db/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$course_id = $_POST['course_id'];
$start_date = $_POST['start_date'];
$payment_id = $_POST['payment_method_id'];
$status_id = 1;

$stmt = $conn->prepare("INSERT INTO applications (user_id, course_id, start_date, status_id, payment_method_id) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iisii", $user_id, $course_id, $start_date, $status_id, $payment_id);

if ($stmt->execute()) {
    echo "Заявка успешно отправлена! <a href='../index.php'>На главную</a>";
} else {
    echo "Ошибка: " . $conn->error;
}
