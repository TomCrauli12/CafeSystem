<?php

require_once __DIR__ . '/../../Services/DishService.php';
require_once __DIR__ . '/../../Services/CategoryService.php';
require_once __DIR__ . '/../../Middleware/roleMiddleware.php';

RoleMiddleware::requireAnyRole([2, 4, 5]);

$cookError = $_SESSION['cook_error'] ?? null;

unset($_SESSION['cook_error']);

$id = (int)($_GET['id'] ?? 0);

$dish = Dish::getById($id);
$categories = Category::getAll();

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование блюда</title>
</head>
<body>
    <?php require_once '../../../include/header.php';?>

    <?php if($cookError):?>
        <p><?=htmlspecialchars($cookError, ENT_QUOTES, 'UTF-8')?></p>
    <?php endif;?>

    <form action="../../Controlers/DishController.php?action=updateDish" method="post" enctype="multipart/form-data">
        <?=Csrf::input()?>
        <input type="hidden" name="id" value="<?=$dish['id']?>">

        <label for="name">Название:</label>
        <input type="text" name="name" id="name" minlength="2" maxlength="20" value="<?=htmlspecialchars($dish['name'], ENT_QUOTES, 'UTF-8')?>" required>

        <p>Текущее изображение:</p>

        <img src="../../../public/uploads/dish/<?=htmlspecialchars($dish['photo'], ENT_QUOTES, 'UTF-8')?>" alt="" width="200">

        <label for="photo">Новое изображение:</label>
        <input type="file" name="photo" id="photo" accept=".jpg,.jpeg,.png,.webp">

        <label for="description">Описание:</label>
        <input type="text" name="description" id="description" minlength="2" maxlength="20" value="<?=htmlspecialchars($dish['description'], ENT_QUOTES, 'UTF-8')?>" required>

        <label for="structure">Состав:</label>
        <input type="text" name="structure" id="structure" minlength="2" maxlength="20" value="<?=htmlspecialchars($dish['structure'], ENT_QUOTES, 'UTF-8')?>" required>

        <label for="categoryId">Категория:</label>

        <select name="categoryId" id="categoryId">
            <?php foreach($categories as $category):?>
                <option value="<?=$category['id']?>" <?=(int)$category['id']===(int)$dish['categoryId'] ? 'selected' : ''?>><?=htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8')?></option>
            <?php endforeach;?>
        </select>

        <label for="cooktime">Время приготовления в минутах:</label>
        <input type="number" name="cooktime" id="cooktime" min="1" max="1440" value="<?=(int)$dish['cooktime']?>" required>

        <button type="submit">Сохранить</button>
    </form>
</body>
</html>
