<?php
session_start();
include('../db/db.php');

if (!isset($_SESSION['user_id'])) {
    exit("Авторизуйтесь");
}

$user_id = $_SESSION['user_id'];

// Исправлено: использование status_id = 3 (Завершено)
$sql = "SELECT 
            c.id, c.title 
        FROM courses c
        JOIN applications a ON a.course_id = c.id
        LEFT JOIN reviews r ON r.course_id = c.id AND r.user_id = a.user_id
        WHERE a.user_id = ? 
          AND a.status_id = 3 
          AND r.id IS NULL";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$courses_to_review = $stmt->get_result();
?>

<select name="course_id" required style="width: 100%; padding: 10px;">
    <?php if ($courses_to_review->num_rows > 0): ?>
        <?php while ($row = $courses_to_review->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></option>
        <?php endwhile; ?>
    <?php else: ?>
        <option value="" disabled selected>Нет доступных завершенных курсов</option>
    <?php endif; ?>
</select>