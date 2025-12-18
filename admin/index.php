<?php
session_start();
include('../db/db.php');

if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 2) {
    die("Доступ запрещен");
}

// 1. Обработка действия с уведомлением
if (isset($_POST['action']) && $_POST['action'] == 'complete' && isset($_POST['app_id'])) {
    $app_id = $_POST['app_id'];
    $stmt = $conn->prepare("UPDATE applications SET status = 'completed' WHERE id = ?");
    $stmt->bind_param("i", $app_id);

    if ($stmt->execute()) {
        $_SESSION['msg'] = "Заявка #$app_id успешно завершена!";
    }
    header("Location: index.php");
    exit;
}

// 2. Логика фильтрации
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$where_clause = "";
if ($status_filter) {
    $where_clause = " WHERE a.status = '" . $conn->real_escape_string($status_filter) . "'";
}

// 3. Запрос с учетом фильтра
$sql = "SELECT 
            a.id, 
            u.fio_user, 
            c.title AS course_title, 
            a.start_date,
            a.status,
            a.created_at 
        FROM applications a 
        JOIN users u ON a.user_id = u.id 
        JOIN courses c ON a.course_id = c.id
        $where_clause
        ORDER BY a.created_at DESC";

$result = $conn->query($sql);
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

    <?php if (isset($_SESSION['msg'])): ?>
        <div class="alert success" style="background: #d4edda; padding: 10px; margin: 10px 0;">
            <?= $_SESSION['msg'];
            unset($_SESSION['msg']); ?>
        </div>
    <?php endif; ?>

    <div class="toolbar" style="margin-bottom: 20px;">
        <form method="GET">
            <label>Фильтр по статусу:</label>
            <select name="status" onchange="this.form.submit()">
                <option value="">Все</option>
                <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>В ожидании</option>
                <option value="completed" <?= $status_filter == 'completed' ? 'selected' : '' ?>>Завершенные</option>
            </select>
            <a href="index.php">Сбросить</a>
        </form>
    </div>

    <table border="1">
        <tr>
            <th>ID</th>
            <th>Пользователь</th>
            <th>Курс</th>
            <th>Дата старта</th>
            <th>Статус</th>
            <th>Действие</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['fio_user']) ?></td>
                <td><?= htmlspecialchars($row['course_title']) ?></td>
                <td><?= $row['start_date'] ? date("d.m.Y", strtotime($row['start_date'])) : '—' ?></td>
                <td><?= $row['status'] ?></td>
                <td>
                    <?php if ($row['status'] != 'completed'): ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Завершить обучение для этого пользователя?');">
                            <input type="hidden" name="app_id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="action" value="complete">
                            <input type="submit" value="Завершить курс">
                        </form>
                    <?php else: ?>
                        ✅ Завершено
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>

</html>