<?php

require_once __DIR__ . '/../../Middleware/roleMiddleware.php';

RoleMiddleware::requireAnyRole([3, 5]);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная официанта</title>
</head>
<body>

<?php require_once '../../../include/header.php';?>

<h1>Главная официанта</h1>

<ul>
    <li><a href="/Cafe/app/Views/waiters/tables.php">Выбрать стол и работать с заказом</a></li>
    <li><a href="/Cafe/app/Views/users/menu.php">Посмотреть меню</a></li>
</ul>

</body>
</html>
