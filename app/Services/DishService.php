<?php

require_once __DIR__ . '/../Repositories/DishRepository.php';

class Dish{

    static function create($name, $photo, $description, $structure, $cooktime, $categoryId, $created){

        $dishRepository = new DishRespositiry();

        return $dishRepository->create($name, $photo, $description, $structure, $cooktime, $categoryId, $created);
    }

    static function getAll(){

        $dishRepository = new DishRespositiry();

        return $dishRepository->getAll();
    }

    static function getById($id){

        $dishRepository = new DishRespositiry();

        return $dishRepository->getById($id);
    }

    static function update($id, $name, $photo, $description, $structure, $cooktime, $categoryId){
        
        $dishRepository = new DishRespositiry();

        return $dishRepository->update($id, $name, $photo, $description, $structure, $cooktime, $categoryId);
    }

    static function getAllWithCategories(){

        $dishRepository = new DishRespositiry();

        return $dishRepository->getAllWithCategories();
    }

    static function getAvailableWithCategories(){

        $dishRepository = new DishRespositiry();

        return $dishRepository->getAvailableWithCategories();
    }

    static function searchAvailable($search){

        $dishRepository = new DishRespositiry();

        return $dishRepository->searchAvailable($search);
    }

    static function updateStopList($dishId, $isStopped){

        $dishRepository = new DishRespositiry();

        return $dishRepository->updateStopList($dishId, $isStopped);
    }

    static function isAvailable($dishId){

        $dishRepository = new DishRespositiry();

        return $dishRepository->isAvailable($dishId);
    }


    
}
