<?php

require_once __DIR__ . '/../Repositories/DishRepository.php';

class Category{

    static function create($name, $created){

        $categoryRepository = new CategoryRespositiry();

        return $categoryRepository->create($name, $created);
    }

    static function getAll(){

        $categoryRepository = new CategoryRespositiry();

        return $categoryRepository->getAll();
    }

    static function getById($id){

        $categoryRepository = new CategoryRespositiry();

        return $categoryRepository->getById($id);
    }

    static function update($id, $name){
        
        $categoryRepository = new CategoryRespositiry();

        return $categoryRepository->update($id, $name);
    }
}