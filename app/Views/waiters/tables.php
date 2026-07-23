<?php

require_once __DIR__ . '/../../Services/WaiterService.php';
require_once __DIR__ . '/../../sessions/Sessions.php';
require_once __DIR__ . '/../../Middleware/roleMiddleware.php';

RoleMiddleware::requireAnyRole([3, 4, 5]);

$waiterId = (int)session::get('user_id');

$tableId = (int)($_GET['tableId'] ?? 0);

$tables = WaiterService::getTables();

$selectedTable = null;

foreach($tables as $table){

    if((int)$table['id']===$tableId){

        $selectedTable = $table;

        break;
    }
}

$order = null;

$orderItems = [];

$dishes = [];

if($selectedTable && $selectedTable['orderId']){

    $order = WaiterService::getOrderByTableId($tableId);

    if($order){

        $orderItems = WaiterService::getOrderItems((int)$order['id']);

        if((int)$order['waiterId']===$waiterId){

            $dishes = WaiterService::getAvailableDishes();
        }
    }
}

$error = $_SESSION['waiter_error'] ?? null;

unset($_SESSION['waiter_error']);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Столы официанта</title>
</head>
<body>

<?php require_once '../../../include/header.php';?>

<h1>Столы</h1>

<?php if($error):?>
    <p><?=htmlspecialchars($error, ENT_QUOTES, 'UTF-8')?></p>
<?php endif;?>

<?php if(isset($_GET['started'])):?>
    <p>Обслуживание стола начато</p>
<?php endif;?>

<?php if(isset($_GET['dishAdded'])):?>
    <p>Блюдо добавлено в заказ</p>
<?php endif;?>

<?php if(isset($_GET['completed'])):?>
    <p>Обслуживание завершено</p>
<?php endif;?>

<?php if(empty($tables)):?>
    <p>Активных столов нет</p>
<?php else:?>
    <?php foreach($tables as $table):?>
        <div>
            <h2>Стол №<?=(int)$table['number']?></h2>
            <p>Количество мест:<?=(int)$table['seats']?></p>

            <?php if(!$table['orderId']):?>
                <p>Стол свободен</p>
                <a href="tables.php?tableId=<?=(int)$table['id']?>">Выбрать стол</a>
            <?php elseif($table['waiterId']===null):?>
                <p>Есть неназначенный заказ №<?=(int)$table['orderId']?></p>
                <a href="tables.php?tableId=<?=(int)$table['id']?>">Взять стол</a>
            <?php elseif((int)$table['waiterId']===$waiterId):?>
                <p>Ваш стол, заказ №<?=(int)$table['orderId']?></p>
                <p>Готово:<?=(int)$table['readyCount']?> из <?=(int)$table['itemsCount']?></p>
                <a href="tables.php?tableId=<?=(int)$table['id']?>">Открыть заказ</a>
            <?php else:?>
                <p>Стол обслуживает:<?=htmlspecialchars($table['waiterName'], ENT_QUOTES, 'UTF-8')?></p>
            <?php endif;?>
        </div>

        <hr>
    <?php endforeach;?>
<?php endif;?>

<?php if($selectedTable):?>
    <h1>Стол №<?=(int)$selectedTable['number']?></h1>

    <?php if(!$order || $order['waiterId']===null):?>
        <form action="/Cafe/app/Controlers/WaiterController.php?action=startService" method="post">
            <?=Csrf::input()?>
            <input type="hidden" name="tableId" value="<?=(int)$selectedTable['id']?>">
            <button type="submit"><?=$order ? 'Взять стол' : 'Начать обслуживание'?></button>
        </form>
    <?php elseif((int)$order['waiterId']!==$waiterId):?>
        <p>Этот стол обслуживает другой официант</p>
    <?php else:?>
        <h2>Заказ №<?=(int)$order['id']?></h2>
        <p>Клиент:<?=htmlspecialchars($order['userName'], ENT_QUOTES, 'UTF-8')?></p>
        <p>Статус:<?=htmlspecialchars($order['statusName'], ENT_QUOTES, 'UTF-8')?></p>

        <h2>Блюда в заказе</h2>

        <?php if(empty($orderItems)):?>
            <p>В заказе ещё нет блюд</p>
        <?php else:?>
            <?php foreach($orderItems as $item):?>
                <div>
                    <img src="/Cafe/public/uploads/dish/<?=htmlspecialchars($item['photo'], ENT_QUOTES, 'UTF-8')?>" alt="<?=htmlspecialchars($item['dishName'], ENT_QUOTES, 'UTF-8')?>" width="150">
                    <h3><?=htmlspecialchars($item['dishName'], ENT_QUOTES, 'UTF-8')?></h3>
                    <p>Количество:<?=(int)$item['quantity']?></p>
                    <p>Комментарий:<?=htmlspecialchars($item['comment'] ?: 'Нет', ENT_QUOTES, 'UTF-8')?></p>
                    <p>Статус:<?=htmlspecialchars($item['statusName'], ENT_QUOTES, 'UTF-8')?></p>
                </div>

                <hr>
            <?php endforeach;?>
        <?php endif;?>

        <h2>Добавить блюдо</h2>

        <?php if(empty($dishes)):?>
            <p>Доступных блюд нет</p>
        <?php else:?>
            <form action="/Cafe/app/Controlers/WaiterController.php?action=addDish" method="post">
                <?=Csrf::input()?>
                <input type="hidden" name="tableId" value="<?=(int)$selectedTable['id']?>">
                <input type="hidden" name="orderId" value="<?=(int)$order['id']?>">

                <label for="dishId">Блюдо:</label>
                <select name="dishId" id="dishId" required>
                    <option value="">Выберите блюдо</option>
                    <?php foreach($dishes as $dish):?>
                        <option value="<?=(int)$dish['id']?>"><?=htmlspecialchars($dish['categoryName'] . ' — ' . $dish['name'], ENT_QUOTES, 'UTF-8')?></option>
                    <?php endforeach;?>
                </select>

                <label for="quantity">Количество:</label>
                <input type="number" name="quantity" id="quantity" min="1" max="20" value="1" required>

                <label for="comment">Комментарий:</label>
                <textarea name="comment" id="comment" minlength="2" maxlength="20" placeholder="Например: без лука"></textarea>

                <button type="submit">Добавить в заказ</button>
            </form>
        <?php endif;?>

        <form action="/Cafe/app/Controlers/WaiterController.php?action=completeOrder" method="post">
            <?=Csrf::input()?>
            <input type="hidden" name="tableId" value="<?=(int)$selectedTable['id']?>">
            <input type="hidden" name="orderId" value="<?=(int)$order['id']?>">
            <button type="submit">Завершить обслуживание</button>
        </form>
    <?php endif;?>
<?php endif;?>

</body>
</html>
