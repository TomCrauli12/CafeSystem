<?php

    require_once __DIR__ . '/../Repositories/UserRepository.php';

    class AuthService{

        static function indefication($login, $password){

            $userRepository = new userRepository;

            return $userRepository->indefication($login, $password);

        }

        
    }

?>