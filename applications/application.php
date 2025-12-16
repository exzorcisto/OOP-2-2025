<?php
// applications/application.php
session_start();
// Подключаем классы
include_once '../models/Database.php';
include_once '../models/Course.php';
include_once '../models/Review.php';
include_once '../models/Application.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

// Создание объектов
$database = new Database();
$db = $database->getConnection();
$course = new Course($db);
$review_obj = new Review($db);
$application_obj = new Application($db);

// Сообщение о результате операции (если оно было установлено ранее)
$message = null;

// ===============================================
// 1. ФУНКЦИЯ ОТОБРАЖЕНИЯ ОТЗЫВОВ (ООП)
// ===============================================

function display_reviews($review_obj, $course_id)
{
    // Устанавливаем ID курса для выборки
    $review_obj->course_id = $course_id;
    $stmt = $review_obj->getReviewsByCourseId();
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo '<h2>Отзывы о курсе</h2>';

    if (count($reviews) > 0) {
        echo '<ul class="review-list">';
        foreach ($reviews as $review) {
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

// ===============================================
// 2. ОБРАБОТКА POST (ОТПРАВКА ЗАЯВКИ ИЛИ ОБНОВЛЕНИЕ ОТЗЫВОВ)
// ===============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $application_obj->course_id = $_POST['course_id'];

    if (isset($_POST['action']) && $_POST['action'] === 'submit_application') {
        // --- Логика отправки заявки ---
        $application_obj->user_id = $_SESSION['user_id'];

        if ($application_obj->checkActiveApplication()) {
            $message = "⚠️ У вас уже есть активная заявка на этот курс.";
        } elseif ($application_obj->create()) {
            $message = "✅ Заявка успешно отправлена и ожидает рассмотрения!";
        } else {
            $message = "❌ Ошибка при отправке заявки.";
        }
    }
    // Если action == update_reviews, мы просто переходим к п.3, 
    // чтобы обновился $selected_course_id и отобразились отзывы.
}

// ===============================================
// 3. ПОЛУЧЕНИЕ ДАННЫХ ДЛЯ ВЫВОДА
// ===============================================

// Получаем список курсов
$course_stmt = $course->readAll();
$courses = $course_stmt->fetchAll(PDO::FETCH_ASSOC);

$selected_course_id = null;
if (!empty($courses)) {
    // Определяем ID выбранного курса (для отображения отзывов)
    if (isset($_POST['course_id'])) {
        $selected_course_id = $_POST['course_id'];
    } else {
        $selected_course_id = $courses[0]['id']; // Первый курс по умолчанию
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
            // Отправляет форму "update-form"
            document.getElementById('update-form').submit();
        }
    </script>
</head>

<body>
    <header>
        <h1>Подача заявки на курс</h1>
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
            <input type="submit" value="Отправить заявку на курс">
        <?php else: ?>
            <p>Нельзя подать заявку, пока администратор не добавит курсы.</p>
        <?php endif; ?>
    </form>

    <hr>

    <?php
    if ($selected_course_id) {
        display_reviews($review_obj, $selected_course_id);
    }
    ?>
</body>

</html>