<?php

require_once __DIR__ . '/../../Services/CategoryService.php';
require_once __DIR__ . '/../../Middleware/roleMiddleware.php';

RoleMiddleware::requireAnyRole([2, 4, 5]);

$categories = Category::getAll();

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Категории</title>
</head>
<body>

<?php require_once '../../../include/header.php';?>

<h1>Категории</h1>

<a href="createCategory.php">Создать категорию</a>

<?php if(empty($categories)):?>
    <p>Категорий пока нет</p>
<?php else:?>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Дата создания</th>
                <th>Действия</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach($categories as $category):?>
                <tr>
                    <td><?=$category['id']?></td>

                    <td>
                        <?=htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8')?>
                    </td>

                    <td>
                        <?=htmlspecialchars($category['created'], ENT_QUOTES, 'UTF-8')?>
                    </td>

                    <td>
                        <a href="editCategory.php?id=<?=$category['id']?>">
                            Редактировать
                        </a>
                    </td>
                </tr>
            <?php endforeach;?>
        </tbody>
    </table>
<?php endif;?>

</body>
</html>
