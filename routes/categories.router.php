<?php
require_once __DIR__ . '/../controllers/CategoriesController.php';

class CategoriesRouter
{
    private $db;
    private $controller;

    public function __construct($db)
    {
        $this->db = $db;
        $this->controller = new CategoriesController($db);
    }

    public function addRoutes($router)
    {
        $router->addRoute('GET', '/categories', 'CategoriesController', 'getAll');
        $router->addRoute('GET', '/categories/{id}', 'CategoriesController', 'getOne');
        $router->addRoute('POST', '/categories', 'CategoriesController', 'create');
        $router->addRoute('PUT', '/categories/{id}', 'CategoriesController', 'update');
        $router->addRoute('GET', '/categories/{id}/products', 'CategoriesController', 'getProducts');
    }
}
?>