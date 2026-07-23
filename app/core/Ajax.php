<?php

class Ajax{

    public static function isRequest(): bool{

        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')==='xmlhttprequest';
    }

    public static function success(array $data=[], string $message=''): void{

        self::response(true, $data, $message, 200);
    }

    public static function error(string $message, int $status=422, array $data=[]): void{

        self::response(false, $data, $message, $status);
    }

    private static function response(bool $success, array $data, string $message, int $status): void{

        http_response_code($status);

        header('Content-Type: application/json; charset=UTF-8');

        echo json_encode(['success'=>$success, 'message'=>$message, 'data'=>$data], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        exit;
    }
}

?>
