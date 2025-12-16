<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Лаб 3 - Главная</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
    <header>
        <h1>Учебный портал (Лаб 3)</h1>
    </header>

    <main>
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="user-panel">
                <h2>Привет, <?= htmlspecialchars($_SESSION['fio']) ?>!</h2>

                <nav>
                    <ul>
                        <li><a href="./profile.php">👤 Мой профиль</a></li>
                        <li><a href="./applications/application.php">📝 Подать заявку на курс</a></li>
                        <li><a href="./reviews/reviews.php">⭐ Оставить отзыв</a></li>

                        <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 2): ?>
                            <li><a href="./admin/index.php" style="color: red; font-weight: bold;">⚙️ Админ-панель</a></li>
                        <?php endif; ?>

                        <li><a href="./auth/logout.php">Выход</a></li>
                    </ul>
                </nav>
            </div>

        <?php else: ?>
            <div class="guest-panel">
                <p>Вы не авторизованы.</p>
                <a href="./auth/login.php" class="btn">Войти</a>
                <a href="./auth/register.php" class="btn">Регистрация</a>
            </div>
        <?php endif; ?>
    </main>
</body>

</html>