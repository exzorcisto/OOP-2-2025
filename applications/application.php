<?php
// applications/application.php
session_start();
include_once '../models/Database.php';
include_once '../models/Course.php';
include_once '../models/Review.php';
include_once '../models/Application.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

$course_obj = new Course($db);
$review_obj = new Review($db);
$application_obj = new Application($db);

$message = null;

// Обработка отправки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_application') {
    if (empty($_POST['start_date']) || empty($_POST['payment_method_id'])) {
        $message = "⚠️ Заполните все поля!";
    } else {
        $application_obj->user_id = $_SESSION['user_id'];
        $application_obj->course_id = $_POST['course_id'];
        $application_obj->start_date = $_POST['start_date'];
        $application_obj->payment_method_id = $_POST['payment_method_id'];
        $application_obj->status_id = 1;

        if ($application_obj->checkActiveApplication()) {
            $message = "⚠️ У вас уже есть активная заявка.";
        } else {
            if ($application_obj->create()) {
                $message = "✅ Заявка отправлена!";
            }
        }
    }
}

$courses = $course_obj->readAll()->fetchAll(PDO::FETCH_ASSOC);
$payments = $application_obj->getPaymentMethods()->fetchAll(PDO::FETCH_ASSOC);
$selected_course_id = $_POST['course_id'] ?? ($courses[0]['id'] ?? null);

// Функция отображения отзывов с ОЦЕНКОЙ
function display_reviews($review_obj, $course_id)
{
    $review_obj->course_id = $course_id;
    $stmt = $review_obj->getReviewsByCourseId();
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo '<h2>Отзывы о курсе</h2>';
    if (count($reviews) > 0) {
        echo '<ul style="list-style:none; padding:0;">';
        foreach ($reviews as $rev) {
            $stars = str_repeat('⭐', $rev['rating']);
            echo "<li style='border-bottom:1px solid #ddd; margin-bottom:10px; padding-bottom:10px;'>";
            echo "<strong>" . htmlspecialchars($rev['fio_user']) . "</strong> ";
            echo "<span style='color:orange;'>$stars</span><br>"; // Вывод оценки
            echo "<em>" . htmlspecialchars($rev['comment']) . "</em>";
            echo "</li>";
        }
        echo '</ul>';
    } else {
        echo '<p>Отзывов пока нет.</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Заявка на курс</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
        function updateReviews() {
            document.getElementById('hidden-course-id').value = document.getElementById('main-course-select').value;
            document.getElementById('update-form').submit();
        }
    </script>
</head>

<body>
    <header>
        <h1>Заявка на обучение</h1>
    </header>
    <nav style="text-align:center;"><a href="../index.php">🏠 На главную</a></nav>

    <?php if ($message) echo "<p style='text-align:center;'>$message</p>"; ?>

    <form id="update-form" method="post" style="display:none;">
        <input type="hidden" name="action" value="update_reviews">
        <input type="hidden" name="course_id" id="hidden-course-id">
    </form>
    <form method="post">
        <input type="hidden" name="action" value="submit_application">

        <label>Курс:</label><br>
        <select name="course_id" id="main-course-select" onchange="updateReviews()" style="width:100%;">
            <?php foreach ($courses as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $c['id'] == $selected_course_id ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['title']) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Дата старта:</label><br>
        <input type="date" name="start_date" required min="<?= date('Y-m-d') ?>" style="width:100%;"><br><br>

        <label>Способ оплаты:</label><br>
        <select name="payment_method_id" required style="width:100%;">
            <option value="" disabled selected>-- Выберите способ --</option>
            <?php foreach ($payments as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['method_name']) ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <input type="submit" value="Подать заявку" class="btn" style="width:100%;">
    </form>

    <hr style="margin:20px 0;">
    <?php if ($selected_course_id) display_reviews($review_obj, $selected_course_id); ?>
</body>

</html>