<?php
require_once __DIR__ . '/../services/order.service.php';
require_once __DIR__ . '/../middleware/session.middleware.php';

class OrderController
{
    private $service;

    public function __construct($db)
    {
        $this->service = new OrderService($db);
    }

    public function createOrderFromCart($data)
    {
        requireLogin(['admin', 'user']);
        $cart_items = isset($data['cart_items']) ? $data['cart_items'] : [];
        $result = $this->service->createOrderFromCart($data, $cart_items);

        return [
            'status' => (!empty($result['success']) && $result['success']) ? 201 : 400,
            'data' => !empty($result['success']) && $result['success']
                ? ['order_id' => $result['order_id']]
                : ['message' => $result['message'] ?? 'Create order from cart failed']
        ];
    }

    public function buyNow($data)
    {
        requireLogin(['admin', 'user']);
        $product = isset($data['product']) ? $data['product'] : [];
        $result = $this->service->buyNow($data, $product);

        return [
            'status' => (!empty($result['success']) && $result['success']) ? 201 : 400,
            'data' => !empty($result['success']) && $result['success']
                ? ['order_id' => $result['order_id']]
                : ['message' => $result['message'] ?? 'Buy now failed']
        ];
    }

    public function getAllOrdersByCustomer($customer_id)
    {
        requireLogin(['admin', 'user']);
        $orders = $this->service->getAllOrdersByCustomer($customer_id);
        return [
            'status' => 200,
            'data' => $orders
        ];
    }

    public function getOrderDetail($order_id)
    {
        requireLogin(['admin', 'user']);
        $details = $this->service->getOrderDetail($order_id);
        return [
            'status' => $details ? 200 : 404,
            'data' => $details ? $details : ['message' => 'Order not found']
        ];
    }

    public function countOrders()
    {
        requireLogin(['admin']);
        $total = $this->service->countOrders();
        return [
            'status' => 200,
            'data' => ['total_orders' => $total]
        ];
    }

    public function countOrdersByStatus()
    {
        requireLogin(['admin']);
        $result = $this->service->countOrdersByStatus();
        return [
            'status' => 200,
            'data' => $result
        ];
    }

    public function revenueThisMonth()
    {
        requireLogin(['admin']);
        $revenue = $this->service->revenueThisMonth();
        return [
            'status' => 200,
            'data' => ['revenue_this_month' => $revenue]
        ];
    }

    public function countActivePromotions()
    {
        requireLogin(['admin']);
        $total = $this->service->countActivePromotions();
        return [
            'status' => 200,
            'data' => ['active_promotions' => $total]
        ];
    }

    public function countProducts()
    {
        requireLogin(['admin']);
        $total = $this->service->countProducts();
        return [
            'status' => 200,
            'data' => ['total_products' => $total]
        ];
    }

    public function countCustomers()
    {
        requireLogin(['admin']);
        $total = $this->service->countCustomers();
        return [
            'status' => 200,
            'data' => ['total_customers' => $total]
        ];
    }

    public function getAllStatistics()
    {
        requireLogin(['admin']);
        return [
            'status' => 200,
            'data' => [
                'total_orders' => $this->service->countOrders(),
                'orders_by_status' => $this->service->countOrdersByStatus(),
                'revenue_this_month' => $this->service->revenueThisMonth(),
                'active_promotions' => $this->service->countActivePromotions(),
                'total_products' => $this->service->countProducts(),
                'total_customers' => $this->service->countCustomers()
            ]
        ];
    }

    public function getAllOrders()
    {
        requireLogin(['admin']);
        $orders = $this->service->getAllOrders();

        return [
            'status' => 200,
            'data' => [
                'success' => true,
                'orders' => $orders
            ]
        ];
    }
}