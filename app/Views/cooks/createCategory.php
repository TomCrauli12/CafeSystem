<?php

require_once __DIR__ . '/../../Middleware/roleMiddleware.php';

RoleMiddleware::requireAnyRole([2, 4, 5]);

$cookError = $_SESSION['cook_error'] ?? null;

unset($_SESSION['cook_error']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php require_once '../../../include/header.php'; ?>
    <?php if($cookError):?>
        <p><?=htmlspecialchars($cookError, ENT_QUOTES, 'UTF-8')?></p>
    <?php endif;?>

    <form action="../../Controlers/CategoryController.php?action=createCategory" method="post">
        <?=Csrf::input()?>
        <label for="name">Название категории:</label>
        <input type="text" name="name" id="name" minlength="2" maxlength="20" required>

        <button>Создать категорию</button>
    </form>
</body>
</html>
