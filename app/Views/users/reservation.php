<?php

require_once __DIR__ . '/../../Services/TableService.php';
require_once __DIR__ . '/../../Services/ReservationService.php';
require_once __DIR__ . '/../../sessions/Sessions.php';
require_once __DIR__ . '/../../Middleware/userMeddleware.php';
require_once __DIR__ . '/../../Validators/Validator.php';

AuthMiddleware::requireAuth();

date_default_timezone_set('Europe/Moscow');

$defaultDateTime = new DateTime('+1 hour');

$date = is_string($_GET['date'] ?? null) ? trim($_GET['date']) : $defaultDateTime->format('Y-m-d');

$time = is_string($_GET['time'] ?? null) ? trim($_GET['time']) : $defaultDateTime->format('H:00');

$durationMinutes = $_GET['durationMinutes'] ?? 120;

$guests = $_GET['guests'] ?? 2;

$availableTables = [];

$filterError = null;

try{

    $durationMinutes = Validator::integer($durationMinutes, 'Длительность', 30, 240);

    $guests = Validator::integer($guests, 'Количество гостей', 1, 20);

    $reservationAt = Validator::dateTime($date, $time);

    $availableTables = TableService::getAvailableForReservation($reservationAt, $durationMinutes, $guests);

}catch(RuntimeException $exception){

    $filterError = $exception->getMessage();

    $durationMinutes = 120;

    $guests = 2;
}

$currentDateTime = date('Y-m-d H:i:s');

$reservations = ReservationService::getByUserId((int)session::get('user_id'), $currentDateTime);

$error = $_SESSION['reservation_error'] ?? $filterError;

unset($_SESSION['reservation_error']);


?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Бронирование столика</title>
</head>
<body>

<?php require_once '../../../include/header.php';?>

<h1>Бронирование столика</h1>

<div id="ajax-message"></div>

<?php if($error):?>
    <p><?=htmlspecialchars($error, ENT_QUOTES, 'UTF-8')?></p>
<?php endif;?>

<?php if(isset($_GET['reservationId'])):?>
    <p>Бронь №<?=(int)$_GET['reservationId']?> успешно создана</p>
<?php endif;?>

<?php if(isset($_GET['cancelled'])):?>
    <p>Бронирование отменено</p>
<?php endif;?>

<form id="available-tables-filter" action="" method="get">
    <label for="date">Дата:</label>
    <input type="date" name="date" id="date" min="<?=date('Y-m-d')?>" value="<?=htmlspecialchars($date, ENT_QUOTES, 'UTF-8')?>" required>

    <label for="time">Время:</label>
    <input type="time" name="time" id="time" value="<?=htmlspecialchars($time, ENT_QUOTES, 'UTF-8')?>" required>

    <label for="durationMinutes">Длительность:</label>
    <select name="durationMinutes" id="durationMinutes">
        <option value="60" <?=$durationMinutes===60 ? 'selected' : ''?>>1 час</option>
        <option value="90" <?=$durationMinutes===90 ? 'selected' : ''?>>1 час 30 минут</option>
        <option value="120" <?=$durationMinutes===120 ? 'selected' : ''?>>2 часа</option>
        <option value="180" <?=$durationMinutes===180 ? 'selected' : ''?>>3 часа</option>
        <option value="240" <?=$durationMinutes===240 ? 'selected' : ''?>>4 часа</option>
    </select>

    <label for="guests">Количество гостей:</label>
    <input type="number" name="guests" id="guests" min="1" max="20" value="<?=$guests?>" required>

    <button type="submit">Найти свободные столы</button>
</form>

<h2>Свободные столы</h2>

<div id="available-tables">

<?php if(empty($availableTables)):?>
    <p>На выбранное время подходящих столов нет</p>
<?php else:?>
    <?php foreach($availableTables as $table):?>
        <div>
            <h3>Стол №<?=(int)$table['number']?></h3>
            <p>Количество мест:<?=(int)$table['seats']?></p>

            <form action="/Cafe/app/Controlers/ReservationController.php?action=create" method="post">
                <?=Csrf::input()?>
                <input type="hidden" name="tableId" value="<?=(int)$table['id']?>">
                <input type="hidden" name="date" value="<?=htmlspecialchars($date, ENT_QUOTES, 'UTF-8')?>">
                <input type="hidden" name="time" value="<?=htmlspecialchars($time, ENT_QUOTES, 'UTF-8')?>">
                <input type="hidden" name="durationMinutes" value="<?=$durationMinutes?>">
                <input type="hidden" name="guests" value="<?=$guests?>">
                <button type="submit">Забронировать</button>
            </form>
        </div>

        <hr>
    <?php endforeach;?>
<?php endif;?>

</div>

<h2>Мои бронирования</h2>

<?php if(empty($reservations)):?>
    <p>У вас пока нет бронирований</p>
<?php else:?>
    <?php foreach($reservations as $reservation):?>
        <div>
            <h3>Бронь №<?=(int)$reservation['id']?></h3>
            <p>Стол:№<?=(int)$reservation['tableNumber']?></p>
            <p>Количество мест:<?=(int)$reservation['seats']?></p>
            <p>Количество гостей:<?=(int)$reservation['guests']?></p>
            <p>Дата и время:<?=date('d.m.Y H:i', strtotime($reservation['reservationAt']))?></p>
            <p>Длительность:<?=(int)$reservation['durationMinutes']?> минут</p>
            <p>Статус:<?=htmlspecialchars($reservation['statusName'], ENT_QUOTES, 'UTF-8')?></p>

            <?php if((int)$reservation['canCancel']===1):?>
                <form action="/Cafe/app/Controlers/ReservationController.php?action=cancel" method="post">
                    <?=Csrf::input()?>
                    <input type="hidden" name="reservationId" value="<?=(int)$reservation['id']?>">
                    <button type="submit">Отменить бронь</button>
                </form>
            <?php endif;?>
        </div>

        <hr>
    <?php endforeach;?>
<?php endif;?>

</body>
</html>
