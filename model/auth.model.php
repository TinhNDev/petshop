<?php
class Auth
{
    private $conn;
    private $table_name = "user_account";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function login($user_name, $password)
    {
        $query = "SELECT * FROM {$this->table_name} WHERE user_name = :user_name and password_hash = :password_hash";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_name', $user_name);
        $stmt->bindParam(':password_hash', $password);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
         return $user;
    }
}