<?php
session_start();
include('../db/db.php');

if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 2) {
    die("Доступ запрещен");
}

// 1. Обработка действия (подтверждение завершения)
if (isset($_POST['action']) && $_POST['action'] == 'complete' && isset($_POST['app_id'])) {
    $app_id = $_POST['app_id'];
    $stmt = $conn->prepare("UPDATE applications SET status = 'completed' WHERE id = ?");
    $stmt->bind_param("i", $app_id);
    $stmt->execute();
    header("Location: index.php"); // Перезагружаем страницу после действия
    exit;
}


// 2. Запрос на получение всех заявок
$sql = "SELECT 
            a.id, 
            u.fio_user, 
            c.title AS course_title, 
            a.status,
            a.created_at 
        FROM applications a 
        JOIN users u ON a.user_id = u.id 
        JOIN courses c ON a.course_id = c.id
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
    <a href="../index.php">На главную</a>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Пользователь</th>
            <th>Курс</th>
            <th>Статус</th>
            <th>Действие</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
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
        <?php endwhile; ?>
    </table>
</body>

</html>