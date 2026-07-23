<?php

require_once __DIR__ . '/../Services/OrderService.php';
require_once __DIR__ . '/../Services/DishService.php';
require_once __DIR__ . '/../sessions/Sessions.php';
require_once __DIR__ . '/../Middleware/roleMiddleware.php';
require_once __DIR__ . '/../Middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../Validators/Validator.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/Ajax.php';
require_once __DIR__ . '/../Services/HistoryService.php';

RoleMiddleware::requireAnyRole([2, 4, 5]);

Csrf::requireValid();

$router = new Router();

$router->post('updateItemStatus', function(){

    $orderItemId = Validator::integer($_POST['orderItemId'] ?? 0, 'Блюдо заказа');

    $nextStatusCode = Validator::choice($_POST['nextStatusCode'] ?? '', 'Статус', ['cooking', 'ready']);

    $updated = OrderService::updateItemStatus($orderItemId, $nextStatusCode);

    if($updated){

        HistoryService::log('update_order_item_status', 'order_item', $orderItemId, 'Повар изменил статус блюда заказа', ['status'=>$nextStatusCode]);
    }

    if(Ajax::isRequest()){

        if(!$updated){

            Ajax::error('Статус блюда уже изменён');
        }

        Ajax::success(['orderItemId'=>$orderItemId, 'status'=>$nextStatusCode], 'Статус блюда изменён');
    }

    Router::redirect('/Cafe/app/Views/cooks/orders.php?' . ($updated ? 'updated=1' : 'error=1'));
});

$router->post('updateStopList', function(){

    $dishId = Validator::integer($_POST['dishId'] ?? 0, 'Блюдо');

    $isStopped = Validator::integer($_POST['isStopped'] ?? 0, 'Стоп-лист', 0, 1);

    $updated = Dish::updateStopList($dishId, $isStopped);

    if($updated){

        HistoryService::log('update_stop_list', 'dish', $dishId, 'Повар изменил стоп-лист', ['isStopped'=>$isStopped]);
    }

    if(Ajax::isRequest()){

        Ajax::success(['dishId'=>$dishId, 'isStopped'=>$isStopped], 'Стоп-лист обновлён');
    }

    Router::redirect('/Cafe/app/Views/cooks/orders.php?' . ($updated ? 'stopUpdated=1' : 'error=1'));
});

try{

    $router->dispatch('/Cafe/app/Views/cooks/orders.php');

}catch(RuntimeException $exception){

    $_SESSION['cook_error'] = $exception->getMessage();

    HistoryService::log('action_failed', 'cook', null, 'Ошибка действия повара', ['action'=>$router->getAction(), 'error'=>$exception->getMessage()]);

    if(Ajax::isRequest()){

        Ajax::error($exception->getMessage());
    }

    Router::redirect('/Cafe/app/Views/cooks/orders.php?error=1');
}

Router::redirect('/Cafe/app/Views/cooks/orders.php');

?>
