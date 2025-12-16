<?php
session_start();
include('../db/db.php');

$login = trim($_POST['login']);
$password = $_POST['password'];

// Ищем пользователя по логину
$stmt = $conn->prepare("SELECT id, password, role_id, fio_user FROM users WHERE login = ?");
$stmt->bind_param("s", $login);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    // Проверяем хеш пароля
    if (password_verify($password, $user['password'])) {
        // Успех: записываем данные в сессию
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['fio'] = $user['fio_user'];

        // Если админ - в админку, иначе на главную
        if ($user['role_id'] == 2) {
            header("Location: ../admin/index.php");
        } else {
            header("Location: ../index.php");
        }
    } else {
        echo "Неверный пароль.";
    }
} else {
    echo "Пользователь не найден.";
}
