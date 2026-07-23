<?php

require_once __DIR__ . '/../../database/BD.php';

class CategoryRespositiry{

    private PDO $conn;

    public function __construct(){

        $this->conn = BD::getConnection();
    }

    public function create($name, $created){

        $query = $this->conn->prepare('INSERT INTO `categories` (`name`, `created`) VALUES (?, ?)');

        $query->execute([$name, $created]);

        return (int)$this->conn->lastInsertId();
    }

    public function getAll(){

        $query = $this->conn->query('SELECT * FROM `categories` ORDER BY `id` DESC');

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id){

        $query = $this->conn->prepare('SELECT * FROM `categories` WHERE `id` = ?');

        $query->execute([$id]);

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $name){

        $query = $this->conn->prepare('UPDATE `categories` SET `name` = ? WHERE `id` = ?');

        return $query->execute([$name, $id]);
    }
}

class DishRespositiry{

    private PDO $conn;

    public function __construct(){

        $this->conn = BD::getConnection();
    }

    public function create($name, $photo, $description, $structure, $cooktime, $categoryId, $created){

        $query = $this->conn->prepare('INSERT INTO `menu` (`name`, `photo`, `description`, `structure`, `cooktime`, `categoryId`, `created`) VALUES (?, ?, ?, ?, ?, ?, ?)');

        $query->execute([$name, $photo, $description, $structure, $cooktime, $categoryId, $created]);

        return (int)$this->conn->lastInsertId();
    }

    public function getAll(){

        $query = $this->conn->query('SELECT * FROM `menu` ORDER BY `id` DESC');

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id){

        $query = $this->conn->prepare('SELECT * FROM `menu` WHERE `id` = ?');

        $query->execute([$id]);

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $name, $photo, $description, $structure, $cooktime, $categoryId){
        
        $query = $this->conn->prepare('UPDATE `menu` SET `name` = ?, `photo` = ?, `description` = ?, `structure` = ?, `cooktime` = ?, `categoryId` = ? WHERE `id` = ?');

        return $query->execute([$name, $photo, $description, $structure, $cooktime, $categoryId, $id]);
    }

    public function getAllWithCategories(){

        $query = $this->conn->query("SELECT `menu`.*, `categories`.`name` AS `categoryName` FROM `menu` JOIN `categories` ON `categories`.`id` = `menu`.`categoryId` WHERE `menu`.`deleted` IS NULL ORDER BY `categories`.`name`, `menu`.`name`");

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getByIds($ids){

        if(empty($ids)){

            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $query = $this->conn->prepare("SELECT * FROM `menu` WHERE `id` IN ($placeholders)");

        $query->execute(array_values($ids));

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAvailableWithCategories(){
        
        $query = $this->conn->query("SELECT `menu`.*, `categories`.`name` AS `categoryName` FROM `menu` JOIN `categories` ON `categories`.`id` = `menu`.`categoryId` WHERE `menu`.`isStopped` = 0 AND `menu`.`deleted` IS NULL ORDER BY `categories`.`name`, `menu`.`name`");
        
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchAvailable($search){

        $search = '%' . $search . '%';

        $query = $this->conn->prepare("SELECT `menu`.*, `categories`.`name` AS `categoryName` FROM `menu` JOIN `categories` ON `categories`.`id` = `menu`.`categoryId` WHERE `menu`.`isStopped` = 0 AND `menu`.`deleted` IS NULL AND (`menu`.`name` LIKE ? OR `menu`.`description` LIKE ? OR `menu`.`structure` LIKE ? OR `categories`.`name` LIKE ?) ORDER BY `categories`.`name`, `menu`.`name`");

        $query->execute([$search, $search, $search, $search]);

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStopList($dishId, $isStopped){

        $query = $this->conn->prepare("UPDATE `menu` SET `isStopped` = ?, `stoppedAt` = CASE WHEN ? = 1 THEN NOW() ELSE NULL END WHERE `id` = ? AND `deleted` IS NULL");

        $query->execute([$isStopped, $isStopped, $dishId]);

        return $query->rowCount() > 0;
    }

    public function isAvailable($dishId){

        $query = $this->conn->prepare("SELECT `id` FROM `menu` WHERE `id` = ? AND `isStopped` = 0 AND `deleted` IS NULL LIMIT 1");

        $query->execute([$dishId]);

        return (bool)$query->fetch(PDO::FETCH_ASSOC);
    }


}


?>
