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

        $router->addRoute('GET', '/orders/statistics/total', 'OrderController', 'countOrders');
        $router->addRoute('GET', '/orders/statistics/by-status', 'OrderController', 'countOrdersByStatus');
        $router->addRoute('GET', '/orders/statistics/revenue-this-month', 'OrderController', 'revenueThisMonth');
        $router->addRoute('GET', '/orders/statistics/active-promotions', 'OrderController', 'countActivePromotions');
        $router->addRoute('GET', '/orders/statistics/total-products', 'OrderController', 'countProducts');
        $router->addRoute('GET', '/orders/statistics/total-customers', 'OrderController', 'countCustomers');
        $router->addRoute('GET', '/orders/statistics/all', 'OrderController', 'getAllStatistics');
    }
}