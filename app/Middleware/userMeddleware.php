<?php

require_once __DIR__ . '/../sessions/Sessions.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../Services/HistoryService.php';

class AuthMiddleware{

    public static function isAuthenticated(): bool{

        session::start();

        return session::has('user_id');
    }

    public static function requireAuth(string $redirectTo = '/Cafe/app/Views/auth/login.php'): void{

        if (!self::isAuthenticated()) {

            HistoryService::log('access_denied', 'page', null, 'Неавторизованный доступ к закрытой странице', ['uri'=>$_SERVER['REQUEST_URI'] ?? '']);

            Router::redirect($redirectTo);
        }
    }

    public static function requireGuest(string $redirectTo = '/Cafe/public/index.php'): void{

        if (self::isAuthenticated()) {

            HistoryService::log('guest_page_denied', 'page', null, 'Авторизованный пользователь открыл гостевую страницу', ['uri'=>$_SERVER['REQUEST_URI'] ?? '']);

            Router::redirect($redirectTo);
        }
    }
}
