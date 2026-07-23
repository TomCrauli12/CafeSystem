<?php

require_once __DIR__ . '/../../database/BD.php';

class AdminRepository{

    private PDO $conn;

    public function __construct(){

        $this->conn = BD::getConnection();
    }

    public function getRoles(){

        $query = $this->conn->query("SELECT * FROM `role` ORDER BY `id` ASC");

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUsers(){

        $query = $this->conn->query("SELECT `users`.`id`, `users`.`login`, `users`.`name`, `users`.`phone`, `users`.`roleId`, `users`.`created`, `role`.`roleName` FROM `users` JOIN `role` ON `role`.`id` = `users`.`roleId` WHERE `users`.`deleted` IS NULL ORDER BY `users`.`id` ASC");

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function roleExists($roleId){

        $query = $this->conn->prepare("SELECT `id` FROM `role` WHERE `id` = ? LIMIT 1");

        $query->execute([$roleId]);

        return (bool)$query->fetch(PDO::FETCH_ASSOC);
    }

    public function loginExists($login){

        $query = $this->conn->prepare("SELECT `id` FROM `users` WHERE `login` = ? AND `deleted` IS NULL LIMIT 1");

        $query->execute([$login]);

        return (bool)$query->fetch(PDO::FETCH_ASSOC);
    }

    public function createUser($login, $name, $phone, $password, $roleId, $created){

        $query = $this->conn->prepare("INSERT INTO `users` (`login`, `name`, `phone`, `password`, `roleId`, `created`) VALUES (?, ?, ?, ?, ?, ?)");

        $query->execute([$login, $name, $phone, $password, $roleId, $created]);

        return (int)$this->conn->lastInsertId();
    }

    public function updateUserRole($userId, $roleId){

        $query = $this->conn->prepare("UPDATE `users` SET `roleId` = ? WHERE `id` = ? AND `deleted` IS NULL");

        $query->execute([$roleId, $userId]);

        return $query->rowCount() > 0;
    }
}

?>
