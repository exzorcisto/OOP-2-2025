<?php
// profile.php
session_start();
include_once './models/Database.php';
include_once './models/Application.php';
include_once './models/Review.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ./auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();

$application = new Application($db);
$application->user_id = $user_id;

$review = new Review($db);
$review->user_id = $user_id;

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
    <div style="text-align: center; margin: 10px;">
        <a href="./index.php">🏠 На главную</a>
    </div>

    <div style="max-width: 1000px; margin: 0 auto; padding: 20px;">
        <h2>Мои заявки</h2>
        <table border="1" width="100%" style="border-collapse: collapse; margin-bottom: 30px;">
            <tr style="background: #eee;">
                <th>Курс</th>
                <th>Дата старта</th>
                <th>Тип оплаты</th>
                <th>Статус</th>
                <th>Дата подачи</th>
            </tr>
            <?php if (!empty($applications_list)): ?>
                <?php foreach ($applications_list as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['course_title']) ?></td>
                        <td><?= $row['start_date'] ? date("d.m.Y", strtotime($row['start_date'])) : '—' ?></td>
                        <td><?= htmlspecialchars($row['payment_method_name'] ?? 'Не указан') ?></td>
                        <td><strong><?= htmlspecialchars($row['status_name']) ?></strong></td>
                        <td><?= date("d.m.Y H:i", strtotime($row['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align:center;">Заявок пока нет.</td>
                </tr>
            <?php endif; ?>
        </table>

        <h2>Мои отзывы</h2>
        <table border="1" width="100%" style="border-collapse: collapse;">
            <tr style="background: #eee;">
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
                    <td colspan="4" style="text-align:center;">Вы еще не оставляли отзывов.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</body>

</html>