<?php

require_once __DIR__ . '/../Services/DishService.php';
require_once __DIR__ . '/../Middleware/roleMiddleware.php';
require_once __DIR__ . '/../Middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../Validators/Validator.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../Services/HistoryService.php';

RoleMiddleware::requireAnyRole([2, 4, 5]);

Csrf::requireValid();

$router = new Router();

$action = $router->getAction();

$id = 0;

$router->post('createDish', function(){

    $name = Validator::text($_POST['name'] ?? '', 'Название блюда');

    $description = Validator::text($_POST['description'] ?? '', 'Описание');

    $structure = Validator::text($_POST['structure'] ?? '', 'Состав');

    $categoryId = Validator::integer($_POST['categoryId'] ?? 0, 'Категория');

    $cooktime = Validator::integer($_POST['cooktime'] ?? 0, 'Время приготовления', 1, 1440);

    $extension = Validator::image($_FILES['photo'] ?? null);
    
    $photo = uniqid() . '.' . $extension;

    $uploadDirectory = dirname(__DIR__, 2) . '/public/uploads/dish/';

    if(!is_dir($uploadDirectory)){

        mkdir($uploadDirectory, 0777, true);
    }

    if(!move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDirectory . $photo)){

        throw new RuntimeException('Не удалось сохранить изображение');
    }

    date_default_timezone_set('Europe/Moscow');

    $created = date('Y-m-d H:i:s');

    $dishId = Dish::create($name, $photo, $description, $structure, $cooktime, $categoryId, $created);

    HistoryService::log('create_dish', 'dish', $dishId, 'Создано блюдо', ['name'=>$name, 'categoryId'=>$categoryId, 'cooktime'=>$cooktime]);

    Router::redirect('/Cafe/app/Views/cooks/createDish.php');
});

$router->post('updateDish', function() use (&$id){

    $id = Validator::integer($_POST['id'] ?? 0, 'Блюдо');

    $name = Validator::text($_POST['name'] ?? '', 'Название блюда');

    $description = Validator::text($_POST['description'] ?? '', 'Описание');

    $structure = Validator::text($_POST['structure'] ?? '', 'Состав');

    $categoryId = Validator::integer($_POST['categoryId'] ?? 0, 'Категория');

    $cooktime = Validator::integer($_POST['cooktime'] ?? 0, 'Время приготовления', 1, 1440);

    $dish = Dish::getById($id);

    if(!$dish){

        throw new RuntimeException('Блюдо не найдено');
    }

    $photo = $dish['photo'];

    $extension = Validator::image($_FILES['photo'] ?? null, false);

    if($extension!==null){
        
        $photo = uniqid() . '.' . $extension;

        $uploadDirectory = dirname(__DIR__, 2) . '/public/uploads/dish/';

        if(!is_dir($uploadDirectory)){

            mkdir($uploadDirectory, 0777, true);
        }

        if(!move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDirectory . $photo)){

            throw new RuntimeException('Не удалось сохранить изображение');
        }
    }

    Dish::update($id, $name, $photo, $description, $structure, $cooktime, $categoryId);

    HistoryService::log('update_dish', 'dish', $id, 'Изменено блюдо', ['name'=>$name, 'categoryId'=>$categoryId, 'cooktime'=>$cooktime]);

    Router::redirect('/Cafe/app/Views/cooks/editDish.php?id=' . $id);
});

try{

    $router->dispatch('/Cafe/app/Views/cooks/createDish.php');

}catch(RuntimeException $exception){

    $_SESSION['cook_error'] = $exception->getMessage();

    HistoryService::log('action_failed', 'dish', $id>0 ? $id : null, 'Ошибка работы с блюдом', ['action'=>$action, 'error'=>$exception->getMessage()]);

    Router::redirect($action==='updateDish' && $id>0 ? '/Cafe/app/Views/cooks/editDish.php?id=' . $id : '/Cafe/app/Views/cooks/createDish.php');
}

Router::redirect('/Cafe/app/Views/cooks/createDish.php');
