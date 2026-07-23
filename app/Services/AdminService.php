<?php

require_once __DIR__ . '/../Repositories/AdminRepository.php';

class AdminService{

    static function getRoles(){

        $adminRepository = new AdminRepository();

        return $adminRepository->getRoles();
    }

    static function getUsers(){

        $adminRepository = new AdminRepository();

        return $adminRepository->getUsers();
    }

    static function createUser($login, $name, $phone, $enterPassword, $roleId, $created){

        $adminRepository = new AdminRepository();

        if(!$adminRepository->roleExists($roleId)){

            throw new RuntimeException('Роль не найдена');
        }

        if($adminRepository->loginExists($login)){

            throw new RuntimeException('Пользователь с таким логином уже существует');
        }

        $password = password_hash($enterPassword, PASSWORD_DEFAULT);

        return $adminRepository->createUser($login, $name, $phone, $password, $roleId, $created);
    }

    static function updateUserRole($userId, $roleId, $adminId){

        $adminRepository = new AdminRepository();

        if(!$adminRepository->roleExists($roleId)){

            throw new RuntimeException('Роль не найдена');
        }

        if($userId===$adminId && $roleId!==5){

            throw new RuntimeException('Нельзя изменить роль своего аккаунта');
        }

        return $adminRepository->updateUserRole($userId, $roleId);
    }
}

?>
