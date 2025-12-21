<?php
// models/Application.php

class Application
{
    private $conn;
    private $table = "applications";

    public $id;
    public $user_id;
    public $course_id;
    public $start_date;
    public $status_id = 1; // 1 - Новое
    public $payment_method_id;
    public $created_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table . " 
                  SET user_id=:user_id, course_id=:course_id, start_date=:start_date, 
                      status_id=:status_id, payment_method_id=:payment_method_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":course_id", $this->course_id);
        $stmt->bindParam(":start_date", $this->start_date);
        $stmt->bindParam(":status_id", $this->status_id);
        $stmt->bindParam(":payment_method_id", $this->payment_method_id);
        return $stmt->execute();
    }

    public function getApplicationsByUserId()
    {
        $query = "SELECT a.*, c.title AS course_title, s.name AS status_name, 
                         pm.method_name AS payment_method_name 
                  FROM " . $this->table . " a 
                  JOIN courses c ON a.course_id = c.id 
                  JOIN statuses s ON a.status_id = s.id
                  LEFT JOIN payment_methods pm ON a.payment_method_id = pm.id
                  WHERE a.user_id = :user_id 
                  ORDER BY a.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->execute();
        return $stmt;
    }

    public function readAll($status_id = null, $limit = null, $offset = null)
    {
        $query = "SELECT a.*, u.fio_user, c.title AS course_title, s.name AS status_name, 
                         pm.method_name AS payment_method
                  FROM " . $this->table . " a
                  JOIN users u ON a.user_id = u.id
                  JOIN courses c ON a.course_id = c.id
                  JOIN statuses s ON a.status_id = s.id
                  JOIN payment_methods pm ON a.payment_method_id = pm.id";
        if ($status_id) $query .= " WHERE a.status_id = :status_id";
        $query .= " ORDER BY a.created_at DESC";
        if ($limit !== null) $query .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        if ($status_id) $stmt->bindParam(":status_id", $status_id);
        if ($limit !== null) {
            $stmt->bindValue(":limit", (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(":offset", (int)$offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt;
    }

    public function countAll($status_id = null)
    {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        if ($status_id) $query .= " WHERE status_id = :status_id";
        $stmt = $this->conn->prepare($query);
        if ($status_id) $stmt->bindParam(":status_id", $status_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function updateStatus($new_status_id)
    {
        $query = "UPDATE " . $this->table . " SET status_id = :status_id WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status_id", $new_status_id);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    public function getPaymentMethods()
    {
        return $this->conn->query("SELECT * FROM payment_methods");
    }

    public function checkActiveApplication()
    {
        $query = "SELECT id FROM " . $this->table . " WHERE user_id = :user_id AND course_id = :course_id AND status_id IN (1, 2)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":course_id", $this->course_id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
