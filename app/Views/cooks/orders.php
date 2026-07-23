<?php

    require_once __DIR__ . '/../../Services/OrderService.php';
    require_once __DIR__ . '/../../Services/DishService.php';
    require_once __DIR__ . '/../../sessions/Sessions.php';
    require_once __DIR__ . '/../../Middleware/roleMiddleware.php';

    RoleMiddleware::requireAnyRole([2, 4, 5]);

    $orders = OrderService::getForCook();
    $dishes = Dish::getAllWithCategories();

    $cookError = $_SESSION['cook_error'] ?? null;

    unset($_SESSION['cook_error']);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Страница повара</title>
</head>
<body>

<?php require_once '../../../include/header.php';?>

<h1>Заказы кухни</h1>

<div id="ajax-message">
    <?php if($cookError):?>
        <p><?=htmlspecialchars($cookError, ENT_QUOTES, 'UTF-8')?></p>
    <?php endif;?>

    <?php if(isset($_GET['updated'])):?>
        <p>Статус блюда изменён</p>
    <?php endif;?>

    <?php if(isset($_GET['stopUpdated'])):?>
        <p>Стоп-лист обновлён</p>
    <?php endif;?>

    <?php if(isset($_GET['error'])):?>
        <p>Не удалось выполнить действие</p>
    <?php endif;?>
</div>

<label for="order-status-filter">Фильтр по статусу:</label>
<select id="order-status-filter">
    <option value="all">Все</option>
    <option value="new">Новые</option>
    <option value="cooking">Готовятся</option>
    <option value="ready">Готовы</option>
</select>

<div id="kitchen-orders">
    <?php require __DIR__ . '/_orders.php';?>
</div>

<h1>Управление стоп-листом</h1>

<?php if(empty($dishes)):?>
    <p>Блюд нет</p>
<?php else:?>
    <?php foreach($dishes as $dish):?>
        <div>
            <img src="/Cafe/public/uploads/dish/<?=htmlspecialchars($dish['photo'], ENT_QUOTES, 'UTF-8')?>" alt="<?=htmlspecialchars($dish['name'], ENT_QUOTES, 'UTF-8')?>" width="150">
            <h3><?=htmlspecialchars($dish['name'], ENT_QUOTES, 'UTF-8')?></h3>
            <p>Категория:<?=htmlspecialchars($dish['categoryName'], ENT_QUOTES, 'UTF-8')?></p>
            <p>Состояние:<?=(int)$dish['isStopped']===1 ? 'В стоп-листе' : 'Доступно для заказа'?></p>

            <form class="ajax-stop-list" action="/Cafe/app/Controlers/CookController.php?action=updateStopList" method="post">
                <?=Csrf::input()?>
                <input type="hidden" name="dishId" value="<?=(int)$dish['id']?>">
                <input type="hidden" name="isStopped" value="<?=(int)$dish['isStopped']===1 ? 0 : 1?>">
                <button type="submit"><?=(int)$dish['isStopped']===1 ? 'Вернуть в меню' : 'Добавить в стоп-лист'?></button>
            </form>
        </div>

        <hr>
    <?php endforeach;?>
<?php endif;?>

</body>
</html>
