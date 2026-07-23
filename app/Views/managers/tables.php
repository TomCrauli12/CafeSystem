<?php

require_once __DIR__ . '/../../Services/ManagerService.php';
require_once __DIR__ . '/../../sessions/Sessions.php';
require_once __DIR__ . '/../../Middleware/roleMiddleware.php';

RoleMiddleware::requireAnyRole([4, 5]);

$tables = ManagerService::getTables();

$error = $_SESSION['manager_error'] ?? null;

unset($_SESSION['manager_error']);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление столами</title>
</head>
<body>

<?php require_once '../../../include/header.php';?>

<h1>Управление столами</h1>

<?php if($error):?>
    <p><?=htmlspecialchars($error, ENT_QUOTES, 'UTF-8')?></p>
<?php endif;?>

<?php if(isset($_GET['created'])):?>
    <p>Стол создан</p>
<?php endif;?>

<?php if(isset($_GET['updated'])):?>
    <p>Данные стола изменены</p>
<?php endif;?>

<?php if(isset($_GET['deleted'])):?>
    <p>Стол выведен из работы</p>
<?php endif;?>

<h2>Добавить стол</h2>

<form action="/Cafe/app/Controlers/ManagerController.php?action=createTable" method="post">
    <?=Csrf::input()?>
    <label for="number">Номер стола:</label>
    <input type="number" name="number" id="number" min="1" max="999" required>

    <label for="seats">Количество мест:</label>
    <input type="number" name="seats" id="seats" min="1" max="20" required>

    <button type="submit">Добавить стол</button>
</form>

<h2>Столы</h2>

<?php if(empty($tables)):?>
    <p>Столов пока нет</p>
<?php else:?>
    <?php foreach($tables as $table):?>
        <div>
            <form action="/Cafe/app/Controlers/ManagerController.php?action=updateTable" method="post">
                <?=Csrf::input()?>
                <input type="hidden" name="tableId" value="<?=(int)$table['id']?>">

                <label for="number-<?=(int)$table['id']?>">Номер:</label>
                <input type="number" name="number" id="number-<?=(int)$table['id']?>" min="1" max="999" value="<?=(int)$table['number']?>" required>

                <label for="seats-<?=(int)$table['id']?>">Количество мест:</label>
                <input type="number" name="seats" id="seats-<?=(int)$table['id']?>" min="1" max="20" value="<?=(int)$table['seats']?>" required>

                <button type="submit">Сохранить</button>
            </form>

            <?php if((int)$table['hasActiveOrder']===1):?>
                <p>На столе есть активный заказ</p>
            <?php endif;?>

            <?php if((int)$table['hasActiveReservation']===1):?>
                <p>На стол есть активная бронь</p>
            <?php endif;?>

            <form action="/Cafe/app/Controlers/ManagerController.php?action=deleteTable" method="post">
                <?=Csrf::input()?>
                <input type="hidden" name="tableId" value="<?=(int)$table['id']?>">
                <button type="submit">Убрать стол</button>
            </form>
        </div>

        <hr>
    <?php endforeach;?>
<?php endif;?>

</body>
</html>
