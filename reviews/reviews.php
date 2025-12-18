<?php
// reviews/reviews.php
session_start();
// Подключаем классы
include_once '../models/Database.php';
include_once '../models/Review.php';

// Если не вошел — отправляем на вход
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error_message = null;

// Создание объектов
$database = new Database();
$db = $database->getConnection();
$review = new Review($db);
$review->user_id = $user_id;

// 1. ОБРАБОТКА POST (ОТПРАВКА ОТЗЫВА)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Передача данных из формы в объект
    $review->course_id = $_POST['course_id'] ?? null;
    $review->rating = $_POST['rating'] ?? null;
    $review->comment = $_POST['comment'] ?? null;

    if (empty($review->course_id) || empty($review->rating) || empty($review->comment)) {
        $error_message = "Пожалуйста, заполните все поля.";
    } else {
        if ($review->create()) {
            header("Location: ../profile.php"); // Перенаправляем в профиль после успешного отзыва
            exit;
        } else {
            $error_message = "Ошибка при сохранении отзыва. Возможно, вы уже оставляли отзыв на этот курс.";
        }
    }
}

// 2. ПОЛУЧЕНИЕ КУРСОВ ДЛЯ ОТЗЫВА (Условная логика)

$courses_stmt = $review->getCoursesAvailableForReview();
$courses_to_review = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);

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
        <h1>Оставить отзыв о курсе</h1>
    </header>
    <a href="../index.php">На главную</a>

    <?php if (isset($error_message)): ?>
        <p style="color: red; padding: 10px; border: 1px solid red;"><?= $error_message ?></p>
    <?php endif; ?>

    <?php if (!empty($courses_to_review)): ?>
        <form action="reviews.php" method="post">
            <label>Выберите курс (только завершенные):</label>
            <select name="course_id" required>
                <?php foreach ($courses_to_review as $row): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></option>
                <?php endforeach; ?>
            </select>
            <br><br>

            <label>Оценка (1-5):</label>
            <input type="number" name="rating" min="1" max="5" value="5" required>
            <br><br>

            <textarea name="comment" placeholder="Напишите, что вы думаете..." rows="5" cols="30" required></textarea>
            <br><br>

            <input type="submit" value="Отправить отзыв">
        </form>
    <?php else: ?>
        <p style="color: grey; font-weight: bold;">
            На данный момент у вас нет завершенных курсов, на которые вы не оставили отзыв.
            Пожалуйста, дождитесь, пока администратор отметит вашу заявку как "Завершено".
        </p>
    <?php endif; ?>
</body>

</html>