<?php
// models/Review.php

class Review
{
    private $conn;
    private $table = "reviews";

    // Свойства
    public $id;
    public $user_id;
    public $course_id;
    public $rating;
    public $comment;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Создание нового отзыва
    public function create()
    {
        $query = "INSERT INTO " . $this->table . " SET user_id=:user_id, course_id=:course_id, rating=:rating, comment=:comment";
        $stmt = $this->conn->prepare($query);

        // Очистка и привязка параметров
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":course_id", $this->course_id);
        $stmt->bindParam(":rating", $this->rating);
        $stmt->bindParam(":comment", htmlspecialchars(strip_tags($this->comment))); // Защита от XSS

        return $stmt->execute();
    }

    // Получение отзывов для конкретного курса (для application.php)
    public function getReviewsByCourseId()
    {
        $query = "SELECT r.rating, r.comment, u.fio_user 
                  FROM " . $this->table . " r 
                  JOIN users u ON r.user_id = u.id 
                  WHERE r.course_id = :course_id
                  ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":course_id", $this->course_id);
        $stmt->execute();

        return $stmt;
    }

    // Получение всех отзывов пользователя (для profile.php)
    public function getReviewsByUserId()
    {
        $query = "SELECT c.title AS course_title, r.rating, r.comment, r.created_at 
                  FROM " . $this->table . " r 
                  JOIN courses c ON r.course_id = c.id 
                  WHERE r.user_id = :user_id
                  ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->execute();

        return $stmt;
    }

    // Получение курсов, доступных для отзыва (статус 'completed' и нет отзыва)
    public function getCoursesAvailableForReview()
    {
        $query = "SELECT 
                    c.id, c.title 
                  FROM courses c
                  JOIN applications a ON a.course_id = c.id
                  LEFT JOIN " . $this->table . " r ON r.course_id = c.id AND r.user_id = a.user_id
                  WHERE a.user_id = :user_id 
                    AND a.status = 'completed' 
                    AND r.id IS NULL";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->execute();

        return $stmt;
    }
}
