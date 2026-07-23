<?php

    require_once __DIR__ . "/../../database/BD.php";


    class UserRepository{

        private PDO $conn;

        public function __construct(){

            $this->conn = BD::getConnection();
        }


        public function register($login, $name, $phone, $password, $created){

            $query = $this->conn->prepare("INSERT INTO users (`login`, `name`, `phone`, `password`, `created`) VALUES (?, ?, ?, ?, ?)");

            $query->execute([$login, $name, $phone, $password, $created]);

            return (int) $this->conn->lastInsertId();
        }

        public function indefication($login, $password){

            $query = $this->conn->prepare('SELECT `id`, `login`, `name`, `password`, `roleId` FROM `users` WHERE login = :login LIMIT 1');

            $query->execute(['login'=>$login]);

            $user = $query->fetch(PDO::FETCH_ASSOC);

            if(!$user){

                return null;
            }

            if(!password_verify($password, $user['password'])){

                return null;
            }

            return $user;
        }




    }
    



?>