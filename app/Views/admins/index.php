<?php

require_once __DIR__ . '/../../Middleware/roleMiddleware.php';

RoleMiddleware::requireRole(5);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная администратора</title>
</head>
<body>

<?php require_once '../../../include/header.php';?>

<h1>Главная администратора</h1>

<h2>Администрирование</h2>

<ul>
    <li><a href="/Cafe/app/Views/admins/users.php">Пользователи и роли</a></li>
    <li><a href="/Cafe/app/Views/admins/history.php">История действий</a></li>
</ul>

<h2>Функции менеджера</h2>

<ul>
    <li><a href="/Cafe/app/Views/managers/tables.php">Управление столами</a></li>
    <li><a href="/Cafe/app/Views/managers/reservations.php">Бронирования</a></li>
    <li><a href="/Cafe/app/Views/managers/orders.php">Назначение официантов</a></li>
    <li><a href="/Cafe/app/Views/managers/statistics.php">Статистика</a></li>
</ul>

<h2>Функции официанта</h2>

<ul>
    <li><a href="/Cafe/app/Views/waiters/tables.php">Столы и заказы</a></li>
</ul>

<h2>Функции повара</h2>

<ul>
    <li><a href="/Cafe/app/Views/cooks/orders.php">Заказы кухни и стоп-лист</a></li>
    <li><a href="/Cafe/app/Views/cooks/Category.php">Категории</a></li>
    <li><a href="/Cafe/app/Views/cooks/createCategory.php">Создать категорию</a></li>
    <li><a href="/Cafe/app/Views/cooks/createDish.php">Создать блюдо</a></li>
</ul>

<h2>Функции пользователя</h2>

<ul>
    <li><a href="/Cafe/app/Views/users/menu.php">Меню</a></li>
    <li><a href="/Cafe/app/Views/users/basket.php">Корзина и заказы</a></li>
    <li><a href="/Cafe/app/Views/users/reservation.php">Бронирование столика</a></li>
</ul>

</body>
</html>
