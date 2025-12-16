<?php
// admin/index.php (ООП версия)
session_start();

// Подключаем классы
include_once '../models/Database.php';
include_once '../models/Application.php';

// Проверка на админа
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 2) {
    die("Доступ запрещен");
}

// Создание объектов
$database = new Database();
$db = $database->getConnection();
$application = new Application($db);


// 1. Обработка действия (подтверждение завершения)
if (isset($_POST['action']) && $_POST['action'] == 'complete' && isset($_POST['app_id'])) {
    $application->id = $_POST['app_id'];

    if ($application->updateStatus('completed')) {
        header("Location: index.php");
        exit;
    }
}


// 2. Получение всех заявок с деталями
$stmt = $application->getAllApplicationsWithDetails();
$applications_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <link rel="stylesheet" href="../css/style.css">
    <title>Админ панель: Заявки</title>
</head>

<body>
    <header>
        <h1>Управление Заявками</h1>
    </header>
    <a href="../index.php">На главную</a>
    <table border="1">
        <?php foreach ($applications_list as $row): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['fio_user']) ?></td>
                <td><?= htmlspecialchars($row['course_title']) ?></td>
                <td><?= $row['status'] ?></td>
                <td>
                    <?php if ($row['status'] != 'completed'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="app_id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="action" value="complete">
                            <input type="submit" value="Завершить курс">
                        </form>
                    <?php else: ?>
                        Курс завершен
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>

</html>

</html>