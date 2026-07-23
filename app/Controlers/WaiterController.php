<?php

require_once __DIR__ . '/../Services/WaiterService.php';
require_once __DIR__ . '/../sessions/Sessions.php';
require_once __DIR__ . '/../Middleware/roleMiddleware.php';
require_once __DIR__ . '/../Middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../Validators/Validator.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../Services/HistoryService.php';

RoleMiddleware::requireAnyRole([3, 4, 5]);

Csrf::requireValid();

date_default_timezone_set('Europe/Moscow');

$router = new Router();

$waiterId = (int)session::get('user_id');

$tableId = 0;

$router->post('startService', function() use ($waiterId, &$tableId){

        $tableId = Validator::integer($_POST['tableId'] ?? 0, 'Стол');

        $created = date('Y-m-d H:i:s');

        $orderId = WaiterService::startService($tableId, $waiterId, $created);

        HistoryService::log('start_service', 'order', $orderId, 'Официант начал обслуживание стола', ['tableId'=>$tableId, 'waiterId'=>$waiterId]);

        Router::redirect('/Cafe/app/Views/waiters/tables.php?tableId=' . $tableId . '&started=1');
});

$router->post('addDish', function() use ($waiterId, &$tableId){

        $tableId = Validator::integer($_POST['tableId'] ?? 0, 'Стол');

        $orderId = Validator::integer($_POST['orderId'] ?? 0, 'Заказ');

        $dishId = Validator::integer($_POST['dishId'] ?? 0, 'Блюдо');

        $quantity = Validator::integer($_POST['quantity'] ?? 1, 'Количество', 1, 20);

        $comment = Validator::text($_POST['comment'] ?? '', 'Комментарий', false);

        $orderItemId = WaiterService::addDish($orderId, $waiterId, $dishId, $quantity, $comment);

        HistoryService::log('waiter_add_dish', 'order_item', $orderItemId, 'Официант добавил блюдо в заказ', ['orderId'=>$orderId, 'dishId'=>$dishId, 'quantity'=>$quantity, 'comment'=>$comment, 'status'=>'new']);

        Router::redirect('/Cafe/app/Views/waiters/tables.php?tableId=' . $tableId . '&dishAdded=1');
});

$router->post('completeOrder', function() use ($waiterId, &$tableId){

        $tableId = Validator::integer($_POST['tableId'] ?? 0, 'Стол');

        $orderId = Validator::integer($_POST['orderId'] ?? 0, 'Заказ');

        WaiterService::completeOrder($orderId, $waiterId);

        HistoryService::log('complete_order', 'order', $orderId, 'Официант завершил обслуживание', ['status'=>'completed']);

        Router::redirect('/Cafe/app/Views/waiters/tables.php?completed=1');
});

try{

    $router->dispatch('/Cafe/app/Views/waiters/tables.php');

}catch(RuntimeException $exception){

    $_SESSION['waiter_error'] = $exception->getMessage();

    HistoryService::log('action_failed', 'waiter', null, 'Ошибка действия официанта', ['action'=>$router->getAction(), 'error'=>$exception->getMessage()]);

    Router::redirect('/Cafe/app/Views/waiters/tables.php' . ($tableId>0 ? '?tableId=' . $tableId : ''));

}catch(Throwable $exception){

    $_SESSION['waiter_error'] = 'Не удалось выполнить действие';

    HistoryService::log('action_failed', 'waiter', null, 'Ошибка действия официанта', ['action'=>$router->getAction()]);

    Router::redirect('/Cafe/app/Views/waiters/tables.php' . ($tableId>0 ? '?tableId=' . $tableId : ''));
}

Router::redirect('/Cafe/app/Views/waiters/tables.php');

?>
