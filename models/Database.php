<?php
// models/Database.php

class Database
{
    private $host = "MySQL-8.0";
    private $db_name = "phpDB";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection()
    {
        $this->conn = null;
        try {
            // Устанавливаем кодировку
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Ошибка подключения: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
