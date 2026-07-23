<?php

require_once __DIR__ . '/../../Middleware/roleMiddleware.php';

RoleMiddleware::requireAnyRole([2, 5]);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная повара</title>
</head>
<body>

<?php require_once '../../../include/header.php';?>

<h1>Главная повара</h1>

<ul>
    <li><a href="/Cafe/app/Views/cooks/orders.php">Заказы кухни и стоп-лист</a></li>
    <li><a href="/Cafe/app/Views/users/menu.php">Меню</a></li>
    <li><a href="/Cafe/app/Views/cooks/Category.php">Категории</a></li>
    <li><a href="/Cafe/app/Views/cooks/createCategory.php">Создать категорию</a></li>
    <li><a href="/Cafe/app/Views/cooks/createDish.php">Создать блюдо</a></li>
</ul>

</body>
</html>
