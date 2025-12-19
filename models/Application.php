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
    public $start_date; // Новое свойство для хранения даты начала
    public $status = 'pending';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Создание новой заявки с учетом даты начала
    public function create()
    {
        $query = "INSERT INTO " . $this->table . " 
                  SET user_id=:user_id, course_id=:course_id, start_date=:start_date, status=:status";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":course_id", $this->course_id);
        $stmt->bindParam(":start_date", $this->start_date); // Привязка даты начала
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

    // Тот самый метод для profile.php: получение заявок конкретного пользователя
    public function getApplicationsByUserId()
    {
        // Добавлено поле a.start_date в выборку
        $query = "SELECT a.id, c.title as course_title, a.start_date, a.status, a.created_at
                  FROM " . $this->table . " a
                  JOIN courses c ON a.course_id = c.id
                  WHERE a.user_id = :user_id
                  ORDER BY a.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->execute();

        return $stmt;
    }

    // Получение всех заявок с поддержкой фильтрации и пагинации (для админки)
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

    // Подсчет общего количества заявок для работы пагинации
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

    // Обновление статуса заявки
    public function updateStatus($new_status)
    {
        $query = "UPDATE " . $this->table . " SET status = :new_status WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":new_status", $new_status);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }
}
