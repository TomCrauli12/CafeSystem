<?php

require_once __DIR__ . '/../../database/BD.php';

class OrderRepository{

    private PDO $conn;

    public function __construct(){

        $this->conn = BD::getConnection();
    }

    public function create($userId, $basket, $tableId, $created){

        $orderEnd = date('Y-m-d H:i:s', strtotime($created . ' +120 minutes'));

        try{
            $this->conn->beginTransaction();

            $tableQuery = $this->conn->prepare("SELECT `id` FROM `cafe_tables` WHERE `id` = ? AND `active` = 1 AND `deleted` IS NULL FOR UPDATE");

            $tableQuery->execute([$tableId]);

            if(!$tableQuery->fetch()){

                throw new RuntimeException('Выбранный стол не существует');
            }

            $activeOrderQuery = $this->conn->prepare("SELECT `orders`.`id` FROM `orders` JOIN `order_statuses` ON `order_statuses`.`id` = `orders`.`statusId` WHERE `orders`.`tableId` = ? AND `order_statuses`.`code` NOT IN ('completed', 'cancelled') LIMIT 1");

            $activeOrderQuery->execute([$tableId]);

            if($activeOrderQuery->fetch()){

                throw new RuntimeException('Этот стол уже занят другим заказом');
            }

            $reservationConflictQuery = $this->conn->prepare("SELECT `reservations`.`id` FROM `reservations` JOIN `reservation_statuses` ON `reservation_statuses`.`id` = `reservations`.`statusId` WHERE `reservations`.`tableId` = ? AND `reservations`.`userId` != ? AND `reservation_statuses`.`code` IN ('new', 'confirmed') AND `reservations`.`reservationAt` < ? AND DATE_ADD(`reservations`.`reservationAt`, INTERVAL `reservations`.`durationMinutes` MINUTE) > ? LIMIT 1");
            
            $reservationConflictQuery->execute([$tableId, $userId, $orderEnd, $created]);

            if($reservationConflictQuery->fetch()){

                throw new RuntimeException('Этот стол забронирован другим пользователем');
            }

            $ownReservationQuery = $this->conn->prepare("SELECT `reservations`.`id` FROM `reservations` JOIN `reservation_statuses` ON `reservation_statuses`.`id` = `reservations`.`statusId` WHERE `reservations`.`tableId` = ? AND `reservations`.`userId` = ? AND `reservation_statuses`.`code` IN ('new', 'confirmed') AND `reservations`.`reservationAt` < ? AND DATE_ADD(`reservations`.`reservationAt`, INTERVAL `reservations`.`durationMinutes` MINUTE) > ? ORDER BY `reservations`.`reservationAt` ASC LIMIT 1");
            
            $ownReservationQuery->execute([$tableId, $userId, $orderEnd, $created]);

            $ownReservation = $ownReservationQuery->fetch();
            
            $reservationId = $ownReservation ? (int)$ownReservation['id'] : null;

            $dishIds = array_map('intval', array_keys($basket));

            $placeholders = implode(',', array_fill(0, count($dishIds), '?'));

            $dishQuery = $this->conn->prepare("SELECT `id` FROM `menu` WHERE `id` IN ($placeholders) AND `isStopped` = 0 AND `deleted` IS NULL FOR UPDATE");

            $dishQuery->execute($dishIds);

            $availableDishIds = $dishQuery->fetchAll(PDO::FETCH_COLUMN);

            if(count($availableDishIds)!==count($dishIds)){
                
                throw new RuntimeException('Одно из блюд находится в стоп-листе. Удалите его из корзины');
            }

            $orderQuery = $this->conn->prepare("INSERT INTO `orders` (`userId`, `tableId`, `reservationId`, `statusId`, `created`) VALUES (?, ?, ?, 1, ?)");
            
            $orderQuery->execute([$userId, $tableId, $reservationId, $created]);

            $orderId = (int)$this->conn->lastInsertId();

            $itemQuery = $this->conn->prepare("INSERT INTO `order_items` (`orderId`, `dishId`, `quantity`, `comment`, `statusId`) VALUES (?, ?, ?, ?, 1)");

            foreach($basket as $dishId=>$item){

                $itemQuery->execute([$orderId, $dishId, $item['quantity'], $item['comment']]);
            }

            $this->conn->commit();

            return $orderId;

        }catch(Throwable $exception){

            if($this->conn->inTransaction()){
                
                $this->conn->rollBack();
            }

            throw $exception;
        }
    }

