<?php
require_once __DIR__ . '/../controllers/WishlistController.php';

class WishlistRouter
{
    private $db;
    private $controller;

    public function __construct($db)
    {
        $this->db = $db;
        $this->controller = new WishlistController($db);
    }

    public function addRoutes($router)
    {
        $router->addRoute('POST', '/wishlist/add', 'WishlistController', 'addToWishlist');
        $router->addRoute('POST', '/wishlist/remove', 'WishlistController', 'removeFromWishlist');
        $router->addRoute('GET', '/wishlist', 'WishlistController', 'getWishlist');
        $router->addRoute('GET', '/wishlist/count', 'WishlistController', 'getWishlistCount');
    }
}
?>