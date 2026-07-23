<?php

    class session{

        public static function start(): void{

            if (session_status() === PHP_SESSION_NONE) {

                $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

                session_set_cookie_params(['lifetime'=>0, 'path'=>'/', 'secure'=>$secure, 'httponly'=>true, 'samesite'=>'Lax']);
                
                session_start();
            }
        }

        public static function has(string $key): bool{

            self::start();

            return isset($_SESSION[$key]);

        }

        public static function get(string $key, $default=null){
            
            self::start();

            return $_SESSION[$key] ?? $default;
        }
        
        public static function destroy(): void{

            self::start();

            $_SESSION = [];

            session_destroy();
        }

        public static function indefication(array $user):void{

            self::start();

            session_regenerate_id(true);

            $_SESSION['user_id'] = (int)$user['id'];

            $_SESSION['login'] = $user['login'];

            $_SESSION['name'] = $user['name'];

            $_SESSION['role_id'] = (int)($user['roleId'] ?? 0);
        }
    }





?>