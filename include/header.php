<?php
    require_once __DIR__ . "/../app/sessions/Sessions.php";
    require_once __DIR__ . "/../app/Middleware/roleMiddleware.php";
    require_once __DIR__ . "/../app/Middleware/CsrfMiddleware.php";
    session::start();

?>
<link rel="stylesheet" href="">
<script src="/Cafe/public/js/ajax.js" defer></script>
<header>
    <div class="loho">
        <h1><a href="<?=htmlspecialchars(RoleMiddleware::getRoleIndex(), ENT_QUOTES, 'UTF-8')?>">Cafe</a></h1>
    </div>
    <div class="navigation">
        <ul>
            <li><a href="/Cafe/app/Views/users/menu.php">Меню</a></li>

            <?php if(RoleMiddleware::hasAnyRole([2, 4, 5])): ?>
                <li><a href="/Cafe/app/Views/cooks/createCategory.php">Создать категорию</a></li>
                <li><a href="/Cafe/app/Views/cooks/createDish.php">Создать блюдо</a></li>
                <li><a href="/Cafe/app/Views/cooks/Category.php">Категории</a></li>
                <li><a href="/Cafe/app/Views/cooks/orders.php">Заказы кухни</a></li>
            <?php endif; ?>

            <?php if(AuthMiddleware::isAuthenticated()): ?>
                <li><a href="/Cafe/app/Views/users/reservation.php">Бронирование столика</a></li>
                <li><a href="/Cafe/app/Views/users/basket.php">Корзина</a></li>
            <?php endif; ?>

            <?php if(RoleMiddleware::hasAnyRole([3, 4, 5])): ?>
                <li><a href="/Cafe/app/Views/waiters/tables.php">Столы официанта</a></li>
            <?php endif; ?>

            <?php if(RoleMiddleware::hasAnyRole([4, 5])): ?>
                <li><a href="/Cafe/app/Views/managers/tables.php">Управление столами</a></li>
                <li><a href="/Cafe/app/Views/managers/reservations.php">Бронирования</a></li>
                <li><a href="/Cafe/app/Views/managers/orders.php">Официанты заказов</a></li>
                <li><a href="/Cafe/app/Views/managers/statistics.php">Статистика</a></li>
            <?php endif; ?>

            <?php if(RoleMiddleware::hasRole(5)): ?>
                <li><a href="/Cafe/app/Views/admins/users.php">Пользователи и роли</a></li>
                <li><a href="/Cafe/app/Views/admins/history.php">История действий</a></li>
            <?php endif; ?>
        </ul>
    </div>
    <?php if(AuthMiddleware::isAuthenticated()): ?>
    <div class="user">
        <div class="user_name">
            <a href="<?=htmlspecialchars(RoleMiddleware::getRoleIndex(), ENT_QUOTES, 'UTF-8')?>"><?= htmlspecialchars(session::get('name')) ?></a>
            <form action="/Cafe/app/Controlers/AuthController.php?action=logout" method="post">
                <?=Csrf::input()?>
                <button type="submit">Выйти</button>
            </form>
        </div>
    </div>
    <?php else: ?>
        <div class="user">
            <a href="/Cafe/app/Views/auth/login.php">Войти</a>
        </div>


    <?php endif; ?>
</header>
