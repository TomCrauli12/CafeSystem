<?php

    require_once __DIR__ . '/../../sessions/Sessions.php';
    require_once __DIR__ . '/../../Middleware/userMeddleware.php';
    require_once __DIR__ . '/../../Middleware/CsrfMiddleware.php';

    session::start();
    AuthMiddleware::requireGuest();

    $registerError = $_SESSION['register_error'] ?? null;

    unset($_SESSION['register_error']);

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php if($registerError):?>
        <p><?=htmlspecialchars($registerError, ENT_QUOTES, 'UTF-8')?></p>
    <?php endif;?>

    <form action="../../Controlers/RegisterController.php?action=register" method="post">
        <?=Csrf::input()?>

        <label for="login">Логин:</label>
        <input type="text" name="login" id="login" minlength="2" maxlength="20" required>

        <label for="name">Имя:</label>
        <input type="text" name="name" id="name" minlength="2" maxlength="20" required>

        <label for="phone">Номер телефона:</label>
        <input type="tel" name="phone" id="phone" minlength="2" maxlength="20" required>

        <label for="enterPassword">Пароль:</label>
        <input type="password" name="enterPassword" id="enterPassword" minlength="2" maxlength="20" required>

        <button>Создать аккаунт</button>
    </form>

    <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
</body>
</html>
