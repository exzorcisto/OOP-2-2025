<?php
// auth/login_handler.php
session_start();
include_once '../models/Database.php';
include_once '../models/User.php';

// 1. Создание объектов и подключения к БД
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    // Обработка ошибки подключения
    die("КРИТИЧЕСКАЯ ОШИБКА: Не удалось подключиться к базе данных. Проверьте настройки Database.php.");
}

$user = new User($db);

// 2. Передача данных
$user->login = trim($_POST['login']);
$user->password = $_POST['password'];

// 3. Вызов метода login
if ($user->login()) {
    // Успех: записываем данные в сессию ИЗ СВОЙСТВ ОБЪЕКТА
    $_SESSION['user_id'] = $user->id;       // <-- ИСПРАВЛЕНО
    $_SESSION['role_id'] = $user->role_id; // <-- ИСПРАВЛЕНО
    $_SESSION['fio'] = $user->fio_user;   // <-- ИСПРАВЛЕНО

    // Перенаправление в зависимости от роли
    if ($user->role_id == 2) {
        header("Location: ../admin/index.php");
    } else {
        header("Location: ../index.php");
    }
    exit;
} else {
    // Ошибка
    echo "Неверный логин или пароль. <a href='./login.php'>Повторить вход</a>";
}
