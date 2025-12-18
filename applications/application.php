<?php
session_start();
include('../db/db.php');

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    die("Пожалуйста, <a href='../auth/login.php'>войдите</a> в систему.");
}

// 1. ФУНКЦИЯ ОТОБРАЖЕНИЯ ОТЗЫВОВ

function display_reviews($conn, $course_id)
{
    echo '<h2>Отзывы о курсе</h2>';

    $review_sql = "SELECT r.rating, r.comment, u.fio_user 
                   FROM reviews r 
                   JOIN users u ON r.user_id = u.id 
                   WHERE r.course_id = ?";
    $stmt = $conn->prepare($review_sql);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $reviews = $stmt->get_result();

    if ($reviews->num_rows > 0) {
        echo '<ul class="review-list">';
        while ($review = $reviews->fetch_assoc()) {
            echo '<li>';
            echo '<strong>' . htmlspecialchars($review['fio_user']) . '</strong> (Оценка: ' . str_repeat('⭐', $review['rating']) . '): ';
            echo htmlspecialchars($review['comment']);
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>Отзывов пока нет. Будьте первыми!</p>';
    }
}


// 2. ОБРАБОТКА POST (ОТПРАВКА ЗАЯВКИ)

$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_application') {
    $user_id = $_SESSION['user_id'];
    $course_id = $_POST['course_id'];
    $start_date = $_POST['start_date']; // Получаем выбранную дату

    // Проверка, нет ли уже активной заявки на этот курс
    $check_stmt = $conn->prepare("SELECT id FROM applications WHERE user_id = ? AND course_id = ? AND status IN ('pending', 'approved')");
    $check_stmt->bind_param("ii", $user_id, $course_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows == 0) {
        // Добавляем start_date в INSERT
        $stmt = $conn->prepare("INSERT INTO applications (user_id, course_id, start_date) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $course_id, $start_date);

        if ($stmt->execute()) {
            $message = "✅ Заявка успешно отправлена на " . date("d.m.Y", strtotime($start_date)) . " и ожидает рассмотрения!";
        } else {
            $message = "❌ Ошибка при отправке заявки: " . $conn->error;
        }
    } else {
        $message = "⚠️ У вас уже есть активная заявка на этот курс.";
    }
}



// 3. ПОЛУЧЕНИЕ ДАННЫХ ДЛЯ ВЫВОДА
$courses_result = $conn->query("SELECT * FROM courses");
$courses = [];
while ($row = $courses_result->fetch_assoc()) {
    $courses[] = $row;
}

$selected_course_id = null;
if (!empty($courses)) {
    if (isset($_POST['course_id'])) {
        $selected_course_id = $_POST['course_id'];
    } else {
        $selected_course_id = $courses[0]['id'];
    }
}

?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Подача заявки</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
        function updateReviews() {
            document.getElementById('update-form').submit();
        }
    </script>
</head>

<body>
    <header>
        <h1>Подать заявку на курс</h1>
    </header>
    <a href="../index.php">На главную</a>

    <?php if (isset($message)): ?>
        <p style="padding: 10px; border: 1px solid #ccc; background-color: #f9f9f9;"><?= $message ?></p>
    <?php endif; ?>

    <form id="update-form" method="post" style="display: none;">
        <input type="hidden" name="action" value="update_reviews">
        <input type="hidden" name="course_id" id="hidden-course-id">
    </form>

    <form method="post">
        <input type="hidden" name="action" value="submit_application">

        <label>Выберите курс:</label>
        <select name="course_id" onchange="document.getElementById('hidden-course-id').value = this.value; updateReviews();" required>
            <?php if (!empty($courses)): ?>
                <?php foreach ($courses as $row): ?>
                    <option value="<?= $row['id'] ?>" <?= ($row['id'] == $selected_course_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['title']) ?>
                    </option>
                <?php endforeach; ?>
            <?php else: ?>
                <option value="" disabled selected>Нет доступных курсов</option>
            <?php endif; ?>
        </select>

        <?php if (!empty($courses)): ?>
            <br><br>
            <label for="start_date">Желаемая дата начала обучения:</label><br>
            <input type="date" name="start_date" id="start_date" required min="<?= date('Y-m-d') ?>">
            <br><br>
            <input type="submit" value="Отправить заявку на курс">
        <?php else: ?>
            <p>Нельзя подать заявку, пока администратор не добавит курсы.</p>
        <?php endif; ?>
    </form>

    <hr>

    <?php
    if ($selected_course_id) {
        display_reviews($conn, $selected_course_id);
    }
    ?>

</body>

</html>