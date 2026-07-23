<?php

require_once __DIR__ . '/../Repositories/WaiterRepository.php';

class WaiterService{

    static function getTables(){

        $waiterRepository = new WaiterRepository();

        return $waiterRepository->getTables();
    }

    static function startService($tableId, $waiterId, $created){

        $waiterRepository = new WaiterRepository();

        return $waiterRepository->startService($tableId, $waiterId, $created);
    }

    static function getOrderByTableId($tableId){

        $waiterRepository = new WaiterRepository();

        return $waiterRepository->getOrderByTableId($tableId);
    }

    static function getOrderItems($orderId){

        $waiterRepository = new WaiterRepository();

        return $waiterRepository->getOrderItems($orderId);
    }

    static function getAvailableDishes(){

        $waiterRepository = new WaiterRepository();

        return $waiterRepository->getAvailableDishes();
    }

    static function addDish($orderId, $waiterId, $dishId, $quantity, $comment){

        $waiterRepository = new WaiterRepository();

        return $waiterRepository->addDish($orderId, $waiterId, $dishId, $quantity, $comment);
    }

    static function completeOrder($orderId, $waiterId){

        $waiterRepository = new WaiterRepository();

        return $waiterRepository->completeOrder($orderId, $waiterId);
    }
}

?>
