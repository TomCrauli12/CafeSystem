<?php
    require_once __DIR__ . '/../../Services/TableService.php';
    require_once __DIR__ . '/../../sessions/Sessions.php';
    require_once __DIR__ . '/../../Services/BasketService.php';
    require_once __DIR__ . '/../../Services/OrderService.php';
    require_once __DIR__ . '/../../Repositories/DishRepository.php';
    require_once __DIR__ . '/../../Middleware/userMeddleware.php';

    AuthMiddleware::requireAuth();

    $basket = BasketService::getAll();

    $dishRepository = new DishRespositiry();

    $dishIds = array_keys($basket);

    $dishes = $dishRepository->getByIds($dishIds);

    $userId = (int)session::get('user_id');

    $orderItems = OrderService::getByUserId($userId);

    $orders = [];

    foreach($orderItems as $orderItem){

        $orderId = (int)$orderItem['orderId'];

        if(!isset($orders[$orderId])){

            $orders[$orderId] = ['created'=>$orderItem['orderCreated'], 'tableNumber'=>$orderItem['tableNumber'], 'items'=>[]];
        }

        $orders[$orderId]['items'][] = $orderItem;
    }


    date_default_timezone_set('Europe/Moscow');

    $currentDateTime = date('Y-m-d H:i:s');

    $availableTables = TableService::getAvailableForOrder($userId, $currentDateTime);

    $orderError = $_SESSION['order_error'] ?? null;

    unset($_SESSION['order_error']);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина</title>
</head>
<body>

<?php require_once '../../../include/header.php';?>

<h1>Корзина</h1>

<div id="ajax-message"></div>

<div id="basket-content">

<?php if(isset($_GET['orderId'])):?>
    <p>Заказ №<?=(int)$_GET['orderId']?> успешно создан</p>
<?php endif;?>

<?php if(empty($dishes)):?>

    <p>Корзина пустая</p>

    <a href="menu.php">Перейти в меню</a>

<?php else:?>

    <?php foreach($dishes as $dish):?>

        <?php
            $dishId = (int)$dish['id'];

            $item = $basket[$dishId];
        ?>

        <div class="basket-item" data-dish-id="<?=$dishId?>">
            <img src="/Cafe/public/uploads/dish/<?=htmlspecialchars($dish['photo'], ENT_QUOTES, 'UTF-8')?>" alt="<?=htmlspecialchars($dish['name'], ENT_QUOTES, 'UTF-8')?>" width="200">

            <h2><?=htmlspecialchars($dish['name'], ENT_QUOTES, 'UTF-8')?></h2>

            <p><?=htmlspecialchars($dish['description'], ENT_QUOTES, 'UTF-8')?></p>

            <p>Состав:<?=htmlspecialchars($dish['structure'], ENT_QUOTES, 'UTF-8')?></p>

            <form class="ajax-basket-update" action="/Cafe/app/Controlers/BasketController.php?action=update" method="post">
                <?=Csrf::input()?>
                <input type="hidden" name="dishId" value="<?=$dishId?>">

                <label for="quantity-<?=$dishId?>">Количество:</label>

                <input type="number" name="quantity" id="quantity-<?=$dishId?>" min="1" max="20" value="<?=(int)$item['quantity']?>" required>

                <label for="comment-<?=$dishId?>">Комментарий к блюду:</label>

                <textarea name="comment" id="comment-<?=$dishId?>" minlength="2" maxlength="20" placeholder="Например: без лука"><?=htmlspecialchars($item['comment'], ENT_QUOTES, 'UTF-8')?></textarea>

                <button type="submit">Сохранить изменения</button>
            </form>

            <form class="ajax-basket-remove" action="/Cafe/app/Controlers/BasketController.php?action=remove" method="post">
                <?=Csrf::input()?>

                <input type="hidden" name="dishId" value="<?=$dishId?>">

                <button type="submit">Удалить из корзины</button>
            </form>
        </div>

        <hr>

    <?php endforeach;?>

    <?php if($orderError):?>

        <p><?=htmlspecialchars($orderError, ENT_QUOTES, 'UTF-8')?></p>

    <?php endif;?>

    <?php if(empty($availableTables)):?>

        <p>Сейчас нет свободных столов. Вы можете забронировать стол на другое время.</p>

        <a href="/Cafe/app/Views/users/reservation.php">Забронировать стол</a>

    <?php else:?>

        <form class="ajax-create-order" action="/Cafe/app/Controlers/BasketController.php?action=createOrder" method="post">
            <?=Csrf::input()?>

            <label for="tableId">Выберите стол:</label>

            <select name="tableId" id="tableId" required>
            <option value="">Выберите стол</option>

                <?php foreach($availableTables as $table):?>

                    <option value="<?=(int)$table['id']?>">Стол №<?=(int)$table['number']?> — <?=(int)$table['seats']?> мест<?=$table['hasOwnReservation'] ? ' — ваша бронь' : ''?></option>
                    
                <?php endforeach;?>
            </select>

            <button type="submit">Оформить заказ</button>
        </form>
    <?php endif;?>

    <form class="ajax-basket-clear" action="/Cafe/app/Controlers/BasketController.php?action=clear" method="post">
        <?=Csrf::input()?>

        <button type="submit">Очистить корзину</button>

    </form>

<?php endif;?>

</div>


<h1>Мои заказы</h1>

<?php if(empty($orders)):?>
    <p>У вас пока нет оформленных заказов</p>
<?php else:?>
    <?php foreach($orders as $orderId=>$order):?>
        <section>
            <h2>Заказ №<?=$orderId?></h2>
            <p>Создан:<?=htmlspecialchars($order['created'], ENT_QUOTES, 'UTF-8')?></p>
            <p>Стол:<?=$order['tableNumber'] ? '№' . (int)$order['tableNumber'] : 'Не выбран'?></p>

            <?php foreach($order['items'] as $item):?>
                <div>
                    <img src="/Cafe/public/uploads/dish/<?=htmlspecialchars($item['photo'], ENT_QUOTES, 'UTF-8')?>" alt="<?=htmlspecialchars($item['dishName'], ENT_QUOTES, 'UTF-8')?>" width="150">

                    <h3><?=htmlspecialchars($item['dishName'], ENT_QUOTES, 'UTF-8')?></h3>
                    <p>Количество:<?=(int)$item['quantity']?></p>
                    <p>Комментарий:<?=htmlspecialchars($item['comment'] ?: 'Нет', ENT_QUOTES, 'UTF-8')?></p>
                    <p>Время приготовления:<?=(int)$item['cooktime']?> минут</p>
                    <p>Статус:<?=htmlspecialchars($item['statusName'], ENT_QUOTES, 'UTF-8')?></p>

                    <?php if((int)$item['canCancel']===1):?>
                        <form class="ajax-cancel-order-item" action="/Cafe/app/Controlers/BasketController.php?action=cancelOrderItem" method="post">
                            <?=Csrf::input()?>
                            <input type="hidden" name="orderItemId" value="<?=(int)$item['orderItemId']?>">
                            <button type="submit">Отменить блюдо</button>
                        </form>
                    <?php endif;?>
                </div>

                <hr>
            <?php endforeach;?>
        </section>
    <?php endforeach;?>
<?php endif;?>

</body>
</html>
