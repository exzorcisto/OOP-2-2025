<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Лаб 3</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
</head>

<body>
    <header>
        <h1>Регистрация</h1>
    </header>
    <a href="../index.php">Назад</a>
    <form action="./register_handler.php" method="post">
        <input type="email" name="login" id="" placeholder="Email">
        <input type="text" name="fio_user" id="" placeholder="Fio user">
        <input type="text" name="number" id="" placeholder="Number">
        <input type="password" name="password" id="" placeholder="Password">
        <input type="submit" value="NEXT">
    </form>
</body>

</html>