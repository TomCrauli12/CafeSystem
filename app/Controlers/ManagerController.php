<?php

require_once __DIR__ . '/../Services/ManagerService.php';
require_once __DIR__ . '/../sessions/Sessions.php';
require_once __DIR__ . '/../Middleware/roleMiddleware.php';
require_once __DIR__ . '/../Middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../Validators/Validator.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../Services/HistoryService.php';

RoleMiddleware::requireAnyRole([4, 5]);

Csrf::requireValid();

date_default_timezone_set('Europe/Moscow');

$router = new Router();

$redirectTo = '/Cafe/app/Views/managers/tables.php';

$router->post('createTable', function() use (&$redirectTo){

        $number = Validator::integer($_POST['number'] ?? 0, 'Номер стола', 1, 999);

        $seats = Validator::integer($_POST['seats'] ?? 0, 'Количество мест', 1, 20);

        $tableId = ManagerService::createTable($number, $seats, date('Y-m-d H:i:s'));

        HistoryService::log('create_table', 'table', $tableId, 'Создан стол', ['number'=>$number, 'seats'=>$seats]);

        Router::redirect($redirectTo . '?created=1');
});

$router->post('updateTable', function() use (&$redirectTo){

        $tableId = Validator::integer($_POST['tableId'] ?? 0, 'Стол');

        $number = Validator::integer($_POST['number'] ?? 0, 'Номер стола', 1, 999);

        $seats = Validator::integer($_POST['seats'] ?? 0, 'Количество мест', 1, 20);

        ManagerService::updateTable($tableId, $number, $seats);

        HistoryService::log('update_table', 'table', $tableId, 'Изменён стол', ['number'=>$number, 'seats'=>$seats]);

        Router::redirect($redirectTo . '?updated=1');
});

$router->post('deleteTable', function() use (&$redirectTo){

        $tableId = Validator::integer($_POST['tableId'] ?? 0, 'Стол');

        ManagerService::deleteTable($tableId, date('Y-m-d H:i:s'));

        HistoryService::log('delete_table', 'table', $tableId, 'Стол отключён');

        Router::redirect($redirectTo . '?deleted=1');
});

$router->post('createReservation', function() use (&$redirectTo){

        $redirectTo = '/Cafe/app/Views/managers/reservations.php';

        $userId = Validator::integer($_POST['userId'] ?? 0, 'Клиент');

        $tableId = Validator::integer($_POST['tableId'] ?? 0, 'Стол');

        $date = $_POST['date'] ?? '';

        $time = $_POST['time'] ?? '';

        $durationMinutes = Validator::integer($_POST['durationMinutes'] ?? 120, 'Длительность', 30, 240);

        $guests = Validator::integer($_POST['guests'] ?? 1, 'Количество гостей', 1, 20);

        $reservationAt = Validator::dateTime($date, $time);

        $reservationId = ManagerService::createReservation($userId, $tableId, $reservationAt, $durationMinutes, $guests, date('Y-m-d H:i:s'));

        HistoryService::log('create_reservation', 'reservation', $reservationId, 'Менеджер создал бронирование', ['clientId'=>$userId, 'tableId'=>$tableId, 'reservationAt'=>$reservationAt, 'durationMinutes'=>$durationMinutes, 'guests'=>$guests, 'status'=>'new']);

        Router::redirect($redirectTo . '?reservationId=' . $reservationId);
});

$router->post('cancelReservation', function() use (&$redirectTo){

        $redirectTo = '/Cafe/app/Views/managers/reservations.php';

        $reservationId = Validator::integer($_POST['reservationId'] ?? 0, 'Бронь');

        if(!ManagerService::cancelReservation($reservationId, date('Y-m-d H:i:s'))){

            throw new RuntimeException('Бронь не найдена');
        }

        HistoryService::log('cancel_reservation', 'reservation', $reservationId, 'Менеджер отменил бронирование', ['status'=>'cancelled']);

        Router::redirect($redirectTo . '?cancelled=1');
});

$router->post('assignWaiter', function() use (&$redirectTo){

        $redirectTo = '/Cafe/app/Views/managers/orders.php';

        $orderId = Validator::integer($_POST['orderId'] ?? 0, 'Заказ');

        $waiterId = Validator::integer($_POST['waiterId'] ?? 0, 'Официант');

        if(!ManagerService::assignWaiter($orderId, $waiterId, date('Y-m-d H:i:s'))){

            throw new RuntimeException('Заказ не найден или официант уже назначен');
        }

        HistoryService::log('assign_waiter', 'order', $orderId, 'Менеджер назначил официанта', ['waiterId'=>$waiterId]);

        Router::redirect($redirectTo . '?assigned=1');
});

try{

    $router->dispatch('/Cafe/app/Views/managers/tables.php');

}catch(PDOException $exception){

    $_SESSION['manager_error'] = $exception->getCode()==='23000' ? 'Стол с таким номером уже существует' : 'Не удалось сохранить изменения';

    HistoryService::log('action_failed', 'manager', null, 'Ошибка действия менеджера', ['action'=>$router->getAction(), 'error'=>$_SESSION['manager_error']]);

    Router::redirect($redirectTo);

}catch(RuntimeException $exception){

    $_SESSION['manager_error'] = $exception->getMessage();

    HistoryService::log('action_failed', 'manager', null, 'Ошибка действия менеджера', ['action'=>$router->getAction(), 'error'=>$exception->getMessage()]);

    Router::redirect($redirectTo);

}catch(Throwable $exception){

    $_SESSION['manager_error'] = 'Не удалось выполнить действие';

    HistoryService::log('action_failed', 'manager', null, 'Ошибка действия менеджера', ['action'=>$router->getAction()]);

    Router::redirect($redirectTo);
}

Router::redirect('/Cafe/app/Views/managers/tables.php');

?>
