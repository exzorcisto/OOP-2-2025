<?php
// models/Course.php

class Course
{
    private $conn;
    private $table = "courses";

    // Свойства
    public $id;
    public $title;
    public $description;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Получение всех курсов
    public function readAll()
    {
        $query = "SELECT id, title, description FROM " . $this->table . " ORDER BY title ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt; // Возвращает PDOStatement
    }
}
