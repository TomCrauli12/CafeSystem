<?php

require_once __DIR__ . '/../Services/CategoryService.php';
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

$router->post('createCategory', function(){

    $name = Validator::text($_POST['name'] ?? '', 'Название категории');

    date_default_timezone_set('Europe/Moscow');

    $created = date('Y-m-d H:i:s');

    $categoryId = Category::create($name, $created);

    HistoryService::log('create_category', 'category', $categoryId, 'Создана категория', ['name'=>$name]);

    Router::redirect('/Cafe/app/Views/cooks/createCategory.php');
});

$router->post('updateCategory', function() use (&$id){

    $id = Validator::integer($_POST['id'] ?? 0, 'Категория');

    $name = Validator::text($_POST['name'] ?? '', 'Название категории');

    Category::update($id, $name);

    HistoryService::log('update_category', 'category', $id, 'Изменена категория', ['name'=>$name]);

    Router::redirect('/Cafe/app/Views/cooks/editCategory.php?id=' . $id);
});

try{

    $router->dispatch('/Cafe/app/Views/cooks/createCategory.php');

}catch(RuntimeException $exception){

    $_SESSION['cook_error'] = $exception->getMessage();

    HistoryService::log('action_failed', 'category', $id>0 ? $id : null, 'Ошибка работы с категорией', ['action'=>$action, 'error'=>$exception->getMessage()]);

    Router::redirect($action==='updateCategory' && $id>0 ? '/Cafe/app/Views/cooks/editCategory.php?id=' . $id : '/Cafe/app/Views/cooks/createCategory.php');
}

Router::redirect('/Cafe/app/Views/cooks/createCategory.php');
