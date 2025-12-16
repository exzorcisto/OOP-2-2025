<?php
// models/Application.php

class Application
{
    private $conn;
    private $table = "applications";

    // Свойства
    public $id;
    public $user_id;
    public $course_id;
    public $status = 'pending'; // Значение по умолчанию

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Создание новой заявки
    public function create()
    {
        $query = "INSERT INTO " . $this->table . " SET user_id=:user_id, course_id=:course_id, status=:status";
        $stmt = $this->conn->prepare($query);

        // Привязка параметров
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":course_id", $this->course_id);
        $stmt->bindParam(":status", $this->status);

        return $stmt->execute();
    }

    // Проверка активной заявки (для предотвращения дубликатов)
    public function checkActiveApplication()
    {
        $query = "SELECT id FROM " . $this->table . " 
                  WHERE user_id = :user_id AND course_id = :course_id AND status IN ('pending', 'approved')";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":course_id", $this->course_id);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Получение всех заявок для пользователя (для profile.php)
    public function getApplicationsByUserId()
    {
        $query = "SELECT a.id, c.title as course_title, a.status, a.created_at
                  FROM " . $this->table . " a
                  JOIN courses c ON a.course_id = c.id
                  WHERE a.user_id = :user_id
                  ORDER BY a.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->execute();

        return $stmt;
    }

    // Получение всех заявок с деталями (для admin/index.php)
    public function getAllApplicationsWithDetails()
    {
        $query = "SELECT 
                    a.id, 
                    u.fio_user, 
                    c.title AS course_title, 
                    a.status,
                    a.created_at 
                  FROM " . $this->table . " a 
                  JOIN users u ON a.user_id = u.id 
                  JOIN courses c ON a.course_id = c.id
                  ORDER BY a.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Обновление статуса заявки (для admin/index.php)
    public function updateStatus($new_status)
    {
        $query = "UPDATE " . $this->table . " SET status = :new_status WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":new_status", $new_status);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }
}
