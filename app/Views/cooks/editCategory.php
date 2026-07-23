<?php

require_once __DIR__ . '/../../Services/CategoryService.php';
require_once __DIR__ . '/../../Middleware/roleMiddleware.php';

RoleMiddleware::requireAnyRole([2, 4, 5]);

$cookError = $_SESSION['cook_error'] ?? null;

unset($_SESSION['cook_error']);

$id = (int)($_GET['id'] ?? 0);
$category = Category::getById($id);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование категории</title>
</head>
<body>
    <?php require_once '../../../include/header.php';?>

    <?php if($cookError):?>
        <p><?=htmlspecialchars($cookError, ENT_QUOTES, 'UTF-8')?></p>
    <?php endif;?>

    <form action="../../Controlers/CategoryController.php?action=updateCategory" method="post">
        <?=Csrf::input()?>
        <input type="hidden" name="id" value="<?=$category['id']?>">

        <label for="name">Название категории:</label>
        <input type="text" name="name" id="name" minlength="2" maxlength="20" value="<?=htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8')?>" required>

        <button type="submit">Сохранить</button>
    </form>
</body>
</html>
