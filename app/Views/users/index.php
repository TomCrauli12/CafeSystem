<?php

require_once __DIR__ . '/../../Middleware/roleMiddleware.php';

RoleMiddleware::requireAnyRole([1, 5]);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная пользователя</title>
</head>
<body>

<?php require_once '../../../include/header.php';?>

<h1>Главная пользователя</h1>

<ul>
    <li><a href="/Cafe/app/Views/users/menu.php">Посмотреть меню</a></li>
    <li><a href="/Cafe/app/Views/users/basket.php">Корзина и мои заказы</a></li>
    <li><a href="/Cafe/app/Views/users/reservation.php">Забронировать стол</a></li>
</ul>

</body>
</html>
