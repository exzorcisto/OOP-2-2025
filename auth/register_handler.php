<?php
include_once ('../models/Database.php');
include_once ('../models/User.php');

// Создаем подключение к базе данных
$database = new Database();
$db = $database->getConnection();

// Создаем объект пользователя
$user = new User($db);

// Получаем данные из формы регистрации
$user->login = trim($_POST['login']);
$user->password = $_POST['password'];
$user->fio_user = trim($_POST['fio_user']);
$user->number = trim($_POST['number']);

// Проверка на пустые поля
if (empty($user->login) || empty($user->password) || empty($user->fio_user) || empty($user->number)) {
    die("Пожалуйста, заполните все поля.");
}

// Регистрируем пользователя
if ($user->register()) {
    header("Location: ./login.php");
} else {
    echo "Ошибка: Не удалось зарегистрировать пользователя.";
}