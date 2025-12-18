<?php
// models/Application.php

class Application
{
    private $conn;
    private $table = "applications";

    public $id;
    public $user_id;
    public $course_id;
    public $start_date; // Новое свойство для даты
    public $status = 'pending';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Создание заявки с датой
    public function create()
    {
        $query = "INSERT INTO " . $this->table . " SET user_id=:user_id, course_id=:course_id, start_date=:start_date, status=:status";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":course_id", $this->course_id);
        $stmt->bindParam(":start_date", $this->start_date);
        $stmt->bindParam(":status", $this->status);
        return $stmt->execute();
    }

    // Метод получения заявок с фильтрацией и пагинацией
    public function getAllWithDetails($status = null, $limit = null, $offset = null)
    {
        $query = "SELECT a.id, u.fio_user, c.title AS course_title, a.start_date, a.status, a.created_at 
                  FROM " . $this->table . " a 
                  JOIN users u ON a.user_id = u.id 
                  JOIN courses c ON a.course_id = c.id";

        if ($status) {
            $query .= " WHERE a.status = :status";
        }

        $query .= " ORDER BY a.created_at DESC";

        if ($limit !== null && $offset !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->conn->prepare($query);
        if ($status) $stmt->bindParam(":status", $status);
        if ($limit !== null) $stmt->bindValue(":limit", (int)$limit, PDO::PARAM_INT);
        if ($offset !== null) $stmt->bindValue(":offset", (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt;
    }

    // Подсчет общего количества записей для пагинации
    public function countAll($status = null)
    {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        if ($status) $query .= " WHERE status = :status";
        $stmt = $this->conn->prepare($query);
        if ($status) $stmt->bindParam(":status", $status);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function updateStatus($new_status)
    {
        $query = "UPDATE " . $this->table . " SET status = :new_status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":new_status", $new_status);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }
}
