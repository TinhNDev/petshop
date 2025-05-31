<?php
require_once __DIR__ . '/../model/auth.model.php';

class AuthService
{
    private $authModel;

    public function __construct($db)
    {
        $this->authModel = new Auth($db);
    }

    public function login($user_name, $password)
    {
        if (empty($user_name) || empty($password)) {
            return [
                'error' => true,
                'message' => 'Missing user_name or password'
            ];
        }
    
        $user = $this->authModel->login($user_name, $password);
        if ($user) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['role'] = $user['role'];
            return $user;
        }
        return false;
    }
}