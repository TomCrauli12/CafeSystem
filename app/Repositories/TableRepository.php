<?php

require_once __DIR__ . '/../../database/BD.php';

class TableRepository{
    
    private PDO $conn;

    public function __construct(){

        $this->conn = BD::getConnection();
    }

    public function getAvailableForReservation($reservationAt, $reservationEnd, $guests){

        $query = $this->conn->prepare("SELECT `cafe_tables`.* FROM `cafe_tables` WHERE `cafe_tables`.`active` = 1 AND `cafe_tables`.`deleted` IS NULL AND `cafe_tables`.`seats` >= ? AND NOT EXISTS (SELECT 1 FROM `reservations` JOIN `reservation_statuses` ON `reservation_statuses`.`id` = `reservations`.`statusId` WHERE `reservations`.`tableId` = `cafe_tables`.`id` AND `reservation_statuses`.`code` IN ('new', 'confirmed') AND `reservations`.`reservationAt` < ? AND DATE_ADD(`reservations`.`reservationAt`, INTERVAL `reservations`.`durationMinutes` MINUTE) > ?) AND NOT EXISTS (SELECT 1 FROM `orders` JOIN `order_statuses` ON `order_statuses`.`id` = `orders`.`statusId` WHERE `orders`.`tableId` = `cafe_tables`.`id` AND `order_statuses`.`code` NOT IN ('completed', 'cancelled') AND `orders`.`created` < ? AND DATE_ADD(`orders`.`created`, INTERVAL 120 MINUTE) > ?) ORDER BY `cafe_tables`.`number` ASC");
        
        $query->execute([$guests, $reservationEnd, $reservationAt, $reservationEnd, $reservationAt]);

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAvailableForOrder($userId, $orderAt, $orderEnd){
        
        $query = $this->conn->prepare("SELECT `cafe_tables`.*, EXISTS (SELECT 1 FROM `reservations` JOIN `reservation_statuses` ON `reservation_statuses`.`id` = `reservations`.`statusId` WHERE `reservations`.`tableId` = `cafe_tables`.`id` AND `reservations`.`userId` = ? AND `reservation_statuses`.`code` IN ('new', 'confirmed') AND `reservations`.`reservationAt` < ? AND DATE_ADD(`reservations`.`reservationAt`, INTERVAL `reservations`.`durationMinutes` MINUTE) > ?) AS `hasOwnReservation` FROM `cafe_tables` WHERE `cafe_tables`.`active` = 1 AND `cafe_tables`.`deleted` IS NULL AND NOT EXISTS (SELECT 1 FROM `orders` JOIN `order_statuses` ON `order_statuses`.`id` = `orders`.`statusId` WHERE `orders`.`tableId` = `cafe_tables`.`id` AND `order_statuses`.`code` NOT IN ('completed', 'cancelled')) AND NOT EXISTS (SELECT 1 FROM `reservations` JOIN `reservation_statuses` ON `reservation_statuses`.`id` = `reservations`.`statusId` WHERE `reservations`.`tableId` = `cafe_tables`.`id` AND `reservations`.`userId` != ? AND `reservation_statuses`.`code` IN ('new', 'confirmed') AND `reservations`.`reservationAt` < ? AND DATE_ADD(`reservations`.`reservationAt`, INTERVAL `reservations`.`durationMinutes` MINUTE) > ?) ORDER BY `cafe_tables`.`number` ASC");
        
        $query->execute([$userId, $orderEnd, $orderAt, $userId, $orderEnd, $orderAt]);

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>