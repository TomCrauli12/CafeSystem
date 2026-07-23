<?php

    require_once __DIR__ . '/../../Services/DishService.php';
    require_once __DIR__ . '/../../sessions/Sessions.php';
    require_once __DIR__ . '/../../Middleware/roleMiddleware.php';

    session::start();

    $dishes = Dish::getAvailableWithCategories();

    $basketError = $_SESSION['basket_error'] ?? null;
    unset($_SESSION['basket_error']);

    $menu = [];

    foreach($dishes as $dish){

        $menu[$dish['categoryName']][] = $dish;
    }

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Меню</title>
</head>
<body>

<?php require_once '../../../include/header.php';?>

<h1>Меню</h1>

<div id="ajax-message">
    <?php if($basketError):?>
        <p><?=htmlspecialchars($basketError, ENT_QUOTES, 'UTF-8')?></p>
    <?php endif;?>
</div>

<label for="dish-search">Поиск блюда:</label>
<input type="search" id="dish-search" minlength="2" maxlength="20" placeholder="Введите название">

<?php if(empty($menu)):?>
    <p>Блюд пока нет</p>
<?php else:?>

    <div id="dish-list">
        <?php foreach($menu as $categoryName=>$categoryDishes):?>

            <section class="dish-category">
                <h2><?=htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8')?></h2>

                <?php foreach($categoryDishes as $dish):?>

                    <div class="dish-card" data-dish-id="<?=(int)$dish['id']?>">
                        <img src="/Cafe/public/uploads/dish/<?=htmlspecialchars($dish['photo'], ENT_QUOTES, 'UTF-8')?>" alt="<?=htmlspecialchars($dish['name'], ENT_QUOTES, 'UTF-8')?>" width="250">

                        <h3><?=htmlspecialchars($dish['name'], ENT_QUOTES, 'UTF-8')?></h3>

                        <p><?=htmlspecialchars($dish['description'], ENT_QUOTES, 'UTF-8')?></p>

                        <p>Состав: <?=htmlspecialchars($dish['structure'], ENT_QUOTES, 'UTF-8')?></p>

                        <p>Время приготовления: <?=(int)$dish['cooktime']?> минут</p>

                        <?php if(RoleMiddleware::hasAnyRole([2, 4, 5])):?>
                            <a href="/Cafe/app/Views/cooks/editDish.php?id=<?=(int)$dish['id']?>">Редактировать</a>
                        <?php endif;?>

                        <form class="ajax-basket-add" action="/Cafe/app/Controlers/BasketController.php?action=add" method="post">
                            <?=Csrf::input()?>

                            <input type="hidden" name="dishId" value="<?=(int)$dish['id']?>">

                            <button type="submit">Добавить в корзину</button>
                        </form>
                    </div>

                <?php endforeach;?>
            </section>

        <?php endforeach;?>
    </div>

    <p id="dish-search-empty" hidden>Ничего не найдено</p>

<?php endif;?>

</body>
</html>
