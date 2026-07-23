<?php

require_once __DIR__ . '/../../database/BD.php';

class ManagerRepository{

    private PDO $conn;

    public function __construct(){

        $this->conn = BD::getConnection();
    }

    public function getTables(){

        $query = $this->conn->query("SELECT `cafe_tables`.*, EXISTS (SELECT 1 FROM `orders` JOIN `order_statuses` ON `order_statuses`.`id` = `orders`.`statusId` WHERE `orders`.`tableId` = `cafe_tables`.`id` AND `order_statuses`.`code` NOT IN ('completed', 'cancelled')) AS `hasActiveOrder`, EXISTS (SELECT 1 FROM `reservations` JOIN `reservation_statuses` ON `reservation_statuses`.`id` = `reservations`.`statusId` WHERE `reservations`.`tableId` = `cafe_tables`.`id` AND `reservation_statuses`.`code` IN ('new', 'confirmed') AND DATE_ADD(`reservations`.`reservationAt`, INTERVAL `reservations`.`durationMinutes` MINUTE) > NOW()) AS `hasActiveReservation` FROM `cafe_tables` WHERE `cafe_tables`.`deleted` IS NULL ORDER BY `cafe_tables`.`number` ASC");

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createTable($number, $seats, $created){

        $query = $this->conn->prepare("INSERT INTO `cafe_tables` (`number`, `seats`, `active`, `created`) VALUES (?, ?, 1, ?)");

        $query->execute([$number, $seats, $created]);

        return (int)$this->conn->lastInsertId();
    }

    public function updateTable($tableId, $number, $seats){

        $reservationQuery = $this->conn->prepare("SELECT `reservations`.`id` FROM `reservations` JOIN `reservation_statuses` ON `reservation_statuses`.`id` = `reservations`.`statusId` WHERE `reservations`.`tableId` = ? AND `reservations`.`guests` > ? AND `reservation_statuses`.`code` IN ('new', 'confirmed') AND DATE_ADD(`reservations`.`reservationAt`, INTERVAL `reservations`.`durationMinutes` MINUTE) > NOW() LIMIT 1");

        $reservationQuery->execute([$tableId, $seats]);

        if($reservationQuery->fetch()){

            throw new RuntimeException('Нельзя уменьшить количество мест: на стол есть бронь');
        }

        $query = $this->conn->prepare("UPDATE `cafe_tables` SET `number` = ?, `seats` = ? WHERE `id` = ? AND `deleted` IS NULL");

        $query->execute([$number, $seats, $tableId]);

        return $query->rowCount() > 0;
    }

    public function deleteTable($tableId, $deleted){

        try{

            $this->conn->beginTransaction();

            $tableQuery = $this->conn->prepare("SELECT `id` FROM `cafe_tables` WHERE `id` = ? AND `deleted` IS NULL FOR UPDATE");

            $tableQuery->execute([$tableId]);

            if(!$tableQuery->fetch()){

                throw new RuntimeException('Стол не найден');
            }

            $orderQuery = $this->conn->prepare("SELECT `orders`.`id` FROM `orders` JOIN `order_statuses` ON `order_statuses`.`id` = `orders`.`statusId` WHERE `orders`.`tableId` = ? AND `order_statuses`.`code` NOT IN ('completed', 'cancelled') LIMIT 1");

            $orderQuery->execute([$tableId]);

            if($orderQuery->fetch()){

                throw new RuntimeException('Нельзя убрать стол с активным заказом');
            }

            $reservationQuery = $this->conn->prepare("SELECT `reservations`.`id` FROM `reservations` JOIN `reservation_statuses` ON `reservation_statuses`.`id` = `reservations`.`statusId` WHERE `reservations`.`tableId` = ? AND `reservation_statuses`.`code` IN ('new', 'confirmed') AND DATE_ADD(`reservations`.`reservationAt`, INTERVAL `reservations`.`durationMinutes` MINUTE) > NOW() LIMIT 1");

            $reservationQuery->execute([$tableId]);

            if($reservationQuery->fetch()){

                throw new RuntimeException('Нельзя убрать забронированный стол');
            }

            $deleteQuery = $this->conn->prepare("UPDATE `cafe_tables` SET `active` = 0, `deleted` = ? WHERE `id` = ?");

            $deleteQuery->execute([$deleted, $tableId]);

            $this->conn->commit();

            return true;

        }catch(Throwable $exception){

            if($this->conn->inTransaction()){

                $this->conn->rollBack();
            }

            throw $exception;
        }
    }

    public function getClients(){

        $query = $this->conn->query("SELECT `id`, `name`, `login`, `phone` FROM `users` WHERE `roleId` = 1 AND `deleted` IS NULL ORDER BY `name` ASC");

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function clientExists($userId){

        $query = $this->conn->prepare("SELECT `id` FROM `users` WHERE `id` = ? AND `roleId` = 1 AND `deleted` IS NULL LIMIT 1");

        $query->execute([$userId]);

        return (bool)$query->fetch(PDO::FETCH_ASSOC);
    }

    public function getReservations(){

        $query = $this->conn->query("SELECT `reservations`.`id`, `reservations`.`userId`, `reservations`.`guests`, `reservations`.`reservationAt`, `reservations`.`durationMinutes`, `users`.`name` AS `userName`, `users`.`phone`, `cafe_tables`.`number` AS `tableNumber`, `reservation_statuses`.`code` AS `statusCode`, `reservation_statuses`.`name` AS `statusName` FROM `reservations` JOIN `users` ON `users`.`id` = `reservations`.`userId` JOIN `cafe_tables` ON `cafe_tables`.`id` = `reservations`.`tableId` JOIN `reservation_statuses` ON `reservation_statuses`.`id` = `reservations`.`statusId` WHERE `reservation_statuses`.`code` IN ('new', 'confirmed') AND DATE_ADD(`reservations`.`reservationAt`, INTERVAL `reservations`.`durationMinutes` MINUTE) > NOW() ORDER BY `reservations`.`reservationAt` ASC");

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cancelReservation($reservationId, $updated){

        $query = $this->conn->prepare("UPDATE `reservations` JOIN `reservation_statuses` ON `reservation_statuses`.`id` = `reservations`.`statusId` SET `reservations`.`statusId` = (SELECT `id` FROM `reservation_statuses` AS `cancelStatus` WHERE `cancelStatus`.`code` = 'cancelled' LIMIT 1), `reservations`.`updated` = ? WHERE `reservations`.`id` = ? AND `reservation_statuses`.`code` IN ('new', 'confirmed')");

        $query->execute([$updated, $reservationId]);

        return $query->rowCount() > 0;
    }

    public function getWaiters(){

        $query = $this->conn->query("SELECT `id`, `name`, `login` FROM `users` WHERE `roleId` = 3 AND `deleted` IS NULL ORDER BY `name` ASC");

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveOrders(){

        $query = $this->conn->query("SELECT `orders`.`id`, `orders`.`created`, `orders`.`waiterId`, `cafe_tables`.`number` AS `tableNumber`, `users`.`name` AS `userName`, `waiters`.`name` AS `waiterName`, `order_statuses`.`name` AS `statusName`, COUNT(`order_items`.`id`) AS `itemsCount` FROM `orders` JOIN `cafe_tables` ON `cafe_tables`.`id` = `orders`.`tableId` JOIN `users` ON `users`.`id` = `orders`.`userId` LEFT JOIN `users` AS `waiters` ON `waiters`.`id` = `orders`.`waiterId` JOIN `order_statuses` ON `order_statuses`.`id` = `orders`.`statusId` LEFT JOIN `order_items` ON `order_items`.`orderId` = `orders`.`id` WHERE `order_statuses`.`code` NOT IN ('completed', 'cancelled') GROUP BY `orders`.`id`, `orders`.`created`, `orders`.`waiterId`, `cafe_tables`.`number`, `users`.`name`, `waiters`.`name`, `order_statuses`.`name` ORDER BY `cafe_tables`.`number` ASC");

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function assignWaiter($orderId, $waiterId, $updated){

        $waiterQuery = $this->conn->prepare("SELECT `id` FROM `users` WHERE `id` = ? AND `roleId` = 3 AND `deleted` IS NULL LIMIT 1");

        $waiterQuery->execute([$waiterId]);

        if(!$waiterQuery->fetch()){

            throw new RuntimeException('Официант не найден');
        }

        $query = $this->conn->prepare("UPDATE `orders` JOIN `order_statuses` ON `order_statuses`.`id` = `orders`.`statusId` SET `orders`.`waiterId` = ?, `orders`.`updated` = ? WHERE `orders`.`id` = ? AND `order_statuses`.`code` NOT IN ('completed', 'cancelled')");

        $query->execute([$waiterId, $updated, $orderId]);

        return $query->rowCount() > 0;
    }

    public function getStatistics($from, $to){

        $query = $this->conn->prepare("SELECT (SELECT COUNT(*) FROM `orders` WHERE `created` BETWEEN ? AND ?) AS `totalOrders`, (SELECT COUNT(*) FROM `orders` JOIN `order_statuses` ON `order_statuses`.`id` = `orders`.`statusId` WHERE `order_statuses`.`code` = 'completed' AND `orders`.`created` BETWEEN ? AND ?) AS `completedOrders`, (SELECT COUNT(*) FROM `orders` JOIN `order_statuses` ON `order_statuses`.`id` = `orders`.`statusId` WHERE `order_statuses`.`code` = 'cancelled' AND `orders`.`created` BETWEEN ? AND ?) AS `cancelledOrders`, (SELECT COALESCE(SUM(`order_items`.`quantity`), 0) FROM `order_items` JOIN `orders` ON `orders`.`id` = `order_items`.`orderId` WHERE `orders`.`created` BETWEEN ? AND ?) AS `dishesCount`, (SELECT COUNT(*) FROM `reservations` WHERE `reservationAt` BETWEEN ? AND ?) AS `reservationsCount`, (SELECT COUNT(*) FROM `users` WHERE `roleId` = 1 AND `created` BETWEEN ? AND ?) AS `newClients`");

        $query->execute([$from, $to, $from, $to, $from, $to, $from, $to, $from, $to, $from, $to]);

        return $query->fetch(PDO::FETCH_ASSOC);
    }
}

?>
