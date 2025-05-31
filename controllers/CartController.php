<?php
require_once __DIR__ . '/../services/cart.service.php';
require_once __DIR__ . '/../middleware/session.middleware.php';

class CartController
{
    private $service;

    public function __construct($db)
    {
        $this->service = new CartService($db);
    }

    public function getCart($customer_id)
    {
        requireLogin(['admin','user']);
        $result = $this->service->getOrCreateCartId($customer_id);
        return [
            'status' => 200,
            'data' => $result
        ];
    }

    public function addToCart($customer_id, $data)
    {
        requireLogin(['admin','user']);
        $result = $this->service->addToCart(
            $customer_id,
            $data['product_id'],
            $data['quantity']
        );
        return [
            'status' => $result ? 200 : 400,
            'data' => $result ? $result : ['message' => 'Add to cart failed']
        ];
    }

    public function removeFromCart($cart_item_id, $product_id)
    {
        requireLogin(['admin','user']);
        $result = $this->service->removeFromCart($cart_item_id, $product_id);
        return [
            'status' => $result ? 200 : 400,
            'data' => ['success' => $result]
        ];
    }
}