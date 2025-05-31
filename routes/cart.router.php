<?php
require_once __DIR__ . '/../controllers/CartController.php';

class CartRouter
{
    private $db;
    private $controller;

    public function __construct($db)
    {
        $this->db = $db;
        $this->controller = new CartController($db);
    }

    public function addRoutes($router)
    {
        $router->addRoute('GET', '/cart/{customer_id}', 'CartController', 'getCart');
        $router->addRoute('POST', '/cart/{customer_id}', 'CartController', 'addToCart');
        $router->addRoute('DELETE', '/cart/{cart_item_id}/{product_id}', 'CartController', 'removeFromCart');
    }
}
?>