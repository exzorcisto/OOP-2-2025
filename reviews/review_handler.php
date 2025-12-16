<?php
session_start();
include('../db/db.php');
// ... проверки сессии ...

$user_id = $_SESSION['user_id'];

// Получаем только те курсы, по которым у пользователя есть заявка со статусом 'completed' 
// и на которые он ЕЩЕ не оставил отзыв.
$sql = "SELECT 
            c.id, c.title 
        FROM courses c
        JOIN applications a ON a.course_id = c.id
        LEFT JOIN reviews r ON r.course_id = c.id AND r.user_id = a.user_id
        WHERE a.user_id = ? AND a.status = 'completed' AND r.id IS NULL";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$courses_to_review = $stmt->get_result();
?>

<select name="course_id" required>
    <?php if ($courses_to_review->num_rows > 0): ?>
        <?php while ($row = $courses_to_review->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></option>
        <?php endwhile; ?>
    <?php else: ?>
        <option value="" disabled selected>Нет курсов для отзыва (нужно завершить курс)</option>
    <?php endif; ?>
</select>

<?php if ($courses_to_review->num_rows == 0): ?>
    <p style="color: grey;">У вас пока нет завершенных курсов, на которые можно оставить отзыв.</p>
<?php endif; ?>