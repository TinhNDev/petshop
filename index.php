<?php
require_once 'config/database.php';
require_once 'routes/index.router.php';
require_once 'routes/product.router.php';
require_once 'routes/promotion.router.php';
require_once 'routes/categories.router.php';
require_once 'routes/cart.router.php';
require_once 'routes/order.router.php';

$database = new Database();
$db = $database->getConnection();

$router = new Router($db);

$productRouter = new ProductRouter($db);
$promotionRouter = new PromotionRouter($db);
$categoriesRouter = new CategoriesRouter($db);
$cartRouter = new CartRouter($db);
$orderRouter = new OrderRouter($db);

$productRouter->addRoutes($router);
$promotionRouter->addRoutes($router);
$categoriesRouter->addRoutes($router);
$cartRouter->addRoutes($router);
$orderRouter->addRoutes($router);

$router->dispatch();