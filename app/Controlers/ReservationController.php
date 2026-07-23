<?php

require_once __DIR__ . '/../Services/ReservationService.php';
require_once __DIR__ . '/../sessions/Sessions.php';
require_once __DIR__ . '/../Middleware/userMeddleware.php';
require_once __DIR__ . '/../Middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../Validators/Validator.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../Services/HistoryService.php';

AuthMiddleware::requireAuth();

Csrf::requireValid();

date_default_timezone_set('Europe/Moscow');

$router = new Router();

$userId = (int)session::get('user_id');

$currentDateTime = date('Y-m-d H:i:s');

$router->post('create', function() use ($userId, $currentDateTime){

    try{

    $tableId = Validator::integer($_POST['tableId'] ?? 0, 'Стол');

    $date = $_POST['date'] ?? '';

    $time = $_POST['time'] ?? '';

    $durationMinutes = Validator::integer($_POST['durationMinutes'] ?? 120, 'Длительность', 30, 240);

    $guests = Validator::integer($_POST['guests'] ?? 1, 'Количество гостей', 1, 20);

    $reservationAt = Validator::dateTime($date, $time);

        $reservationId = ReservationService::create($userId, $tableId, $reservationAt, $durationMinutes, $guests, $currentDateTime);

        HistoryService::log('create_reservation', 'reservation', $reservationId, 'Клиент создал бронирование', ['tableId'=>$tableId, 'reservationAt'=>$reservationAt, 'durationMinutes'=>$durationMinutes, 'guests'=>$guests, 'status'=>'new']);

        Router::redirect('/Cafe/app/Views/users/reservation.php?reservationId=' . $reservationId);

    }catch(RuntimeException $exception){

        HistoryService::log('action_failed', 'reservation', null, 'Не удалось создать бронирование', ['action'=>'create', 'error'=>$exception->getMessage()]);

        $_SESSION['reservation_error'] = $exception->getMessage();

        Router::redirect('/Cafe/app/Views/users/reservation.php');

    }catch(Throwable $exception){

        HistoryService::log('action_failed', 'reservation', null, 'Не удалось создать бронирование', ['action'=>'create']);

        $_SESSION['reservation_error'] = 'Не удалось создать бронь';

        Router::redirect('/Cafe/app/Views/users/reservation.php');
    }
});

$router->post('cancel', function() use ($userId, $currentDateTime){

    try{

    $reservationId = Validator::integer($_POST['reservationId'] ?? 0, 'Бронь');

    ReservationService::cancel($reservationId, $userId, $currentDateTime);

    HistoryService::log('cancel_reservation', 'reservation', $reservationId, 'Клиент отменил бронирование', ['status'=>'cancelled']);

    Router::redirect('/Cafe/app/Views/users/reservation.php?cancelled=1');

    }catch(RuntimeException $exception){

        HistoryService::log('action_failed', 'reservation', $reservationId ?? null, 'Не удалось отменить бронирование', ['action'=>'cancel', 'error'=>$exception->getMessage()]);

        $_SESSION['reservation_error'] = $exception->getMessage();

        Router::redirect('/Cafe/app/Views/users/reservation.php');
    }
});

$router->dispatch('/Cafe/app/Views/users/reservation.php');

?>
