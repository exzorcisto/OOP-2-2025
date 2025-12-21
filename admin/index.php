<?php
// admin/index.php
session_start();
include_once '../models/Database.php';
include_once '../models/Application.php';

// Проверка прав (админ - role_id 2)
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 2) {
    die("Доступ запрещен. Требуются права администратора.");
}

$database = new Database();
$db = $database->getConnection();
$application = new Application($db);

// 1. ОБРАБОТКА ИЗМЕНЕНИЯ СТАТУСА (из выпадающего списка)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $application->id = $_POST['app_id'];
    $new_status_id = $_POST['status_id'];
    
    if ($application->updateStatus($new_status_id)) {
        $_SESSION['flash_msg'] = "Статус заявки #{$application->id} успешно обновлен!";
        header("Location: index.php?" . $_SERVER['QUERY_STRING']);
        exit;
    }
}

// 2. ФИЛЬТРАЦИЯ И ПАГИНАЦИЯ
$status_filter = $_GET['status'] ?? null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5; 
$offset = ($page - 1) * $limit;

$total_rows = $application->countAll($status_filter);
$total_pages = ceil($total_rows / $limit);

$stmt = $application->readAll($status_filter, $limit, $offset);
$applications_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем все возможные статусы для выпадающих списков
$statuses_stmt = $db->query("SELECT * FROM statuses ORDER BY id ASC");
$all_statuses = $statuses_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель администратора</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header><h1>Управление заявками</h1></header>
    
    <nav style="text-align: center; margin: 15px;">
        <a href="../index.php">🏠 На сайт</a> | <strong>Админ-панель</strong>
    </nav>

    <div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
        
        <?php if (isset($_SESSION['flash_msg'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border: 1px solid #c3e6cb; text-align:center;">
                <?= $_SESSION['flash_msg']; unset($_SESSION['flash_msg']); ?>
            </div>
        <?php endif; ?>

        <div style="background: #f4f4f4; padding: 15px; margin-bottom: 20px;">
            <form method="GET">
                <label>Показать только:</label>
                <select name="status" onchange="this.form.submit()">
                    <option value="">Все статусы</option>
                    <?php foreach ($all_statuses as $st): ?>
                        <option value="<?= $st['id'] ?>" <?= $status_filter == $st['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($st['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <a href="index.php" style="margin-left: 10px;">Сбросить</a>
            </form>
        </div>

        <table border="1" width="100%" style="border-collapse: collapse;">
            <tr style="background: #eee;">
                <th>ID</th>
                <th>ФИО студента</th>
                <th>Название курса</th>
                <th>Дата начала</th>
                <th>Тип оплаты</th>
                <th>Дата создания</th> <th>Статус заявки</th> </tr>
            <?php foreach ($applications_list as $row): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['fio_user']) ?></td>
                    <td><?= htmlspecialchars($row['course_title']) ?></td>
                    <td align="center"><?= $row['start_date'] ? date("d.m.Y", strtotime($row['start_date'])) : '—' ?></td>
                    <td><?= htmlspecialchars($row['payment_method'] ?? '—') ?></td>
                    <td align="center"><?= date("d.m.Y H:i", strtotime($row['created_at'])) ?></td> <td>
                        <form method="POST" style="width: 120px; padding: 0">
                            <input type="hidden" name="update_status" value="1">
                            <input type="hidden" name="app_id" value="<?= $row['id'] ?>">
                            <select name="status_id" onchange="this.form.submit()" style="width: 100%; padding: 5px;">
                                <?php foreach ($all_statuses as $st): ?>
                                    <option value="<?= $st['id'] ?>" <?= ($row['status_id'] == $st['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($st['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <div style="margin-top: 20px; text-align: center;">
            Страницы:
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?>&status=<?= $status_filter ?>" 
                   style="padding: 5px 10px; border: 1px solid #ccc; text-decoration: none; <?= $page == $i ? 'background: #007bff; color: white;' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
</body>
</html>