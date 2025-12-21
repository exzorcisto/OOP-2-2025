<?php
session_start();
include('../db/db.php');

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
            echo '<strong>' . htmlspecialchars($review['fio_user']) . '</strong> (' . str_repeat('⭐', $review['rating']) . '): ';
            echo htmlspecialchars($review['comment']);
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>Отзывов пока нет. Будьте первыми!</p>';
    }
}

$message = null;

// 2. ОБРАБОТКА ОТПРАВКИ ЗАЯВКИ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_application') {
    // Защита от Fatal error: проверяем, что дата и способ оплаты заполнены
    if (empty($_POST['start_date']) || empty($_POST['payment_method_id'])) {
        $message = "⚠️ Пожалуйста, заполните все поля формы.";
    } else {
        $user_id = $_SESSION['user_id'];
        $course_id = $_POST['course_id'];
        $start_date = $_POST['start_date'];
        $payment_id = $_POST['payment_method_id'];
        $initial_status_id = 1; // ID статуса "Новое"

        // Проверка на дубликаты (статусы "Новое" или "В процессе")
        $check_stmt = $conn->prepare("SELECT id FROM applications WHERE user_id = ? AND course_id = ? AND status_id IN (1, 2)");
        $check_stmt->bind_param("ii", $user_id, $course_id);
        $check_stmt->execute();

        if ($check_stmt->get_result()->num_rows == 0) {
            // INSERT со всеми новыми полями: status_id и payment_method_id
            $stmt = $conn->prepare("INSERT INTO applications (user_id, course_id, start_date, status_id, payment_method_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisii", $user_id, $course_id, $start_date, $initial_status_id, $payment_id);

            if ($stmt->execute()) {
                $message = "✅ Заявка успешно отправлена!";
            } else {
                $message = "❌ Ошибка БД: " . $conn->error;
            }
        } else {
            $message = "⚠️ У вас уже есть активная заявка на этот курс.";
        }
    }
}

// 3. ПОЛУЧЕНИЕ ДАННЫХ ДЛЯ ВЫВОДА
$courses = $conn->query("SELECT * FROM courses")->fetch_all(MYSQLI_ASSOC);
$payments = $conn->query("SELECT * FROM payment_methods")->fetch_all(MYSQLI_ASSOC);

// Определение выбранного курса для показа отзывов
$selected_course_id = $_POST['course_id'] ?? ($courses[0]['id'] ?? null);
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Подача заявки</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
        function updateReviews() {
            // Устанавливаем ID курса в скрытую форму и отправляем её для обновления отзывов
            document.getElementById('hidden-course-id').value = document.getElementById('main-course-select').value;
            document.getElementById('update-form').submit();
        }
    </script>
</head>

<body>
    <header>
        <h1>Подать заявку на курс</h1>
    </header>
    <nav style="text-align: center; margin: 10px;"><a href="../index.php">🏠 На главную</a></nav>

    <?php if ($message): ?>
        <div style="padding: 15px; border: 1px solid #ccc; background-color: #f9f9f9; max-width: 600px; margin: 10px auto; border-radius: 5px;">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form id="update-form" method="post" style="display: none;">
        <input type="hidden" name="action" value="update_reviews">
        <input type="hidden" name="course_id" id="hidden-course-id">
    </form>

    <form method="post">
        <input type="hidden" name="action" value="submit_application">

        <label><strong>Выберите курс:</strong></label><br>
        <select name="course_id" id="main-course-select" onchange="updateReviews();" >
            <?php foreach ($courses as $row): ?>
                <option value="<?= $row['id'] ?>" <?= ($row['id'] == $selected_course_id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>

        <label><strong>Желаемая дата начала:</strong></label><br>
        <input type="date" name="start_date" required min="<?= date('Y-m-d') ?>" style="width: 94%; padding: 10px; margin-top: 5px;">
        <br><br>

        <label><strong>Способ оплаты:</strong></label><br>
        <select name="payment_method_id">
            <option value="" disabled selected>-- Выберите способ --</option>
            <?php foreach ($payments as $pm): ?>
                <option value="<?= $pm['id'] ?>"><?= htmlspecialchars($pm['method_name']) ?></option>
            <?php endforeach; ?>
        </select>
        <br><br>

        <input type="submit" value="Отправить заявку" class="btn" style="width: 100%; padding: 12px; cursor: pointer;">
    </form>

    <hr style="margin: 30px 0;">

    <?php if ($selected_course_id) display_reviews($conn, $selected_course_id); ?>
</body>

</html>