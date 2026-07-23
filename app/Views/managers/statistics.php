<?php

require_once __DIR__ . '/../../Middleware/roleMiddleware.php';

RoleMiddleware::requireAnyRole([4, 5]);

date_default_timezone_set('Europe/Moscow');

$from = date('Y-m-01');
$to = date('Y-m-d');

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Статистика</title>
</head>
<body>

<?php require_once '../../../include/header.php';?>

<h1>Статистика</h1>

<div id="ajax-message"></div>

<form id="statistics-filter">
    <label for="statistics-from">Дата от:</label>
    <input type="date" name="from" id="statistics-from" value="<?=$from?>" required>

    <label for="statistics-to">Дата до:</label>
    <input type="date" name="to" id="statistics-to" value="<?=$to?>" required>

    <button type="submit">Загрузить статистику</button>
</form>

<div id="statistics-result">
    <p>Статистика загружается...</p>
</div>

</body>
</html>
