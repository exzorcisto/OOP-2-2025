<?php
session_start();
include('./db/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ./auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Запрос 1: Получение заявок с названиями статуса и метода оплаты
$app_sql = "SELECT 
                c.title AS course_title, 
                a.start_date, 
                s.name AS status_name,
                pm.method_name AS payment_name,
                a.created_at 
            FROM applications a 
            JOIN courses c ON a.course_id = c.id 
            JOIN statuses s ON a.status_id = s.id
            JOIN payment_methods pm ON a.payment_method_id = pm.id
            WHERE a.user_id = ?
            ORDER BY a.created_at DESC";
$app_stmt = $conn->prepare($app_sql);
$app_stmt->bind_param("i", $user_id);
$app_stmt->execute();
$applications = $app_stmt->get_result();

// Запрос 2: Получение отзывов пользователя (без изменений)
$rev_sql = "SELECT 
                c.title AS course_title, 
                r.rating, 
                r.comment,
                r.created_at 
            FROM reviews r 
            JOIN courses c ON r.course_id = c.id 
            WHERE r.user_id = ?
            ORDER BY r.created_at DESC";
$rev_stmt = $conn->prepare($rev_sql);
$rev_stmt->bind_param("i", $user_id);
$rev_stmt->execute();
$reviews = $rev_stmt->get_result();
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
            <th>Дата старта</th>
            <th>Оплата</th>
            <th>Статус</th>
            <th>Дата подачи</th>
        </tr>
        <?php while ($row = $applications->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['course_title']) ?></td>
                <td><?= $row['start_date'] ? date("d.m.Y", strtotime($row['start_date'])) : '—' ?></td>
                <td><?= htmlspecialchars($row['payment_name']) ?></td>
                <td><strong><?= htmlspecialchars($row['status_name']) ?></strong></td>
                <td><?= date("d.m.Y H:i", strtotime($row['created_at'])) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h2>Мои отзывы</h2>
    <table border="1">
        <tr>
            <th>Курс</th>
            <th>Оценка</th>
            <th>Отзыв</th>
            <th>Дата</th>
        </tr>
        <?php while ($row = $reviews->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['course_title']) ?></td>
                <td><?= str_repeat('⭐', $row['rating']) ?></td>
                <td><?= htmlspecialchars($row['comment']) ?></td>
                <td><?= date("d.m.Y", strtotime($row['created_at'])) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>

</html>