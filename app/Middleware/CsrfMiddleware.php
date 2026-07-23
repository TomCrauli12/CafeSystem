<?php

require_once __DIR__ . '/../sessions/Sessions.php';

class Csrf{

    private const SESSION_KEY = 'csrf_token';

    public static function token(): string{

        session::start();

        if(!isset($_SESSION[self::SESSION_KEY])){

            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    public static function input(): string{

        return '<input type="hidden" name="csrfToken" value="' . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function requireValid(): void{

        session::start();

        if(($_SERVER['REQUEST_METHOD'] ?? '')!=='POST'){

            http_response_code(405);

            exit('Метод запроса не разрешён');
        }

        $token = $_POST['csrfToken'] ?? '';

        $sessionToken = $_SESSION[self::SESSION_KEY] ?? '';

        if(!is_string($token) || !is_string($sessionToken) || $sessionToken==='' || !hash_equals($sessionToken, $token)){

            http_response_code(419);

            exit('Недействительный CSRF-токен');
        }
    }
}

?>
