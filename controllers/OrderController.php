<?php
require_once __DIR__ . '/../services/order.service.php';

class OrderController
{
    private $service;

    public function __construct($db)
    {
        $this->service = new OrderService($db);
    }

    public function createOrderFromCart($data)
    {
        $cart_items = isset($data['cart_items']) ? $data['cart_items'] : [];
        $order_id = $this->service->createOrderFromCart($data, $cart_items);
        return [
            'status' => $order_id ? 201 : 400,
            'data' => $order_id ? ['order_id' => $order_id] : ['message' => 'Create order from cart failed']
        ];
    }

    public function buyNow($data)
    {
        $product = isset($data['product']) ? $data['product'] : [];
        $order_id = $this->service->buyNow($data, $product);
        return [
            'status' => $order_id ? 201 : 400,
            'data' => $order_id ? ['order_id' => $order_id] : ['message' => 'Buy now failed']
        ];
    }

    public function getAllOrdersByCustomer($customer_id)
    {
        $orders = $this->service->getAllOrdersByCustomer($customer_id);
        return [
            'status' => 200,
            'data' => $orders
        ];
    }

    public function getOrderDetail($order_id)
    {
        $details = $this->service->getOrderDetail($order_id);
        return [
            'status' => $details ? 200 : 404,
            'data' => $details 
        ];
    }
}