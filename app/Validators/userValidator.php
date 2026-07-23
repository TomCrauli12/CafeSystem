<?php

require_once __DIR__ . '/Validator.php';

class UserValidator{

    public static function login($login){

        return Validator::text($login, 'Логин');
    }

    public static function name($name){

        return Validator::text($name, 'Имя');
    }

    public static function phone($phone){

        return Validator::text($phone, 'Телефон');
    }

    public static function password($password){

        return Validator::text($password, 'Пароль');
    }
}

?>
