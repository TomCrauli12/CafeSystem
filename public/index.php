<?php

require_once __DIR__ . '/../app/Middleware/roleMiddleware.php';

if(AuthMiddleware::isAuthenticated()){

    RoleMiddleware::redirectToRoleIndex();
}

require_once __DIR__ . '/../include/header.php';

?>
    <a href="../app/Views/auth/register.php">Регестрация</a>
    <a href="../app/Views/auth/login.php">Авторизация</a>
