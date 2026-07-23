<?php

require_once __DIR__ . '/../Services/BasketService.php';
require_once __DIR__ . '/../Services/OrderService.php';
require_once __DIR__ . '/../sessions/Sessions.php';
require_once __DIR__ . '/../Services/DishService.php';
require_once __DIR__ . '/../Middleware/userMeddleware.php';
require_once __DIR__ . '/../Middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../Validators/Validator.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/Ajax.php';
require_once __DIR__ . '/../Services/HistoryService.php';

AuthMiddleware::requireAuth();

Csrf::requireValid();

$router = new Router();

$action = $router->getAction();

$router->post('add', function(){

    $dishId = Validator::integer($_POST['dishId'] ?? 0, 'Блюдо');

    if(!Dish::isAvailable($dishId)){

        $_SESSION['basket_error'] = 'Это блюдо сейчас находится в стоп-листе';

        if(Ajax::isRequest()){

            Ajax::error($_SESSION['basket_error']);
        }

        Router::redirect('/Cafe/app/Views/users/menu.php');
    }

    BasketService::add($dishId);

    HistoryService::log('basket_add', 'dish', $dishId, 'Клиент добавил блюдо в корзину');

    if(Ajax::isRequest()){

        Ajax::success(['basketCount'=>BasketService::getCount()], 'Блюдо добавлено в корзину');
    }

    Router::redirect('/Cafe/app/Views/users/menu.php');
});

$router->post('update', function(){

    $dishId = Validator::integer($_POST['dishId'] ?? 0, 'Блюдо');

    $quantity = Validator::integer($_POST['quantity'] ?? 1, 'Количество', 1, 20);

    $comment = Validator::text($_POST['comment'] ?? '', 'Комментарий', false);

    BasketService::update($dishId, $quantity, $comment);

    HistoryService::log('basket_update', 'dish', $dishId, 'Клиент изменил блюдо в корзине', ['quantity'=>$quantity, 'comment'=>$comment]);

    if(Ajax::isRequest()){

        Ajax::success(['dishId'=>$dishId, 'quantity'=>$quantity, 'basketCount'=>BasketService::getCount()], 'Количество изменено');
    }

    Router::redirect('/Cafe/app/Views/users/basket.php');
});

$router->post('remove', function(){

    $dishId = Validator::integer($_POST['dishId'] ?? 0, 'Блюдо');

    BasketService::remove($dishId);

    HistoryService::log('basket_remove', 'dish', $dishId, 'Клиент удалил блюдо из корзины');

    if(Ajax::isRequest()){

        Ajax::success(['dishId'=>$dishId, 'basketCount'=>BasketService::getCount()], 'Блюдо удалено из корзины');
    }

    Router::redirect('/Cafe/app/Views/users/basket.php');
});

$router->post('clear', function(){

    BasketService::clear();

    HistoryService::log('basket_clear', 'basket', null, 'Клиент очистил корзину');

    if(Ajax::isRequest()){

        Ajax::success(['basketCount'=>0], 'Корзина очищена');
    }

    Router::redirect('/Cafe/app/Views/users/basket.php');
});

$router->post('createOrder', function(){

    $basket = BasketService::getAll();

    $tableId = Validator::integer($_POST['tableId'] ?? 0, 'Стол');

    if(empty($basket)){

        if(Ajax::isRequest()){

            Ajax::error('Корзина пустая');
        }

        Router::redirect('/Cafe/app/Views/users/basket.php');
    }

    date_default_timezone_set('Europe/Moscow');

    $created = date('Y-m-d H:i:s');

    try{

        $orderId = OrderService::create((int)session::get('user_id'), $basket, $tableId, $created);

        HistoryService::log('create_order', 'order', $orderId, 'Клиент создал заказ', ['tableId'=>$tableId, 'itemsCount'=>count($basket), 'status'=>'new']);

        BasketService::clear();

        if(Ajax::isRequest()){

            Ajax::success(['orderId'=>$orderId, 'basketCount'=>0], 'Заказ успешно создан');
        }

        Router::redirect('/Cafe/app/Views/users/basket.php?orderId=' . $orderId);

    }catch(RuntimeException $exception){

        HistoryService::log('action_failed', 'order', null, 'Не удалось создать заказ', ['action'=>'createOrder', 'error'=>$exception->getMessage()]);

        $_SESSION['order_error'] = $exception->getMessage();

        if(Ajax::isRequest()){

            Ajax::error($exception->getMessage());
        }

        Router::redirect('/Cafe/app/Views/users/basket.php');

    }catch(Throwable $exception){

        HistoryService::log('action_failed', 'order', null, 'Не удалось создать заказ', ['action'=>'createOrder']);

        $_SESSION['order_error'] = 'Не удалось оформить заказ';

        if(Ajax::isRequest()){

            Ajax::error($_SESSION['order_error'], 500);
        }

        Router::redirect('/Cafe/app/Views/users/basket.php');
    }
});

$router->post('cancelOrderItem', function(){

    $orderItemId = Validator::integer($_POST['orderItemId'] ?? 0, 'Блюдо заказа');

    $userId = (int)session::get('user_id');

    if(!OrderService::cancelItem($orderItemId, $userId)){

        throw new RuntimeException('Блюдо уже нельзя отменить');
    }

    HistoryService::log('cancel_order_item', 'order_item', $orderItemId, 'Клиент отменил блюдо заказа', ['status'=>'cancelled']);

    if(Ajax::isRequest()){

        Ajax::success(['orderItemId'=>$orderItemId], 'Блюдо отменено');
    }

    Router::redirect('/Cafe/app/Views/users/basket.php');
});

try{

    $router->dispatch('/Cafe/app/Views/users/menu.php');

}catch(RuntimeException $exception){

    HistoryService::log('action_failed', 'basket', null, 'Ошибка действия с корзиной или заказом', ['action'=>$action, 'error'=>$exception->getMessage()]);

    if(Ajax::isRequest()){

        Ajax::error($exception->getMessage());
    }

    if($action==='add'){

        $_SESSION['basket_error'] = $exception->getMessage();

        Router::redirect('/Cafe/app/Views/users/menu.php');
    }

    $_SESSION['order_error'] = $exception->getMessage();

    Router::redirect('/Cafe/app/Views/users/basket.php');
}

Router::redirect('/Cafe/app/Views/users/menu.php');



?>
