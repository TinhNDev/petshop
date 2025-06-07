<?php
require_once 'config/database.php';
require_once 'routes/index.router.php';
require_once 'routes/product.router.php';
require_once 'routes/promotion.router.php';
require_once 'routes/categories.router.php';
require_once 'routes/cart.router.php';
require_once 'routes/order.router.php';
require_once 'routes/auth.router.php';
require_once 'routes/customer.router.php';
require_once 'routes/wishlist.router.php';
require_once 'routes/address.router.php';
$database = new Database();
$db = $database->getConnection();

$router = new Router($db);

$productRouter = new ProductRouter($db);
$promotionRouter = new PromotionRouter($db);
$categoriesRouter = new CategoriesRouter($db);
$cartRouter = new CartRouter($db);
$orderRouter = new OrderRouter($db);
$authRouter = new AuthRouter($db); 
$customerRouter = new CustomerRouter($db);
$wishlistRouter = new WishlistRouter($db);
$addressRouter = new AddressRouter($db);
$addressRouter->addRoutes($router);
$productRouter->addRoutes($router);
$promotionRouter->addRoutes($router);
$categoriesRouter->addRoutes($router);
$cartRouter->addRoutes($router);
$orderRouter->addRoutes($router);
$authRouter->addRoutes($router); 
$customerRouter->addRoutes($router);
$wishlistRouter->addRoutes($router);
$router->dispatch();