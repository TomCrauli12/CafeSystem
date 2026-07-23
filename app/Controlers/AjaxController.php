<?php

require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/Ajax.php';
require_once __DIR__ . '/../Services/DishService.php';
require_once __DIR__ . '/../Services/OrderService.php';
require_once __DIR__ . '/../Services/TableService.php';
require_once __DIR__ . '/../Services/ManagerService.php';
require_once __DIR__ . '/../Middleware/userMeddleware.php';
require_once __DIR__ . '/../Middleware/roleMiddleware.php';
require_once __DIR__ . '/../Middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../Validators/Validator.php';

date_default_timezone_set('Europe/Moscow');

$router = new Router();

$router->get('searchDishes', function(){

    $search = Validator::text($_GET['search'] ?? '', 'Поиск', false);

    $dishes = $search==='' ? Dish::getAvailableWithCategories() : Dish::searchAvailable($search);

    Ajax::success(['dishIds'=>array_map('intval', array_column($dishes, 'id'))]);
});

$router->get('kitchenOrders', function(){

    RoleMiddleware::requireAnyRole([2, 4, 5]);

    $status = Validator::choice($_GET['status'] ?? 'all', 'Статус', ['all', 'new', 'cooking', 'ready']);

    $orders = OrderService::getForCook($status);

    ob_start();

    require __DIR__ . '/../Views/cooks/_orders.php';

    $html = ob_get_clean();

    Ajax::success(['html'=>$html, 'ordersCount'=>count($orders)]);
});

$router->get('availableTables', function(){

    AuthMiddleware::requireAuth();

    $date = is_string($_GET['date'] ?? null) ? trim($_GET['date']) : '';

    $time = is_string($_GET['time'] ?? null) ? trim($_GET['time']) : '';

    $durationMinutes = Validator::integer($_GET['durationMinutes'] ?? 120, 'Длительность', 30, 240);

    $guests = Validator::integer($_GET['guests'] ?? 1, 'Количество гостей', 1, 20);

    $reservationAt = Validator::dateTime($date, $time);

    $tables = TableService::getAvailableForReservation($reservationAt, $durationMinutes, $guests);

    Ajax::success(['tables'=>$tables]);
});

$router->get('statistics', function(){

    RoleMiddleware::requireAnyRole([4, 5]);

    $from = is_string($_GET['from'] ?? null) ? trim($_GET['from']) : '';

    $to = is_string($_GET['to'] ?? null) ? trim($_GET['to']) : '';

    $fromDate = DateTime::createFromFormat('!Y-m-d', $from);

    $toDate = DateTime::createFromFormat('!Y-m-d', $to);

    if(!$fromDate || !$toDate || $fromDate->format('Y-m-d')!==$from || $toDate->format('Y-m-d')!==$to || $fromDate>$toDate){

        throw new RuntimeException('Проверьте выбранный период');
    }

    $statistics = ManagerService::getStatistics($from . ' 00:00:00', $to . ' 23:59:59');

    foreach($statistics as $key=>$value){

        $statistics[$key] = (int)$value;
    }

    Ajax::success(['statistics'=>$statistics]);
});

try{

    $router->dispatch('', function(){

        Ajax::error('AJAX-действие не найдено', 404);
    });

}catch(RuntimeException $exception){

    Ajax::error($exception->getMessage());

}catch(Throwable $exception){

    Ajax::error('Не удалось загрузить данные', 500);
}

?>
