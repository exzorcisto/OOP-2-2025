<?php
// profile.php
session_start();
// Подключаем классы
include_once './models/Database.php';
include_once './models/Application.php';
include_once './models/Review.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ./auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 1. Создание объектов
$database = new Database();
$db = $database->getConnection();

$application = new Application($db);
$application->user_id = $user_id;

$review = new Review($db);
$review->user_id = $user_id;

// 2. Получение данных
$app_stmt = $application->getApplicationsByUserId();
$applications_list = $app_stmt->fetchAll(PDO::FETCH_ASSOC);

$rev_stmt = $review->getReviewsByUserId();
$reviews_list = $rev_stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <link rel="stylesheet" href="./css/style.css">
    <title>Мой профиль</title>
</head>

<body>
    <header>
        <h1>Личный кабинет</h1>
    </header>
    <a href="./index.php">На главную</a>

    <h2>Мои заявки</h2>
    <table border="1">
        <tr>
            <th>Курс</th>
            <th>Статус</th>
            <th>Дата подачи</th>
        </tr>
        <?php foreach ($applications_list as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['course_title']) ?></td>
                <td><strong><?= $row['status'] ?></strong></td>
                <td><?= $row['created_at'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h2>Мои отзывы</h2>
    <table border="1">
        <tr>
            <th>Курс</th>
            <th>Оценка</th>
            <th>Отзыв</th>
            <th>Дата</th>
        </tr>
        <?php foreach ($reviews_list as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['course_title']) ?></td>
                <td><?= $row['rating'] ?> ⭐</td>
                <td><?= htmlspecialchars($row['comment']) ?></td>
                <td><?= $row['created_at'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>

</html>