<?php

class Validator{

    public static function text($value, $field, $required=true){

        if(!is_string($value)){

            throw new RuntimeException($field . ': неверное значение');
        }

        $value = trim($value);

        if($value==='' && !$required){

            return '';
        }

        $length = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);

        if($length<2 || $length>20){

            throw new RuntimeException($field . ': от 2 до 20 символов');
        }

        return $value;
    }

    public static function integer($value, $field, $min=1, $max=2147483647){

        if(is_array($value) || filter_var($value, FILTER_VALIDATE_INT)===false){

            throw new RuntimeException($field . ': укажите целое число');
        }

        $value = (int)$value;

        if($value<$min || $value>$max){

            throw new RuntimeException($field . ': значение от ' . $min . ' до ' . $max);
        }

        return $value;
    }

    public static function choice($value, $field, $values){

        if(!is_string($value) || !in_array($value, $values, true)){

            throw new RuntimeException($field . ': неверное значение');
        }

        return $value;
    }

    public static function dateTime($date, $time){

        if(!is_string($date) || !is_string($time)){

            throw new RuntimeException('Проверьте дату и время');
        }

        $reservationAt = trim($date) . ' ' . trim($time) . ':00';

        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $reservationAt);

        if(!$dateTime || $dateTime->format('Y-m-d H:i:s')!==$reservationAt || $dateTime<=new DateTime()){

            throw new RuntimeException('Дата и время должны быть в будущем');
        }

        return $reservationAt;
    }

    public static function image($file, $required=true){

        if(!is_array($file) || !isset($file['error'])){

            if(!$required){

                return null;
            }

            throw new RuntimeException('Выберите изображение');
        }

        if((int)$file['error']===UPLOAD_ERR_NO_FILE && !$required){

            return null;
        }

        if((int)$file['error']!==UPLOAD_ERR_OK){

            throw new RuntimeException('Не удалось загрузить изображение');
        }

        if((int)($file['size'] ?? 0)>5*1024*1024){

            throw new RuntimeException('Размер изображения не должен превышать 5 МБ');
        }

        $imageInfo = @getimagesize($file['tmp_name'] ?? '');

        if(!$imageInfo || !in_array($imageInfo['mime'] ?? '', ['image/jpeg', 'image/png', 'image/webp'], true)){

            throw new RuntimeException('Файл не является изображением');
        }

        $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));

        if(!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)){

            throw new RuntimeException('Доступны только JPG, JPEG, PNG и WEBP');
        }

        return $extension;
    }
}

?>
