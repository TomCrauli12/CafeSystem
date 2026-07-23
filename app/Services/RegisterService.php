<?php

    require_once __DIR__ . '/../Repositories/UserRepository.php';

    class userRegister{

        static function register($login, $name, $phone, $enterPassword, $created){

            $UserRepository = new UserRepository();

            $password = password_hash($enterPassword, PASSWORD_DEFAULT);

            $userId = $UserRepository->register($login, $name, $phone, $password, $created);

            return $userId;

        }
    }




?>