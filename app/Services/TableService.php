<?php

require_once __DIR__ . '/../Repositories/TableRepository.php';

class TableService{
    
    static function getAvailableForReservation($reservationAt, $durationMinutes, $guests){

        $tableRepository = new TableRepository();

        $reservationEnd = date('Y-m-d H:i:s', strtotime($reservationAt . " +$durationMinutes minutes"));

        return $tableRepository->getAvailableForReservation($reservationAt, $reservationEnd, $guests);
    }

    static function getAvailableForOrder($userId, $orderAt){

        $tableRepository = new TableRepository();

        $orderEnd = date('Y-m-d H:i:s', strtotime($orderAt . ' +120 minutes'));

        return $tableRepository->getAvailableForOrder($userId, $orderAt, $orderEnd);
    }
}

?>