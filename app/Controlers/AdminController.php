<?php

require_once __DIR__ . '/../Services/AdminService.php';
require_once __DIR__ . '/../sessions/Sessions.php';
require_once __DIR__ . '/../Middleware/roleMiddleware.php';
require_once __DIR__ . '/../Middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../Validators/userValidator.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../Services/HistoryService.php';

RoleMiddleware::requireRole(5);

Csrf::requireValid();

date_default_timezone_set('Europe/Moscow');

$router = new Router();

$router->post('createUser', function(){

        $login = UserValidator::login($_POST['login'] ?? '');

        $name = UserValidator::name($_POST['name'] ?? '');

        $phone = UserValidator::phone($_POST['phone'] ?? '');

        $enterPassword = UserValidator::password($_POST['enterPassword'] ?? '');

        $roleId = Validator::integer($_POST['roleId'] ?? 0, 'Роль', 1, 5);

        $userId = AdminService::createUser($login, $name, $phone, $enterPassword, $roleId, date('Y-m-d H:i:s'));

        HistoryService::log('create_user', 'user', $userId, 'Администратор создал пользователя', ['login'=>$login, 'name'=>$name, 'roleId'=>$roleId]);

        Router::redirect('/Cafe/app/Views/admins/users.php?userId=' . $userId);
});

$router->post('updateUserRole', function(){

        $userId = Validator::integer($_POST['userId'] ?? 0, 'Пользователь');

        $roleId = Validator::integer($_POST['roleId'] ?? 0, 'Роль', 1, 5);

        $adminId = (int)session::get('user_id');

        if(!AdminService::updateUserRole($userId, $roleId, $adminId)){

            throw new RuntimeException('Пользователь не найден или роль уже установлена');
        }

        HistoryService::log('update_user_role', 'user', $userId, 'Администратор изменил роль пользователя', ['roleId'=>$roleId]);

        Router::redirect('/Cafe/app/Views/admins/users.php?roleUpdated=1');
});

try{

    $router->dispatch('/Cafe/app/Views/admins/users.php');

}catch(PDOException $exception){

    $_SESSION['admin_error'] = $exception->getCode()==='23000' ? 'Пользователь с таким логином уже существует' : 'Не удалось сохранить изменения';

    HistoryService::log('action_failed', 'admin', null, 'Ошибка действия администратора', ['action'=>$router->getAction(), 'error'=>$_SESSION['admin_error']]);

    Router::redirect('/Cafe/app/Views/admins/users.php');

}catch(RuntimeException $exception){

    $_SESSION['admin_error'] = $exception->getMessage();

    HistoryService::log('action_failed', 'admin', null, 'Ошибка действия администратора', ['action'=>$router->getAction(), 'error'=>$exception->getMessage()]);

    Router::redirect('/Cafe/app/Views/admins/users.php');

}catch(Throwable $exception){

    $_SESSION['admin_error'] = 'Не удалось выполнить действие';

    HistoryService::log('action_failed', 'admin', null, 'Ошибка действия администратора', ['action'=>$router->getAction()]);

    Router::redirect('/Cafe/app/Views/admins/users.php');
}

Router::redirect('/Cafe/app/Views/admins/users.php');

?>
