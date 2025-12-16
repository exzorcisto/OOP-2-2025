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
    <a href="../index.php">На главную</a>
    <form action="./login_handler.php" method="post">
        <input type="email" name="login" placeholder="Email" required>
        <input type="password" name="password" placeholder="Пароль" required>
        <input type="submit" value="Войти">
    </form>
</body>

</html>