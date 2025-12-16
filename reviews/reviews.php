<?php
session_start();
include('../db/db.php');

// Если не вошел — отправляем на вход
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ===============================================
// 1. ПОЛУЧЕНИЕ КУРСОВ ДЛЯ ОТЗЫВА
// ===============================================

// Получаем только те курсы, по которым у пользователя есть заявка со статусом 'completed' 
// И на которые он ЕЩЕ не оставил отзыв.
$sql = "SELECT 
            c.id, c.title 
        FROM courses c
        JOIN applications a ON a.course_id = c.id
        LEFT JOIN reviews r ON r.course_id = c.id AND r.user_id = a.user_id
        WHERE a.user_id = ? 
          AND a.status = 'completed' 
          AND r.id IS NULL";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$courses_to_review = $stmt->get_result();

// ===============================================
// 2. ОБРАБОТКА POST (ОТПРАВКА ОТЗЫВА)
// ===============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $rating = $_POST['rating'];
    $comment = trim($_POST['comment']);

    if (empty($course_id) || empty($rating) || empty($comment)) {
        $error_message = "Пожалуйста, заполните все поля.";
    } else {
        $stmt = $conn->prepare("INSERT INTO reviews (user_id, course_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $user_id, $course_id, $rating, $comment);

        if ($stmt->execute()) {
            header("Location: ../profile.php"); // Перенаправляем в профиль после успешного отзыва
            exit;
        } else {
            $error_message = "Ошибка при сохранении отзыва: " . $conn->error;
        }
    }
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
        <h1>Оставить отзыв о курсе</h1>
    </header>
    <a href="../index.php">На главную</a>

    <?php if (isset($error_message)): ?>
        <p style="color: red;"><?= $error_message ?></p>
    <?php endif; ?>

    <?php if ($courses_to_review->num_rows > 0): ?>
        <form action="reviews.php" method="post">
            <label>Выберите курс (только завершенные):</label>
            <select name="course_id" required>
                <?php while ($row = $courses_to_review->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></option>
                <?php endwhile; ?>
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