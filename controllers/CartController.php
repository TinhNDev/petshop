<?php
require_once __DIR__ . '/../services/cart.service.php';
require_once __DIR__ . '/../middleware/session.middleware.php';

class CartController
{
    private $service;
 private $auth;
    public function __construct($db)
    {
        $this->service = new CartService($db);
        $this->auth = new CustomerService($db);
    }

    public function getCart($customer_id)
    {
        requireLogin(['admin','user']);
        $customer_id = $this->auth->getCustomerByCustomerId($_SESSION['user_id']);
        $result = $this->service->getOrCreateCartId($customer_id['customer_id']);
        return [
            'status' => 200,
            'data' => $result
        ];
    }

    public function addToCart($data)
    {
        requireLogin(['admin','user']);
        

        
        $customer_data = $this->auth->getCustomerByCustomerId($_SESSION['user_id']);
        
        if (!$customer_data || !isset($customer_data['customer_id'])) {
            return [
                'status' => 400,
                'data' => ['message' => 'Không tìm thấy thông tin khách hàng']
            ];
        }
        
        $customer_id = $customer_data['customer_id'];
        
        $result = $this->service->addToCart(
            $customer_id,
            $data['product_id'],
            $data['quantity']
        );
        
        return [
            'status' => $result ? 200 : 400,
            'data' => $result ? $result : ['message' => 'Thêm vào giỏ hàng thất bại']
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