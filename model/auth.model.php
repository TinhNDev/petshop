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
        $query = "SELECT * FROM {$this->table_name} WHERE user_name = :user_name LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_name', $user_name);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            unset($user['password_hash']);
            return $user;
        }
         return $user;
    }
}