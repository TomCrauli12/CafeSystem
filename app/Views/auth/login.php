<?php
    require_once __DIR__ . '/../../sessions/Sessions.php';
    require_once __DIR__ . '/../../Middleware/userMeddleware.php';
    require_once __DIR__ . '/../../Middleware/CsrfMiddleware.php';

    session::start();
    AuthMiddleware::requireGuest();

    $loginError = $_SESSION['loginError'] ?? null;

    unset($_SESSION['loginError']);


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php if($loginError):?>
        <p><?=htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8')?></p>
    <?php endif;?>

    <?php if(isset($_GET['roleError'])):?>
        <p>У пользователя не указана роль. Обратитесь к администратору</p>
    <?php endif;?>

    <form action="../../Controlers/AuthController.php?action=indefication" method="post">
        <?=Csrf::input()?>

        <label for="login">Логин:</label>
        <input type="text" name="login" id="login" minlength="2" maxlength="20" required>

        <label for="password">Пароль:</label>
        <input type="password" name="password" id="password" minlength="2" maxlength="20" required>

        <button type="submit">Войти</button>
    </form>

    <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
</body>
</html>
