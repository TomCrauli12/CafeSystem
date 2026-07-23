<?php

    require_once __DIR__ . '/../vendor/autoload.php';


    class BD{

        private static bool $envLoaded = false;

        private static function loadEnv():void{

            if(self::$envLoaded){

                return;
            }

            $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));

            $dotenv->load();

            $dotenv->required(['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD']);

            self::$envLoaded = true;
        }

        public static function getConnection(){

            self::loadEnv();

            $host = $_ENV['DB_HOST'];

            $port = $_ENV['DB_PORT'];

            $dbname = $_ENV['DB_NAME'];

            $user = $_ENV['DB_USER'];

            $password = $_ENV['DB_PASSWORD'];

            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

            $conn = new PDO($dsn, $user, $password);

            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            $conn->exec("SET time_zone = '+03:00'");

            return $conn;
        }

    }

?>