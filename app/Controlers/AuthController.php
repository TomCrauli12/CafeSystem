<?php

    require_once __DIR__ . '/../Services/AuthService.php';
    require_once __DIR__ . '/../sessions/Sessions.php';
    require_once __DIR__ . '/../Middleware/userMeddleware.php';
    require_once __DIR__ . '/../Middleware/roleMiddleware.php';
    require_once __DIR__ . '/../Middleware/CsrfMiddleware.php';
    require_once __DIR__ . '/../Validators/userValidator.php';
    require_once __DIR__ . '/../core/Router.php';
    require_once __DIR__ . '/../Services/HistoryService.php';


    session::start();

    Csrf::requireValid();

    $router = new Router();

    $router->post('indefication', function(){

        AuthMiddleware::requireGuest();

        try{

            $login = UserValidator::login($_POST['login'] ?? '');

            $password = UserValidator::password($_POST['password'] ?? '');

        }catch(RuntimeException $exception){

            $_SESSION['loginError'] = $exception->getMessage();

            HistoryService::log('login_failed', 'user', null, 'Неудачная попытка входа', ['login'=>is_string($_POST['login'] ?? null) ? $_POST['login'] : '']);

            Router::redirect('/Cafe/app/Views/auth/login.php');
        }

        $user = AuthService::indefication($login, $password);

        if(!$user){
            
            $_SESSION['loginError'] = 'Неверный логин или пароль';

            HistoryService::log('login_failed', 'user', null, 'Неверный логин или пароль', ['login'=>$login]);

            Router::redirect('/Cafe/app/Views/auth/login.php');
        }

        session::indefication($user);

        HistoryService::log('login', 'user', (int)$user['id'], 'Пользователь вошёл в систему');

        RoleMiddleware::redirectToRoleIndex();
    });



    $router->post('logout', function(){

        AuthMiddleware::requireAuth();

        HistoryService::log('logout', 'user', (int)session::get('user_id'), 'Пользователь вышел из системы');

        session::destroy();

        Router::redirect('/Cafe/app/Views/auth/login.php');
    });

    $router->dispatch('/Cafe/app/Views/auth/login.php');

?>
