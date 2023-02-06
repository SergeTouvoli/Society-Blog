<?php

require "config.php";

abstract class Model {
    
    private static $pdo;

    private static function setDbConnexion() {
        self::$pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS);
    }

    protected function getDbConnexion(){
        if(self::$pdo === null){
            self::setDbConnexion();
        }
        return self::$pdo;
    }
    
}

