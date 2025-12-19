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
// Метод getApplicationsByUserId() в модели Application.php 
// должен быть обновлен для выбора поля start_date
$app_stmt = $application->getApplicationsByUserId();
$applications_list = $app_stmt->fetchAll(PDO::FETCH_ASSOC);

$rev_stmt = $review->getReviewsByUserId();
$reviews_list = $rev_stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="./css/style.css">
    <title>Мой профиль</title>
</head>

<body>
    <header>
        <h1>Личный кабинет</h1>
    </header>
    <div class="nav" style="margin-bottom: 20px;">
        <a href="./index.php">На главную</a> |
        <a href="./auth/logout.php">Выйти</a>
    </div>

    <h2>Мои заявки</h2>
    <table border="1" width="100%">
        <tr>
            <th>Курс</th>
            <th>Желаемая дата начала</th>
            <th>Статус</th>
            <th>Дата подачи заявки</th>
        </tr>
        <?php if (!empty($applications_list)): ?>
            <?php foreach ($applications_list as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['course_title']) ?></td>
                    <td>
                        <?php
                        // Вывод даты старта в удобном формате
                        echo !empty($row['start_date'])
                            ? date("d.m.Y", strtotime($row['start_date']))
                            : '<span style="color: gray;">не указана</span>';
                        ?>
                    </td>
                    <td><strong><?= htmlspecialchars($row['status']) ?></strong></td>
                    <td><?= date("d.m.Y H:i", strtotime($row['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" style="text-align:center;">У вас пока нет поданных заявок.</td>
            </tr>
        <?php endif; ?>
    </table>

    <h2>Мои отзывы</h2>
    <table border="1" width="100%">
        <tr>
            <th>Курс</th>
            <th>Оценка</th>
            <th>Отзыв</th>
            <th>Дата</th>
        </tr>
        <?php if (!empty($reviews_list)): ?>
            <?php foreach ($reviews_list as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['course_title']) ?></td>
                    <td><?= $row['rating'] ?> ⭐</td>
                    <td><?= htmlspecialchars($row['comment']) ?></td>
                    <td><?= date("d.m.Y", strtotime($row['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" style="text-align:center;">Вы еще не оставили ни одного отзыва.</td>
            </tr>
        <?php endif; ?>
    </table>
</body>

</html>