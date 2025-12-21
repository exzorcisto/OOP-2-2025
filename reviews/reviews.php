<?php
session_start();
include('../db/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 1. ПОЛУЧЕНИЕ КУРСОВ ДЛЯ ОТЗЫВА
// Исправлено: замена a.status = 'completed' на a.status_id = 3
$sql = "SELECT 
            c.id, c.title 
        FROM courses c
        JOIN applications a ON a.course_id = c.id
        LEFT JOIN reviews r ON r.course_id = c.id AND r.user_id = a.user_id
        WHERE a.user_id = ? 
          AND a.status_id = 3 
          AND r.id IS NULL";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$courses_to_review = $stmt->get_result();

// 2. ОБРАБОТКА POST (ОТПРАВКА ОТЗЫВА)
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
            header("Location: ../profile.php");
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
    <nav style="text-align: center; margin: 10px;"><a href="../index.php">🏠 На главную</a></nav>

    <?php if (isset($error_message)): ?>
        <p style="color: red; text-align: center;"><?= $error_message ?></p>
    <?php endif; ?>

    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <?php if ($courses_to_review->num_rows > 0): ?>
            <form action="reviews.php" method="post">
                <label>Выберите курс (только завершенные):</label><br>
                <select name="course_id" required style="width: 100%; padding: 10px;">
                    <?php while ($row = $courses_to_review->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></option>
                    <?php endwhile; ?>
                </select>
                <br><br>

                <label>Оценка (1-5):</label><br>
                <input type="number" name="rating" min="1" max="5" value="5" required style="width: 100%; padding: 10px;">
                <br><br>

                <label>Ваш отзыв:</label><br>
                <textarea name="comment" placeholder="Напишите, что вы думаете..." rows="5" required style="width: 100%; padding: 10px;"></textarea>
                <br><br>

                <input type="submit" value="Отправить отзыв" class="btn">
            </form>
        <?php else: ?>
            <div style="border: 1px solid #ccc; padding: 20px; background: #f9f9f9; color: #666;">
                На данный момент у вас нет завершенных курсов, на которые вы не оставили отзыв.
                Отзыв можно оставить только после того, как статус вашей заявки в профиле сменится на "Завершено".
            </div>
        <?php endif; ?>
    </div>
</body>

</html>