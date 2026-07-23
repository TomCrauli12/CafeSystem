<?php

require_once __DIR__ . '/../sessions/Sessions.php';

class BasketService{

    static function getAll(){

        session::start();

        return $_SESSION['basket'] ?? [];
    }

    static function getCount(){

        $basket = self::getAll();

        return array_sum(array_column($basket, 'quantity'));
    }

    static function add($dishId){
        
    session::start();

    if(isset($_SESSION['basket'][$dishId])){

        $_SESSION['basket'][$dishId]['quantity']++;

        return;
    }

    $_SESSION['basket'][$dishId] = ['quantity'=>1, 'comment'=>''];
    }

    static function update($dishId, $quantity, $comment){

        session::start();

        if(!isset($_SESSION['basket'][$dishId])){

            return;
        }

        $_SESSION['basket'][$dishId]['quantity'] = $quantity;

        $_SESSION['basket'][$dishId]['comment'] = $comment;
    }

    static function remove($dishId){

        session::start();

        unset($_SESSION['basket'][$dishId]);
    }

    static function clear(){

        session::start();

        unset($_SESSION['basket']);
    }
}




?>
