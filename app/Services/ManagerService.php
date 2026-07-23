<?php

require_once __DIR__ . '/../Repositories/ManagerRepository.php';
require_once __DIR__ . '/../Repositories/ReservationRepository.php';

class ManagerService{

    static function getTables(){

        $managerRepository = new ManagerRepository();

        return $managerRepository->getTables();
    }

    static function createTable($number, $seats, $created){

        $managerRepository = new ManagerRepository();

        return $managerRepository->createTable($number, $seats, $created);
    }

    static function updateTable($tableId, $number, $seats){

        $managerRepository = new ManagerRepository();

        return $managerRepository->updateTable($tableId, $number, $seats);
    }

    static function deleteTable($tableId, $deleted){

        $managerRepository = new ManagerRepository();

        return $managerRepository->deleteTable($tableId, $deleted);
    }

    static function getClients(){

        $managerRepository = new ManagerRepository();

        return $managerRepository->getClients();
    }

    static function createReservation($userId, $tableId, $reservationAt, $durationMinutes, $guests, $created){

        $managerRepository = new ManagerRepository();

        if(!$managerRepository->clientExists($userId)){

            throw new RuntimeException('Клиент не найден');
        }

        $reservationRepository = new ReservationRepository();

        return $reservationRepository->create($userId, $tableId, $reservationAt, $durationMinutes, $guests, $created);
    }

    static function getReservations(){

        $managerRepository = new ManagerRepository();

        return $managerRepository->getReservations();
    }

    static function cancelReservation($reservationId, $updated){

        $managerRepository = new ManagerRepository();

        return $managerRepository->cancelReservation($reservationId, $updated);
    }

    static function getWaiters(){

        $managerRepository = new ManagerRepository();

        return $managerRepository->getWaiters();
    }

    static function getActiveOrders(){

        $managerRepository = new ManagerRepository();

        return $managerRepository->getActiveOrders();
    }

    static function assignWaiter($orderId, $waiterId, $updated){

        $managerRepository = new ManagerRepository();

        return $managerRepository->assignWaiter($orderId, $waiterId, $updated);
    }

    static function getStatistics($from, $to){

        $managerRepository = new ManagerRepository();

        return $managerRepository->getStatistics($from, $to);
    }
}

?>
