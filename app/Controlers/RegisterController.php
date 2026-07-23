<?php

    require_once __DIR__ . '/../Services/RegisterService.php';
    require_once __DIR__ . '/../Middleware/userMeddleware.php';
    require_once __DIR__ . '/../Middleware/CsrfMiddleware.php';
    require_once __DIR__ . '/../Validators/userValidator.php';
    require_once __DIR__ . '/../core/Router.php';
    require_once __DIR__ . '/../Services/HistoryService.php';

    AuthMiddleware::requireGuest();

    Csrf::requireValid();

    $router = new Router();

    $router->post('register', function(){

        try{

            $login = UserValidator::login($_POST['login'] ?? '');

            $name = UserValidator::name($_POST['name'] ?? '');

            $phone = UserValidator::phone($_POST['phone'] ?? '');
            
            $enterPassword = UserValidator::password($_POST['enterPassword'] ?? '');

            date_default_timezone_set('Europe/Moscow');

            $created = date('Y-m-d H:i:s');

            $userId = userRegister::register($login, $name, $phone, $enterPassword, $created);

            HistoryService::log('register', 'user', $userId, 'Клиент зарегистрировался', ['login'=>$login, 'name'=>$name], $userId, 1);

        }catch(PDOException $exception){

            $_SESSION['register_error'] = $exception->getCode()==='23000' ? 'Пользователь с таким логином уже существует' : 'Не удалось создать аккаунт';

            HistoryService::log('register_failed', 'user', null, 'Неудачная регистрация', ['login'=>is_string($_POST['login'] ?? null) ? $_POST['login'] : '']);

            Router::redirect('/Cafe/app/Views/auth/register.php');

        }catch(RuntimeException $exception){

            $_SESSION['register_error'] = $exception->getMessage();

            HistoryService::log('register_failed', 'user', null, 'Неудачная регистрация', ['login'=>is_string($_POST['login'] ?? null) ? $_POST['login'] : '', 'error'=>$exception->getMessage()]);

            Router::redirect('/Cafe/app/Views/auth/register.php');
        }

        Router::redirect('/Cafe/public/index.php');
    });

    $router->dispatch('/Cafe/app/Views/auth/register.php');

?>
