<?php

require_once __DIR__ . '/../../Services/ManagerService.php';
require_once __DIR__ . '/../../Services/TableService.php';
require_once __DIR__ . '/../../sessions/Sessions.php';
require_once __DIR__ . '/../../Middleware/roleMiddleware.php';
require_once __DIR__ . '/../../Validators/Validator.php';

RoleMiddleware::requireAnyRole([4, 5]);

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

$clients = ManagerService::getClients();

$reservations = ManagerService::getReservations();

$error = $_SESSION['manager_error'] ?? $filterError;

unset($_SESSION['manager_error']);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Бронирования</title>
</head>
<body>

<?php require_once '../../../include/header.php';?>

<h1>Бронирования</h1>

<div id="ajax-message"></div>

<?php if($error):?>
    <p><?=htmlspecialchars($error, ENT_QUOTES, 'UTF-8')?></p>
<?php endif;?>

<?php if(isset($_GET['reservationId'])):?>
    <p>Бронь №<?=(int)$_GET['reservationId']?> создана</p>
<?php endif;?>

<?php if(isset($_GET['cancelled'])):?>
    <p>Бронь отменена</p>
<?php endif;?>

<h2>Найти свободный стол</h2>

<form id="manager-tables-filter" action="" method="get">
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

    <button type="submit">Найти столы</button>
</form>

<h2>Создать бронь</h2>

<?php if(empty($clients)):?>
    <p>Нет зарегистрированных клиентов</p>
<?php else:?>
    <form id="manager-reservation-form" action="/Cafe/app/Controlers/ManagerController.php?action=createReservation" method="post">
        <?=Csrf::input()?>
        <input type="hidden" name="date" data-reservation-value="date" value="<?=htmlspecialchars($date, ENT_QUOTES, 'UTF-8')?>">
        <input type="hidden" name="time" data-reservation-value="time" value="<?=htmlspecialchars($time, ENT_QUOTES, 'UTF-8')?>">
        <input type="hidden" name="durationMinutes" data-reservation-value="durationMinutes" value="<?=$durationMinutes?>">
        <input type="hidden" name="guests" data-reservation-value="guests" value="<?=$guests?>">

        <label for="userId">Клиент:</label>
        <select name="userId" id="userId" required>
            <option value="">Выберите клиента</option>
            <?php foreach($clients as $client):?>
                <option value="<?=(int)$client['id']?>"><?=htmlspecialchars($client['name'] . ' — ' . $client['phone'], ENT_QUOTES, 'UTF-8')?></option>
            <?php endforeach;?>
        </select>

        <label for="tableId">Стол:</label>
        <select name="tableId" id="tableId" data-available-table-select required>
            <option value="">Выберите стол</option>
            <?php foreach($availableTables as $table):?>
                <option value="<?=(int)$table['id']?>">Стол №<?=(int)$table['number']?> — <?=(int)$table['seats']?> мест</option>
            <?php endforeach;?>
        </select>

        <p id="manager-tables-empty" <?=empty($availableTables) ? '' : 'hidden'?>>На выбранное время подходящих столов нет</p>

        <button type="submit" data-reservation-submit <?=empty($availableTables) ? 'disabled' : ''?>>Забронировать</button>
    </form>
<?php endif;?>

<h2>Активные брони</h2>

<?php if(empty($reservations)):?>
    <p>Активных броней нет</p>
<?php else:?>
    <?php foreach($reservations as $reservation):?>
        <div>
            <h3>Бронь №<?=(int)$reservation['id']?></h3>
            <p>Клиент:<?=htmlspecialchars($reservation['userName'], ENT_QUOTES, 'UTF-8')?></p>
            <p>Телефон:<?=htmlspecialchars($reservation['phone'], ENT_QUOTES, 'UTF-8')?></p>
            <p>Стол:№<?=(int)$reservation['tableNumber']?></p>
            <p>Гостей:<?=(int)$reservation['guests']?></p>
            <p>Дата и время:<?=date('d.m.Y H:i', strtotime($reservation['reservationAt']))?></p>
            <p>Длительность:<?=(int)$reservation['durationMinutes']?> минут</p>
            <p>Статус:<?=htmlspecialchars($reservation['statusName'], ENT_QUOTES, 'UTF-8')?></p>

            <form action="/Cafe/app/Controlers/ManagerController.php?action=cancelReservation" method="post">
                <?=Csrf::input()?>
                <input type="hidden" name="reservationId" value="<?=(int)$reservation['id']?>">
                <button type="submit">Отменить бронь</button>
            </form>
        </div>

        <hr>
    <?php endforeach;?>
<?php endif;?>

</body>
</html>
