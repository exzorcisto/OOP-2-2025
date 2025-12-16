<?php
// models/User.php

class User
{
    private $conn;
    private $table = "users";

    public $id;
    public $login;
    public $password;
    public $fio_user;
    public $number;
    public $role_id; // 1 - Пользователь, 2 - Администратор

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Метод регистрации пользователя
    public function register()
    {
        // Устанавливаем role_id=1 по умолчанию (пользователь)
        $query = "INSERT INTO " . $this->table . " 
                  SET login=:login, password=:password, fio_user=:fio_user, number=:number, role_id=1";
        $stmt = $this->conn->prepare($query);

        // Хеширование пароля
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);

        // Привязка параметров
        $stmt->bindParam(":login", $this->login);
        $stmt->bindParam(":password", $password_hash);
        $stmt->bindParam(":fio_user", $this->fio_user);
        $stmt->bindParam(":number", $this->number);

        return $stmt->execute();
    }

    // Метод входа пользователя
    public function login()
    {
        $query = "SELECT id, login, password, fio_user, role_id FROM " . $this->table . " WHERE login = :login LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":login", $this->login);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Проверка пароля
            if (password_verify($this->password, $row['password'])) {
                // Если пароль верен, заполняем свойства объекта
                $this->id = $row['id'];
                $this->fio_user = $row['fio_user'];
                $this->role_id = $row['role_id'];
                return true;
            }
        }
        return false;
    }
}
