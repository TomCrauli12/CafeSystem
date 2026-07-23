<?php

require_once __DIR__ . '/../../Services/HistoryService.php';
require_once __DIR__ . '/../../Middleware/roleMiddleware.php';

RoleMiddleware::requireRole(5);

$history = HistoryService::getAll();

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>История действий</title>
</head>
<body>

<?php require_once '../../../include/header.php';?>

<h1>История действий</h1>

<?php if(empty($history)):?>
    <p>История пока пустая</p>
<?php else:?>
    <?php foreach($history as $historyItem):?>
        <div>
            <p>ID:<?=(int)$historyItem['id']?></p>
            <p>Дата:<?=htmlspecialchars($historyItem['created'], ENT_QUOTES, 'UTF-8')?></p>
            <p>Пользователь:<?=htmlspecialchars($historyItem['userName'] ?? 'Гость или удалённый пользователь', ENT_QUOTES, 'UTF-8')?></p>
            <p>Логин:<?=htmlspecialchars($historyItem['login'] ?? '-', ENT_QUOTES, 'UTF-8')?></p>
            <p>Роль:<?=htmlspecialchars($historyItem['roleName'] ?? ('ID ' . ($historyItem['roleId'] ?? '-')), ENT_QUOTES, 'UTF-8')?></p>
            <p>Действие:<?=htmlspecialchars($historyItem['action'], ENT_QUOTES, 'UTF-8')?></p>
            <p>Объект:<?=htmlspecialchars($historyItem['entityType'], ENT_QUOTES, 'UTF-8')?> №<?=htmlspecialchars((string)($historyItem['entityId'] ?? '-'), ENT_QUOTES, 'UTF-8')?></p>
            <p>Описание:<?=htmlspecialchars($historyItem['description'], ENT_QUOTES, 'UTF-8')?></p>

            <?php if($historyItem['details']):?>
                <p>Детали:<?=htmlspecialchars($historyItem['details'], ENT_QUOTES, 'UTF-8')?></p>
            <?php endif;?>

            <p>IP:<?=htmlspecialchars($historyItem['ipAddress'] ?? '-', ENT_QUOTES, 'UTF-8')?></p>
        </div>

        <hr>
    <?php endforeach;?>
<?php endif;?>

</body>
</html>
