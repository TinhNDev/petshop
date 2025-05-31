<?php
require_once __DIR__ . '/../controllers/ProductController.php';

class ProductRouter
{
    private $db;
    private $controller;

    public function __construct($db)
    {
        $this->db = $db;
        $this->controller = new ProductController($db);
    }

    public function addRoutes($router)
    {
        $router->addRoute('GET', '/products', 'ProductController', 'getAll');
        $router->addRoute('GET', '/products/{id}', 'ProductController', 'getDetail');
        $router->addRoute('POST', '/products', 'ProductController', 'create');
        $router->addRoute('PUT', '/products/{id}', 'ProductController', 'update');
        $router->addRoute('GET', '/products/type/{pet_type}', 'ProductController', 'findByType');
        $router->addRoute('GET', '/products/price/{min_price}/{max_price}', 'ProductController', 'findByPriceRange');
    }
}
?>