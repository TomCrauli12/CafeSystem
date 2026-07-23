<?php

require_once __DIR__ . '/../Repositories/OrderRepository.php';

class OrderService{

    static function create($userId, $basket, $tableId, $created){
        
        $orderRepository = new OrderRepository();

        return $orderRepository->create($userId, $basket, $tableId, $created);
    }
    
    static function getByUserId($userId){
        
        $orderRepository = new OrderRepository();

        return $orderRepository->getByUserId($userId);
    }

    static function cancelItem($orderItemId, $userId){

        $orderRepository = new OrderRepository();

        return $orderRepository->cancelItem($orderItemId, $userId);
    }

    static function getAllForCook(){

        $orderRepository = new OrderRepository();

        return $orderRepository->getAllForCook();
    }

    static function getForCook($status='all'){

        $orderItems = self::getAllForCook();

        $orders = [];

        foreach($orderItems as $item){

            if($status!=='all' && $item['statusCode']!==$status){

                continue;
            }

            $orderId = (int)$item['orderId'];

            if(!isset($orders[$orderId])){

                $orders[$orderId] = ['created'=>$item['created'], 'tableNumber'=>$item['tableNumber'], 'userName'=>$item['userName'], 'items'=>[]];
            }

            $orders[$orderId]['items'][] = $item;
        }

        return $orders;
    }

    static function updateItemStatus($orderItemId, $nextStatusCode){

        $orderRepository = new OrderRepository();
        
        return $orderRepository->updateItemStatus($orderItemId, $nextStatusCode);
    }


}

?>
