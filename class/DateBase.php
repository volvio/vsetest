<?php
class DateBase {

    private static $instance = null;
    private static $db = null;

    public static function getDB() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance->db;
    }

    private function __clone() {
        
    }

    private function __construct() {
        $host = 'localhost';
        $db = 'vse';
        $user = 'vse';
        $pass = 'vse';
        $charset = 'utf8';
        $opt = array();

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        try {
            $this->db = new PDO($dsn, $user, $pass, $opt);
        } catch (PDOException $e) {
            die('Unable  connect to DB: ' . $e->getMessage());
        }
    }

}
