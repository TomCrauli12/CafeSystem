<?php

require_once __DIR__ . '/../Repositories/HistoryRepository.php';
require_once __DIR__ . '/../sessions/Sessions.php';

class HistoryService{

    static function log($action, $entityType, $entityId, $description, $details=[], $userId=null, $roleId=null){

        session::start();

        if($userId===null && session::has('user_id')){

            $userId = (int)session::get('user_id');
        }

        if($roleId===null && session::has('role_id')){

            $roleId = (int)session::get('role_id');
        }

        $detailsJson = empty($details) ? null : json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;

        date_default_timezone_set('Europe/Moscow');

        try{

            $historyRepository = new HistoryRepository();

            return $historyRepository->create($userId, $roleId, $action, $entityType, $entityId, $description, $detailsJson, $ipAddress, date('Y-m-d H:i:s'));

        }catch(Throwable $exception){

            error_log('History error: ' . $exception->getMessage());

            return 0;
        }
    }

    static function getAll(){

        $historyRepository = new HistoryRepository();

        return $historyRepository->getAll();
    }
}

?>
