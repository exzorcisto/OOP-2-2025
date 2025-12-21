<?php
// reviews/reviews.php
session_start();

// Включаем отображение ошибок, чтобы не было просто пустого экрана
ini_set('display_errors', 1);
error_reporting(E_ALL);

include_once '../models/Database.php';
include_once '../models/Review.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();
$review_obj = new Review($db);
$review_obj->user_id = $user_id;

$message = null;

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $review_obj->course_id = $_POST['course_id'] ?? null;
    $review_obj->rating = $_POST['rating'] ?? null;
    $review_obj->comment = $_POST['comment'] ?? null;

    if ($review_obj->course_id && $review_obj->rating && $review_obj->comment) {
        if ($review_obj->create()) {
            header("Location: ../profile.php");
            exit;
        } else {
            $message = "❌ Ошибка при сохранении.";
        }
    } else {
        $message = "⚠️ Заполните все поля.";
    }
}

// Получаем список доступных курсов
try {
    $courses_stmt = $review_obj->getCoursesAvailableForReview();
    $courses_to_review = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Ошибка БД: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Оставить отзыв</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <header>
        <h1>Оставить отзыв</h1>
    </header>
    <nav style="text-align:center;"><a href="../index.php">🏠 На главную</a></nav>
    <?php if ($message): ?>
        <p style="color:red;"><?= $message ?></p>
    <?php endif; ?>

    <?php if (!empty($courses_to_review)): ?>
        <form method="post">
            <label>Курс:</label><br>
            <select name="course_id" style="width:100%; padding:8px;">
                <?php foreach ($courses_to_review as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                <?php endforeach; ?>
            </select><br><br>

            <label>Оценка:</label><br>
            <select name="rating" style="width:100%; padding:8px;">
                <option value="5">⭐⭐⭐⭐⭐ (5)</option>
                <option value="4">⭐⭐⭐⭐ (4)</option>
                <option value="3">⭐⭐⭐ (3)</option>
                <option value="2">⭐⭐ (2)</option>
                <option value="1">⭐ (1)</option>
            </select><br><br>

            <label>Комментарий:</label><br>
            <textarea name="comment" rows="5" style="width:100%; padding:8px;" required></textarea><br><br>

            <input type="submit" value="Отправить отзыв" class="btn" style="width:100%;">
        </form>
    <?php else: ?>
        <div style="background:#f9f9f9; padding:20px; border:1px solid #ccc;">
            На данный момент у вас нет завершенных курсов для отзыва.<br>
            <small>(Статус вашей заявки должен быть "Завершено")</small>
        </div>
    <?php endif; ?>
</body>

</html>