<?php

    require_once __DIR__ . '/../../Repositories/DishRepository.php';
    require_once __DIR__ . '/../../Middleware/roleMiddleware.php';
    require_once __DIR__ . '/../../Middleware/CsrfMiddleware.php';

    RoleMiddleware::requireAnyRole([2, 4, 5]);

    $cookError = $_SESSION['cook_error'] ?? null;

    unset($_SESSION['cook_error']);

    $CategoryRespositiry = new CategoryRespositiry();
    $categories = $CategoryRespositiry->getAll();

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php if($cookError):?>
        <p><?=htmlspecialchars($cookError, ENT_QUOTES, 'UTF-8')?></p>
    <?php endif;?>

    <form action="../../Controlers/DishController.php?action=createDish" method="post" enctype="multipart/form-data">
        <?=Csrf::input()?>

        <label for="name">Название:</label>
        <input type="text" name="name" id="name" minlength="2" maxlength="20" required>

        <label for="photo">Изображение:</label>
        <input type="file" name="photo" id="photo" accept=".jpg,.jpeg,.png,.webp" required>

        <label for="description">Описание:</label>
        <input type="text" name="description" id="description" minlength="2" maxlength="20" required>

        <label for="structure">Состав:</label>
        <input type="text" name="structure" id="structure" minlength="2" maxlength="20" required>

        <label for="categoryId">Категория:</label>
        <select name="categoryId" id="">
                <?php foreach($categories as $category):?>
                    <option value="<?=$category['id']?>"><?=htmlspecialchars($category['name'])?></option>
                <?php endforeach;?>
        </select>

        <label for="cooktime">Время приготовления в минутах:</label>
        <input type="number" name="cooktime" id="cooktime" min="1" max="1440" required>

        <button>Создать блюдо</button>
    </form>
</body>
</html>
