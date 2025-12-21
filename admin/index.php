<?php
session_start();
include('../db/db.php');

if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 2) {
    die("Доступ запрещен");
}

// 1. ОБРАБОТКА ИЗМЕНЕНИЯ СТАТУСА
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $app_id = intval($_POST['app_id']);
    $new_status_id = intval($_POST['new_status_id']);

    $stmt = $conn->prepare("UPDATE applications SET status_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status_id, $app_id);

    if ($stmt->execute()) {
        $_SESSION['msg'] = "Статус заявки #$app_id успешно обновлен!";
    } else {
        $_SESSION['msg'] = "Ошибка при обновлении: " . $conn->error;
    }
    header("Location: index.php");
    exit;
}

// 2. Логика фильтрации
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$where_clause = "";
if ($status_filter) {
    $where_clause = " WHERE a.status_id = " . intval($status_filter);
}

// 3. Получение данных с JOIN для новых таблиц
$sql = "SELECT 
            a.id, 
            u.fio_user, 
            c.title AS course_title, 
            a.start_date,
            a.status_id,
            s.name AS status_name,
            pm.method_name AS payment_method,
            a.created_at 
        FROM applications a 
        JOIN users u ON a.user_id = u.id 
        JOIN courses c ON a.course_id = c.id
        JOIN statuses s ON a.status_id = s.id
        JOIN payment_methods pm ON a.payment_method_id = pm.id
        $where_clause
        ORDER BY a.created_at DESC";

$result = $conn->query($sql);
$status_options = $conn->query("SELECT * FROM statuses")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/style.css">
    <title>Админ панель: Заявки</title>
    <style>
        .status-select {
            padding: 5px;
            border-radius: 4px;
            cursor: pointer;
        }

        .status-1 {
            border: 1px solid orange;
        }

        /* Новое */
        .status-2 {
            border: 1px solid blue;
        }

        /* В процессе */
        .status-3 {
            border: 1px solid green;
        }

        /* Завершено */
    </style>
</head>

<body>
    <header>
        <h1>Управление Заявками</h1>
        <nav><a href="../index.php" style="color: white; text-decoration: none;">🏠 На главную</a></nav>
    </header>

    <?php if (isset($_SESSION['msg'])): ?>
        <div style="background: #e3f2fd; padding: 10px; margin: 10px 0; border-left: 5px solid #2196f3;">
            <?= $_SESSION['msg'];
            unset($_SESSION['msg']); ?>
        </div>
    <?php endif; ?>

    <div class="toolbar" style="margin-bottom: 20px;">
        <form method="GET">
            <label>Фильтр по статусу:</label>
            <select name="status" onchange="this.form.submit()">
                <option value="">Все статусы</option>
                <?php foreach ($status_options as $st): ?>
                    <option value="<?= $st['id'] ?>" <?= $status_filter == $st['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($st['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <a href="index.php">Сбросить</a>
        </form>
    </div>

    <table border="1" cellpadding="10" style="width: 100%; border-collapse: collapse;">
        <tr style="background: #f4f4f4;">
            <th>ID</th>
            <th>Пользователь</th>
            <th>Курс</th>
            <th>Дата старта</th>
            <th>Оплата</th>
            <th>Статус (изменить)</th>
            <th>Дата создания</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['fio_user']) ?></td>
                <td><?= htmlspecialchars($row['course_title']) ?></td>
                <td><?= $row['start_date'] ? date("d.m.Y", strtotime($row['start_date'])) : '—' ?></td>
                <td><?= htmlspecialchars($row['payment_method']) ?></td>
                <td>
                    <form method="POST" style="margin:0; padding:0; background: none">
                        <input type="hidden" name="update_status" value="1">
                        <input type="hidden" name="app_id" value="<?= $row['id'] ?>">
                        <select name="new_status_id" class="status-select status-<?= $row['status_id'] ?>" onchange="this.form.submit()">
                            <?php foreach ($status_options as $option): ?>
                                <option value="<?= $option['id'] ?>" <?= ($option['id'] == $row['status_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($option['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </td>
                <td><?= date("d.m.Y H:i", strtotime($row['created_at'])) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>

</html>