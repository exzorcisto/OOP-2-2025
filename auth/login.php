<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <header>
        <h1>Авторизация</h1>
    </header>
    <a href="../index.php">Назад</a>

    <?php if (isset($_GET['registration_success'])): ?>
        <p style="color: green; font-weight: bold;">✅ Вы успешно зарегистрированы! Войдите в систему.</p>
    <?php endif; ?>

    <form action="./login_handler.php" method="post">
        <input type="email" name="login" id="" placeholder="Email" required>
        <input type="password" name="password" id="" placeholder="Password" required>
        <input type="submit" value="Войти">
    </form>
    <p>Еще нет аккаунта? <a href="./register.php">Зарегистрироваться</a></p>
</body>

</html>