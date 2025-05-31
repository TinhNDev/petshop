<?php
require_once __DIR__ . '/../controllers/PromotionController.php';

class PromotionRouter
{
    private $db;
    private $controller;

    public function __construct($db)
    {
        $this->db = $db;
        $this->controller = new PromotionController($db);
    }

    public function addRoutes($router)
    {
        $router->addRoute('GET', '/promotions', 'PromotionController', 'getAll');
        $router->addRoute('GET', '/promotions/{id}', 'PromotionController', 'getDetail');
        $router->addRoute('POST', '/promotions', 'PromotionController', 'create');
        $router->addRoute('PUT', '/promotions/{id}', 'PromotionController', 'update');
        $router->addRoute('GET', '/promotions/code/{code}', 'PromotionController', 'findByCode');
    }
}
?>