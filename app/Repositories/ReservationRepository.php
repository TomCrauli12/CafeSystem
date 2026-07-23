<?php

require_once __DIR__ . '/../../database/BD.php';

class ReservationRepository{

    private PDO $conn;

    public function __construct(){

        $this->conn = BD::getConnection();
    }

    public function create($userId, $tableId, $reservationAt, $durationMinutes, $guests, $created){

        $reservationEnd = date('Y-m-d H:i:s', strtotime($reservationAt . " +$durationMinutes minutes"));

        try{
            
            $this->conn->beginTransaction();

            $tableQuery = $this->conn->prepare("SELECT `id` FROM `cafe_tables` WHERE `id` = ? AND `active` = 1 AND `deleted` IS NULL AND `seats` >= ? FOR UPDATE");

            $tableQuery->execute([$tableId, $guests]);

            if(!$tableQuery->fetch()){

                throw new RuntimeException('Стол не существует или не подходит по количеству мест');
            }

            $activeOrderQuery = $this->conn->prepare("SELECT `orders`.`id` FROM `orders` JOIN `order_statuses` ON `order_statuses`.`id` = `orders`.`statusId` WHERE `orders`.`tableId` = ? AND `order_statuses`.`code` NOT IN ('completed', 'cancelled') LIMIT 1");
            
            $activeOrderQuery->execute([$tableId]);

            if($activeOrderQuery->fetch()){
                
                throw new RuntimeException('Этот стол сейчас занят');
            }

            $userReservationQuery = $this->conn->prepare("SELECT `reservations`.`id` FROM `reservations` JOIN `reservation_statuses` ON `reservation_statuses`.`id` = `reservations`.`statusId` WHERE `reservations`.`userId` = ? AND `reservation_statuses`.`code` IN ('new', 'confirmed') AND `reservations`.`reservationAt` < ? AND DATE_ADD(`reservations`.`reservationAt`, INTERVAL `reservations`.`durationMinutes` MINUTE) > ? LIMIT 1");
            
            $userReservationQuery->execute([$userId, $reservationEnd, $reservationAt]);

            if($userReservationQuery->fetch()){

                throw new RuntimeException('У вас уже есть бронь на это время');
            }

            $conflictQuery = $this->conn->prepare("SELECT `reservations`.`id` FROM `reservations` JOIN `reservation_statuses` ON `reservation_statuses`.`id` = `reservations`.`statusId` WHERE `reservations`.`tableId` = ? AND `reservation_statuses`.`code` IN ('new', 'confirmed') AND `reservations`.`reservationAt` < ? AND DATE_ADD(`reservations`.`reservationAt`, INTERVAL `reservations`.`durationMinutes` MINUTE) > ? LIMIT 1");
           
            $conflictQuery->execute([$tableId, $reservationEnd, $reservationAt]);

            if($conflictQuery->fetch()){

                throw new RuntimeException('Этот стол уже забронирован на выбранное время');
            }

            $query = $this->conn->prepare("INSERT INTO `reservations` (`userId`, `tableId`, `guests`, `reservationAt`, `durationMinutes`, `statusId`, `created`) VALUES (?, ?, ?, ?, ?, (SELECT `id` FROM `reservation_statuses` WHERE `code` = 'new' LIMIT 1), ?)");
            
            $query->execute([$userId, $tableId, $guests, $reservationAt, $durationMinutes, $created]);

            $reservationId = (int)$this->conn->lastInsertId();

            $this->conn->commit();

            return $reservationId;

        }catch(Throwable $exception){

            if($this->conn->inTransaction()){

                $this->conn->rollBack();
            }

            throw $exception;
        }
    }

    public function getByUserId($userId, $currentDateTime){

        $query = $this->conn->prepare("SELECT `reservations`.`id`, `reservations`.`guests`, `reservations`.`reservationAt`, `reservations`.`durationMinutes`, `cafe_tables`.`number` AS `tableNumber`, `cafe_tables`.`seats`, `reservation_statuses`.`code` AS `statusCode`, `reservation_statuses`.`name` AS `statusName`, CASE WHEN `reservation_statuses`.`code` IN ('new', 'confirmed') AND `reservations`.`reservationAt` > ? THEN 1 ELSE 0 END AS `canCancel` FROM `reservations` JOIN `cafe_tables` ON `cafe_tables`.`id` = `reservations`.`tableId` JOIN `reservation_statuses` ON `reservation_statuses`.`id` = `reservations`.`statusId` WHERE `reservations`.`userId` = ? ORDER BY `reservations`.`reservationAt` DESC");
        
        $query->execute([$currentDateTime, $userId]);

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cancel($reservationId, $userId, $currentDateTime){

        $query = $this->conn->prepare("UPDATE `reservations` JOIN `reservation_statuses` ON `reservation_statuses`.`id` = `reservations`.`statusId` SET `reservations`.`statusId` = (SELECT `id` FROM `reservation_statuses` AS `cancelStatus` WHERE `cancelStatus`.`code` = 'cancelled' LIMIT 1), `reservations`.`updated` = ? WHERE `reservations`.`id` = ? AND `reservations`.`userId` = ? AND `reservation_statuses`.`code` IN ('new', 'confirmed') AND `reservations`.`reservationAt` > ?");
        
        $query->execute([$currentDateTime, $reservationId, $userId, $currentDateTime]);

        return $query->rowCount() > 0;
    }
}

?>