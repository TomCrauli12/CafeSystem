<?php

require_once __DIR__ . '/../../Services/ManagerService.php';
require_once __DIR__ . '/../../sessions/Sessions.php';
require_once __DIR__ . '/../../Middleware/roleMiddleware.php';

RoleMiddleware::requireAnyRole([4, 5]);

$orders = ManagerService::getActiveOrders();

$waiters = ManagerService::getWaiters();

$error = $_SESSION['manager_error'] ?? null;

unset($_SESSION['manager_error']);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Официанты заказов</title>
</head>
<body>

<?php require_once '../../../include/header.php';?>

<h1>Официанты заказов</h1>

<?php if($error):?>
    <p><?=htmlspecialchars($error, ENT_QUOTES, 'UTF-8')?></p>
<?php endif;?>

<?php if(isset($_GET['assigned'])):?>
    <p>Официант заказа изменен</p>
<?php endif;?>

<?php if(empty($waiters)):?>
    <p>Нет пользователей с ролью официанта</p>
<?php endif;?>

<?php if(empty($orders)):?>
    <p>Активных заказов нет</p>
<?php else:?>
    <?php foreach($orders as $order):?>
        <div>
            <h2>Заказ №<?=(int)$order['id']?></h2>
            <p>Стол:№<?=(int)$order['tableNumber']?></p>
            <p>Клиент:<?=htmlspecialchars($order['userName'], ENT_QUOTES, 'UTF-8')?></p>
            <p>Блюд в заказе:<?=(int)$order['itemsCount']?></p>
            <p>Статус:<?=htmlspecialchars($order['statusName'], ENT_QUOTES, 'UTF-8')?></p>
            <p>Текущий официант:<?=htmlspecialchars($order['waiterName'] ?: 'Не назначен', ENT_QUOTES, 'UTF-8')?></p>

            <?php if(!empty($waiters)):?>
                <form action="/Cafe/app/Controlers/ManagerController.php?action=assignWaiter" method="post">
                    <?=Csrf::input()?>
                    <input type="hidden" name="orderId" value="<?=(int)$order['id']?>">

                    <label for="waiterId-<?=(int)$order['id']?>">Официант:</label>
                    <select name="waiterId" id="waiterId-<?=(int)$order['id']?>" required>
                        <option value="">Выберите официанта</option>
                        <?php foreach($waiters as $waiter):?>
                            <option value="<?=(int)$waiter['id']?>" <?=(int)$order['waiterId']===(int)$waiter['id'] ? 'selected' : ''?>><?=htmlspecialchars($waiter['name'], ENT_QUOTES, 'UTF-8')?></option>
                        <?php endforeach;?>
                    </select>

                    <button type="submit">Назначить</button>
                </form>
            <?php endif;?>
        </div>

        <hr>
    <?php endforeach;?>
<?php endif;?>

</body>
</html>
