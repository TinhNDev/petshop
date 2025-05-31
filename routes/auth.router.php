<?php
require_once __DIR__ . '/../controllers/AuthController.php';

class AuthRouter
{
    private $db;
    private $controller;

    public function __construct($db)
    {
        $this->db = $db;
        $this->controller = new AuthController($db);
    }

    public function addRoutes($router)
    {
        $router->addRoute('POST', '/login', 'AuthController', 'login');
    }
}