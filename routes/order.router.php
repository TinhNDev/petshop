<?php
require_once __DIR__ . '/../controllers/OrderController.php';

class OrderRouter
{
    private $db;
    private $controller;

    public function __construct($db)
    {
        $this->db = $db;
        $this->controller = new OrderController($db);
    }

    public function addRoutes($router)
    {
        $router->addRoute('POST', '/orders/cart', 'OrderController', 'createOrderFromCart');
        $router->addRoute('POST', '/orders/buynow', 'OrderController', 'buyNow');
        $router->addRoute('GET', '/orders/customer/{customer_id}', 'OrderController', 'getAllOrdersByCustomer');
        $router->addRoute('GET', '/orders/{order_id}', 'OrderController', 'getOrderDetail');
    }
}
?>