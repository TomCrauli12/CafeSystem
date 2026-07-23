<?php

require_once __DIR__ . '/../../database/BD.php';

class WaiterRepository{

    private PDO $conn;

    public function __construct(){

        $this->conn = BD::getConnection();
    }

    public function getTables(){

        $query = $this->conn->query("SELECT `cafe_tables`.`id`, `cafe_tables`.`number`, `cafe_tables`.`seats`, `orders`.`id` AS `orderId`, `orders`.`waiterId`, `users`.`name` AS `userName`, `waiters`.`name` AS `waiterName`, `order_statuses`.`code` AS `statusCode`, `order_statuses`.`name` AS `statusName`, COUNT(`order_items`.`id`) AS `itemsCount`, SUM(CASE WHEN `item_statuses`.`code` = 'ready' THEN 1 ELSE 0 END) AS `readyCount` FROM `cafe_tables` LEFT JOIN `orders` ON `orders`.`tableId` = `cafe_tables`.`id` AND `orders`.`statusId` IN (SELECT `id` FROM `order_statuses` WHERE `code` NOT IN ('completed', 'cancelled')) LEFT JOIN `users` ON `users`.`id` = `orders`.`userId` LEFT JOIN `users` AS `waiters` ON `waiters`.`id` = `orders`.`waiterId` LEFT JOIN `order_statuses` ON `order_statuses`.`id` = `orders`.`statusId` LEFT JOIN `order_items` ON `order_items`.`orderId` = `orders`.`id` LEFT JOIN `order_statuses` AS `item_statuses` ON `item_statuses`.`id` = `order_items`.`statusId` WHERE `cafe_tables`.`active` = 1 AND `cafe_tables`.`deleted` IS NULL GROUP BY `cafe_tables`.`id`, `cafe_tables`.`number`, `cafe_tables`.`seats`, `orders`.`id`, `orders`.`waiterId`, `users`.`name`, `waiters`.`name`, `order_statuses`.`code`, `order_statuses`.`name` ORDER BY `cafe_tables`.`number` ASC");

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function startService($tableId, $waiterId, $created){

        try{

            $this->conn->beginTransaction();

            $tableQuery = $this->conn->prepare("SELECT `id` FROM `cafe_tables` WHERE `id` = ? AND `active` = 1 AND `deleted` IS NULL FOR UPDATE");

            $tableQuery->execute([$tableId]);

            if(!$tableQuery->fetch()){

                throw new RuntimeException('Стол не найден');
            }

            $orderQuery = $this->conn->prepare("SELECT `orders`.`id`, `orders`.`waiterId` FROM `orders` JOIN `order_statuses` ON `order_statuses`.`id` = `orders`.`statusId` WHERE `orders`.`tableId` = ? AND `order_statuses`.`code` NOT IN ('completed', 'cancelled') LIMIT 1 FOR UPDATE");

            $orderQuery->execute([$tableId]);

            $order = $orderQuery->fetch(PDO::FETCH_ASSOC);

            if($order){

                if($order['waiterId']!==null && (int)$order['waiterId']!==$waiterId){

                    throw new RuntimeException('Этот стол уже обслуживает другой официант');
                }

                if($order['waiterId']===null){

                    $updateQuery = $this->conn->prepare("UPDATE `orders` SET `waiterId` = ?, `updated` = ? WHERE `id` = ?");

                    $updateQuery->execute([$waiterId, $created, $order['id']]);
                }

                $this->conn->commit();

                return (int)$order['id'];
            }

            $createQuery = $this->conn->prepare("INSERT INTO `orders` (`userId`, `tableId`, `waiterId`, `statusId`, `created`) VALUES (?, ?, ?, (SELECT `id` FROM `order_statuses` WHERE `code` = 'new' LIMIT 1), ?)");

            $createQuery->execute([$waiterId, $tableId, $waiterId, $created]);

            $orderId = (int)$this->conn->lastInsertId();

            $this->conn->commit();

            return $orderId;

        }catch(Throwable $exception){

            if($this->conn->inTransaction()){

                $this->conn->rollBack();
            }

            throw $exception;
        }
    }

    public function getOrderByTableId($tableId){

        $query = $this->conn->prepare("SELECT `orders`.`id`, `orders`.`waiterId`, `orders`.`created`, `users`.`name` AS `userName`, `waiters`.`name` AS `waiterName`, `order_statuses`.`code` AS `statusCode`, `order_statuses`.`name` AS `statusName` FROM `orders` JOIN `users` ON `users`.`id` = `orders`.`userId` LEFT JOIN `users` AS `waiters` ON `waiters`.`id` = `orders`.`waiterId` JOIN `order_statuses` ON `order_statuses`.`id` = `orders`.`statusId` WHERE `orders`.`tableId` = ? AND `order_statuses`.`code` NOT IN ('completed', 'cancelled') LIMIT 1");

        $query->execute([$tableId]);

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function getOrderItems($orderId){

        $query = $this->conn->prepare("SELECT `order_items`.`id`, `order_items`.`quantity`, `order_items`.`comment`, `menu`.`name` AS `dishName`, `menu`.`photo`, `order_statuses`.`code` AS `statusCode`, `order_statuses`.`name` AS `statusName` FROM `order_items` JOIN `menu` ON `menu`.`id` = `order_items`.`dishId` JOIN `order_statuses` ON `order_statuses`.`id` = `order_items`.`statusId` WHERE `order_items`.`orderId` = ? ORDER BY `order_items`.`id` ASC");

        $query->execute([$orderId]);

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAvailableDishes(){

        $query = $this->conn->query("SELECT `menu`.*, `categories`.`name` AS `categoryName` FROM `menu` JOIN `categories` ON `categories`.`id` = `menu`.`categoryId` WHERE `menu`.`isStopped` = 0 AND `menu`.`deleted` IS NULL ORDER BY `categories`.`name`, `menu`.`name`");

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addDish($orderId, $waiterId, $dishId, $quantity, $comment){

        try{

            $this->conn->beginTransaction();

            $orderQuery = $this->conn->prepare("SELECT `orders`.`id` FROM `orders` JOIN `order_statuses` ON `order_statuses`.`id` = `orders`.`statusId` WHERE `orders`.`id` = ? AND `orders`.`waiterId` = ? AND `order_statuses`.`code` NOT IN ('completed', 'cancelled') FOR UPDATE");

            $orderQuery->execute([$orderId, $waiterId]);

            if(!$orderQuery->fetch()){

                throw new RuntimeException('Заказ не найден или назначен другому официанту');
            }

            $dishQuery = $this->conn->prepare("SELECT `id` FROM `menu` WHERE `id` = ? AND `isStopped` = 0 AND `deleted` IS NULL FOR UPDATE");

            $dishQuery->execute([$dishId]);

            if(!$dishQuery->fetch()){

                throw new RuntimeException('Блюдо недоступно или находится в стоп-листе');
            }

            $itemQuery = $this->conn->prepare("INSERT INTO `order_items` (`orderId`, `dishId`, `quantity`, `comment`, `statusId`) VALUES (?, ?, ?, ?, (SELECT `id` FROM `order_statuses` WHERE `code` = 'new' LIMIT 1))");

            $itemQuery->execute([$orderId, $dishId, $quantity, $comment]);

            $updateOrderQuery = $this->conn->prepare("UPDATE `orders` SET `statusId` = (SELECT `id` FROM `order_statuses` WHERE `code` = 'new' LIMIT 1), `updated` = NOW() WHERE `id` = ?");

            $updateOrderQuery->execute([$orderId]);

            $this->conn->commit();

            return (int)$this->conn->lastInsertId();

        }catch(Throwable $exception){

            if($this->conn->inTransaction()){

                $this->conn->rollBack();
            }

            throw $exception;
        }
    }

    public function completeOrder($orderId, $waiterId){

        try{

            $this->conn->beginTransaction();

            $orderQuery = $this->conn->prepare("SELECT `orders`.`id` FROM `orders` JOIN `order_statuses` ON `order_statuses`.`id` = `orders`.`statusId` WHERE `orders`.`id` = ? AND `orders`.`waiterId` = ? AND `order_statuses`.`code` NOT IN ('completed', 'cancelled') FOR UPDATE");

            $orderQuery->execute([$orderId, $waiterId]);

            if(!$orderQuery->fetch()){

                throw new RuntimeException('Заказ не найден или назначен другому официанту');
            }

            $itemsQuery = $this->conn->prepare("SELECT COUNT(*) AS `total`, SUM(`order_statuses`.`code` IN ('new', 'cooking')) AS `notReady` FROM `order_items` JOIN `order_statuses` ON `order_statuses`.`id` = `order_items`.`statusId` WHERE `order_items`.`orderId` = ? AND `order_statuses`.`code` != 'cancelled'");

            $itemsQuery->execute([$orderId]);

            $items = $itemsQuery->fetch(PDO::FETCH_ASSOC);

            if((int)$items['total']===0){

                throw new RuntimeException('В заказе ещё нет блюд');
            }

            if((int)$items['notReady']>0){

                throw new RuntimeException('Нельзя завершить обслуживание, пока не все блюда готовы');
            }

            $itemQuery = $this->conn->prepare("UPDATE `order_items` SET `statusId` = (SELECT `id` FROM `order_statuses` WHERE `code` = 'completed' LIMIT 1), `updated` = NOW() WHERE `orderId` = ? AND `statusId` = (SELECT `id` FROM `order_statuses` WHERE `code` = 'ready' LIMIT 1)");

            $itemQuery->execute([$orderId]);

            $updateQuery = $this->conn->prepare("UPDATE `orders` SET `statusId` = (SELECT `id` FROM `order_statuses` WHERE `code` = 'completed' LIMIT 1), `updated` = NOW() WHERE `id` = ?");

            $updateQuery->execute([$orderId]);

            $this->conn->commit();

            return true;

        }catch(Throwable $exception){

            if($this->conn->inTransaction()){

                $this->conn->rollBack();
            }

            throw $exception;
        }
    }
}

?>
