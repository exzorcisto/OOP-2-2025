<?php
// admin/index.php (ООП версия с улучшениями по ТЗ)
session_start();
include_once '../models/Database.php';
include_once '../models/Application.php';

if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 2) {
    die("Доступ запрещен");
}

$database = new Database();
$db = $database->getConnection();
$application = new Application($db);

// 1. Обработка действия с уведомлением
if (isset($_POST['action']) && $_POST['action'] == 'complete' && isset($_POST['app_id'])) {
    $application->id = $_POST['app_id'];
    if ($application->updateStatus('completed')) {
        $_SESSION['flash_msg'] = "Заявка #" . $application->id . " успешно завершена!";
        header("Location: index.php");
        exit;
    }
}

// 2. Параметры для пагинации и фильтрации
$status_filter = $_GET['status'] ?? null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5; // Количество записей на страницу
$offset = ($page - 1) * $limit;

$total_rows = $application->countAll($status_filter);
$total_pages = ceil($total_rows / $limit);

// 3. Получение данных
$stmt = $application->getAllWithDetails($status_filter, $limit, $offset);
$applications_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <link rel="stylesheet" href="../css/style.css">
    <title>Админ панель: Управление</title>
</head>

<body>
    <header>
        <h1>Управление Заявками</h1>
    </header>
    <a href="../index.php">На главную</a>

    <?php if (isset($_SESSION['flash_msg'])): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; margin: 10px 0; border-radius: 5px;">
            <?= $_SESSION['flash_msg'];
            unset($_SESSION['flash_msg']); ?>
        </div>
    <?php endif; ?>

    <div style="margin: 20px 0;">
        <form method="GET">
            <label>Статус:</label>
            <select name="status" onchange="this.form.submit()">
                <option value="">Все статусы</option>
                <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>В ожидании</option>
                <option value="completed" <?= $status_filter == 'completed' ? 'selected' : '' ?>>Завершенные</option>
            </select>
        </form>
    </div>

    <table border="1" width="100%">
        <tr>
            <th>ID</th>
            <th>ФИО</th>
            <th>Курс</th>
            <th>Дата старта</th>
            <th>Статус</th>
            <th>Действие</th>
        </tr>
        <?php foreach ($applications_list as $row): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['fio_user']) ?></td>
                <td><?= htmlspecialchars($row['course_title']) ?></td>
                <td><?= $row['start_date'] ? date("d.m.Y", strtotime($row['start_date'])) : '—' ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td>
                    <?php if ($row['status'] != 'completed'): ?>
                        <form method="POST" onsubmit="return confirm('Вы уверены, что хотите завершить курс для этого пользователя?');">
                            <input type="hidden" name="app_id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="action" value="complete">
                            <input type="submit" value="Завершить">
                        </form>
                    <?php else: ?>
                        ✅ Завершено
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <div style="margin-top: 20px;">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>&status=<?= $status_filter ?>"
                style="padding: 5px 10px; text-decoration: none; border: 1px solid #ccc; <?= $page == $i ? 'background: #eee;' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
</body>

</html>