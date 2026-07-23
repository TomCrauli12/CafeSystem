<?php

require_once __DIR__ . '/../../database/BD.php';

class HistoryRepository{

    private PDO $conn;

    public function __construct(){

        $this->conn = BD::getConnection();
    }

    public function create($userId, $roleId, $action, $entityType, $entityId, $description, $details, $ipAddress, $created){

        $query = $this->conn->prepare("INSERT INTO `activity_logs` (`userId`, `roleId`, `action`, `entityType`, `entityId`, `description`, `details`, `ipAddress`, `created`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $query->execute([$userId, $roleId, $action, $entityType, $entityId, $description, $details, $ipAddress, $created]);

        return (int)$this->conn->lastInsertId();
    }

    public function getAll(){

        $query = $this->conn->query("SELECT `activity_logs`.*, `users`.`name` AS `userName`, `users`.`login`, `role`.`roleName` FROM `activity_logs` LEFT JOIN `users` ON `users`.`id` = `activity_logs`.`userId` LEFT JOIN `role` ON `role`.`id` = `activity_logs`.`roleId` ORDER BY `activity_logs`.`id` DESC");

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