    public function getAllForCook(){
        
        $query = $this->conn->query("SELECT `orders`.`id` AS `orderId`, `orders`.`created`, `users`.`name` AS `userName`, `cafe_tables`.`number` AS `tableNumber`, `order_items`.`id` AS `orderItemId`, `order_items`.`quantity`, `order_items`.`comment`, `menu`.`id` AS `dishId`, `menu`.`name` AS `dishName`, `menu`.`photo`, `menu`.`cooktime`, `categories`.`name` AS `categoryName`, `order_statuses`.`code` AS `statusCode`, `order_statuses`.`name` AS `statusName` FROM `orders` JOIN `users` ON `users`.`id` = `orders`.`userId` LEFT JOIN `cafe_tables` ON `cafe_tables`.`id` = `orders`.`tableId` JOIN `order_items` ON `order_items`.`orderId` = `orders`.`id` JOIN `menu` ON `menu`.`id` = `order_items`.`dishId` JOIN `categories` ON `categories`.`id` = `menu`.`categoryId` JOIN `order_statuses` ON `order_statuses`.`id` = `order_items`.`statusId` WHERE `order_statuses`.`code` IN ('new', 'cooking', 'ready') ORDER BY FIELD(`order_statuses`.`code`, 'cooking', 'new', 'ready'), `orders`.`created` ASC, `order_items`.`id` ASC");
        
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateItemStatus($orderItemId, $nextStatusCode){

        $allowedTransitions = ['new'=>'cooking', 'cooking'=>'ready'];

        try{

            $this->conn->beginTransaction();

            $itemQuery = $this->conn->prepare("SELECT `order_items`.`orderId`, `order_statuses`.`code` AS `currentStatusCode` FROM `order_items` JOIN `order_statuses` ON `order_statuses`.`id` = `order_items`.`statusId` WHERE `order_items`.`id` = ? FOR UPDATE");

            $itemQuery->execute([$orderItemId]);

            $item = $itemQuery->fetch(PDO::FETCH_ASSOC);

            if(!$item || !isset($allowedTransitions[$item['currentStatusCode']]) || $allowedTransitions[$item['currentStatusCode']]!==$nextStatusCode){

                $this->conn->rollBack();
                
                return false;
            }

            $updateQuery = $this->conn->prepare("UPDATE `order_items` SET `statusId` = (SELECT `id` FROM `order_statuses` WHERE `code` = ? LIMIT 1), `updated` = NOW() WHERE `id` = ?");

            $updateQuery->execute([$nextStatusCode, $orderItemId]);

            $this->syncOrderStatus((int)$item['orderId']);

            $this->conn->commit();

            return true;

        }catch(Throwable $exception){

            if($this->conn->inTransaction()){

                $this->conn->rollBack();
            }

            throw $exception;
        }
    }

    public function getByUserId($userId){

        $query = $this->conn->prepare("SELECT `orders`.`id` AS `orderId`, `orders`.`created` AS `orderCreated`, `cafe_tables`.`number` AS `tableNumber`, `order_items`.`id` AS `orderItemId`, `order_items`.`quantity`, `order_items`.`comment`, `menu`.`name` AS `dishName`, `menu`.`photo`, `menu`.`cooktime`, `order_statuses`.`code` AS `statusCode`, `order_statuses`.`name` AS `statusName`, CASE WHEN `order_statuses`.`code` = 'new' AND TIMESTAMPDIFF(SECOND, `orders`.`created`, NOW()) BETWEEN 0 AND 180 THEN 1 ELSE 0 END AS `canCancel` FROM `orders` LEFT JOIN `cafe_tables` ON `cafe_tables`.`id` = `orders`.`tableId` JOIN `order_items` ON `order_items`.`orderId` = `orders`.`id` JOIN `menu` ON `menu`.`id` = `order_items`.`dishId` JOIN `order_statuses` ON `order_statuses`.`id` = `order_items`.`statusId` WHERE `orders`.`userId` = ? ORDER BY `orders`.`created` DESC, `order_items`.`id` ASC");
        
        $query->execute([$userId]);

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cancelItem($orderItemId, $userId){

        try{

            $this->conn->beginTransaction();

            $itemQuery = $this->conn->prepare("SELECT `order_items`.`orderId` FROM `order_items` JOIN `orders` ON `orders`.`id` = `order_items`.`orderId` JOIN `order_statuses` ON `order_statuses`.`id` = `order_items`.`statusId` WHERE `order_items`.`id` = ? AND `orders`.`userId` = ? AND `order_statuses`.`code` = 'new' AND TIMESTAMPDIFF(SECOND, `orders`.`created`, NOW()) BETWEEN 0 AND 180 FOR UPDATE");

            $itemQuery->execute([$orderItemId, $userId]);

            $item = $itemQuery->fetch(PDO::FETCH_ASSOC);

            if(!$item){

                $this->conn->rollBack();

                return false;
            }

            $updateQuery = $this->conn->prepare("UPDATE `order_items` SET `statusId` = (SELECT `id` FROM `order_statuses` WHERE `code` = 'cancelled' LIMIT 1), `updated` = NOW() WHERE `id` = ?");

            $updateQuery->execute([$orderItemId]);

            $this->syncOrderStatus((int)$item['orderId']);

            $this->conn->commit();

            return true;

        }catch(Throwable $exception){

            if($this->conn->inTransaction()){

                $this->conn->rollBack();
            }

            throw $exception;
        }
    }

    private function syncOrderStatus($orderId){

        $query = $this->conn->prepare("SELECT COUNT(*) AS `total`, SUM(`order_statuses`.`code` = 'cancelled') AS `cancelled`, SUM(`order_statuses`.`code` = 'ready') AS `ready`, SUM(`order_statuses`.`code` IN ('cooking', 'ready')) AS `started` FROM `order_items` JOIN `order_statuses` ON `order_statuses`.`id` = `order_items`.`statusId` WHERE `order_items`.`orderId` = ?");
        
        $query->execute([$orderId]);

        $statuses = $query->fetch(PDO::FETCH_ASSOC);

        $total = (int)$statuses['total'];

        $cancelled = (int)$statuses['cancelled'];

        $ready = (int)$statuses['ready'];

        $started = (int)$statuses['started'];

        if($total===0){

            return;
        }

        if($cancelled===$total){

            $statusCode = 'cancelled';

        }elseif($ready+$cancelled===$total){

            $statusCode = 'ready';

        }elseif($started>0){

            $statusCode = 'cooking';

        }else{

            $statusCode = 'new';
        }

        $updateQuery = $this->conn->prepare("UPDATE `orders` SET `statusId` = (SELECT `id` FROM `order_statuses` WHERE `code` = ? LIMIT 1), `updated` = NOW() WHERE `id` = ?");

        $updateQuery->execute([$statusCode, $orderId]);
    }
    

    
}



?>