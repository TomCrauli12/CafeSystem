<?php

require_once __DIR__ . '/../../Services/AdminService.php';
require_once __DIR__ . '/../../sessions/Sessions.php';
require_once __DIR__ . '/../../Middleware/roleMiddleware.php';

RoleMiddleware::requireRole(5);

$users = AdminService::getUsers();

$roles = AdminService::getRoles();

$error = $_SESSION['admin_error'] ?? null;

unset($_SESSION['admin_error']);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Пользователи и роли</title>
</head>
<body>

<?php require_once '../../../include/header.php';?>

<h1>Пользователи и роли</h1>

<?php if($error):?>
    <p><?=htmlspecialchars($error, ENT_QUOTES, 'UTF-8')?></p>
<?php endif;?>

<?php if(isset($_GET['userId'])):?>
    <p>Пользователь №<?=(int)$_GET['userId']?> создан</p>
<?php endif;?>

<?php if(isset($_GET['roleUpdated'])):?>
    <p>Роль пользователя изменена</p>
<?php endif;?>

<h2>Создать пользователя</h2>

<form action="/Cafe/app/Controlers/AdminController.php?action=createUser" method="post">
    <?=Csrf::input()?>
    <label for="login">Логин:</label>
    <input type="text" name="login" id="login" minlength="2" maxlength="20" required>

    <label for="name">Имя:</label>
    <input type="text" name="name" id="name" minlength="2" maxlength="20" required>

    <label for="phone">Номер телефона:</label>
    <input type="tel" name="phone" id="phone" minlength="2" maxlength="20" required>

    <label for="enterPassword">Пароль:</label>
    <input type="password" name="enterPassword" id="enterPassword" minlength="2" maxlength="20" required>

    <label for="roleId">Роль:</label>
    <select name="roleId" id="roleId" required>
        <option value="">Выберите роль</option>
        <?php foreach($roles as $role):?>
            <option value="<?=(int)$role['id']?>"><?=htmlspecialchars($role['roleName'], ENT_QUOTES, 'UTF-8')?></option>
        <?php endforeach;?>
    </select>

    <button type="submit">Создать пользователя</button>
</form>

<h2>Все пользователи</h2>

<?php if(empty($users)):?>
    <p>Пользователей нет</p>
<?php else:?>
    <?php foreach($users as $user):?>
        <div>
            <h3><?=htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8')?></h3>
            <p>ID:<?=(int)$user['id']?></p>
            <p>Логин:<?=htmlspecialchars($user['login'], ENT_QUOTES, 'UTF-8')?></p>
            <p>Телефон:<?=htmlspecialchars($user['phone'], ENT_QUOTES, 'UTF-8')?></p>
            <p>Текущая роль:<?=htmlspecialchars($user['roleName'], ENT_QUOTES, 'UTF-8')?></p>

            <form action="/Cafe/app/Controlers/AdminController.php?action=updateUserRole" method="post">
                <?=Csrf::input()?>
                <input type="hidden" name="userId" value="<?=(int)$user['id']?>">

                <label for="roleId-<?=(int)$user['id']?>">Новая роль:</label>
                <select name="roleId" id="roleId-<?=(int)$user['id']?>" required>
                    <?php foreach($roles as $role):?>
                        <option value="<?=(int)$role['id']?>" <?=(int)$user['roleId']===(int)$role['id'] ? 'selected' : ''?>><?=htmlspecialchars($role['roleName'], ENT_QUOTES, 'UTF-8')?></option>
                    <?php endforeach;?>
                </select>

                <button type="submit">Изменить роль</button>
            </form>
        </div>

        <hr>
    <?php endforeach;?>
<?php endif;?>

</body>
</html>
