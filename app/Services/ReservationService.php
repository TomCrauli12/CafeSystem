<?php

require_once __DIR__ . '/../Repositories/ReservationRepository.php';

class ReservationService{

    static function create($userId, $tableId, $reservationAt, $durationMinutes, $guests, $created){

        $reservationRepository = new ReservationRepository();

        return $reservationRepository->create($userId, $tableId, $reservationAt, $durationMinutes, $guests, $created);
    }

    static function getByUserId($userId, $currentDateTime){

        $reservationRepository = new ReservationRepository();

        return $reservationRepository->getByUserId($userId, $currentDateTime);
    }

    static function cancel($reservationId, $userId, $currentDateTime){
        
        $reservationRepository = new ReservationRepository();

        return $reservationRepository->cancel($reservationId, $userId, $currentDateTime);
    }
}

?>