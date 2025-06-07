<?php
require_once __DIR__ . '/../services/wishlist.service.php';
require_once __DIR__ . '/../services/customer.service.php';

class WishlistController
{
    private $service;
    private $customerService;
    
    public function __construct($db)
    {
        $this->service = new WishlistService($db);
        $this->customerService = new CustomerService($db);
    }

    public function addToWishlist($data)
    {
        requireLogin(['user']);
        
        // Lấy customer_id từ phiên đăng nhập
        $customer = $this->customerService->getCustomerByCustomerId($_SESSION['user_id']);
        if (!$customer || !isset($customer['customer_id'])) {
            return [
                'status' => 400,
                'data' => ['success' => false, 'message' => 'Không tìm thấy thông tin khách hàng']
            ];
        }

        $product_id = $data['product_id'] ?? '';
        if (empty($product_id)) {
            return [
                'status' => 400,
                'data' => ['success' => false, 'message' => 'Thiếu thông tin sản phẩm']
            ];
        }

        $result = $this->service->addToWishlist($customer['customer_id'], $product_id);
        return [
            'status' => $result['success'] ? 200 : 400,
            'data' => [
               'items'=> $result,
               'message' => $result['success'] ? 'Thêm vào danh sách yêu thích thành công' : $result['message']
               ]
        ];
    }

    public function removeFromWishlist($data)
    {
        requireLogin(['user']);
        
        // Lấy customer_id từ phiên đăng nhập
        $customer = $this->customerService->getCustomerByCustomerId($_SESSION['user_id']);
        if (!$customer || !isset($customer['customer_id'])) {
            return [
                'status' => 400,
                'data' => ['success' => false, 'message' => 'Không tìm thấy thông tin khách hàng']
            ];
        }

        $product_id = $data['product_id'] ?? '';
        if (empty($product_id)) {
            return [
                'status' => 400,
                'data' => ['success' => false, 'message' => 'Thiếu thông tin sản phẩm']
            ];
        }

        $result = $this->service->removeFromWishlist($customer['customer_id'], $product_id);
        return [
            'status' => $result['success'] ? 200 : 400,
            'data' => $result
        ];
    }

    public function getWishlist()
    {
        requireLogin(['user']);
        
        // Lấy customer_id từ phiên đăng nhập
        $customer = $this->customerService->getCustomerByCustomerId($_SESSION['user_id']);
        if (!$customer || !isset($customer['customer_id'])) {
            return [
                'status' => 400,
                'data' => ['success' => false, 'message' => 'Không tìm thấy thông tin khách hàng']
            ];
        }

        $items = $this->service->getWishlistByCustomer($customer['customer_id']);
        return [
            'status' => 200,
            'data' => [
                'success' => true,
                'items' => $items,
                'count' => count($items)
            ]
        ];
    }
    
    public function getWishlistCount()
    {
        requireLogin(['user']);
        
        // Lấy customer_id từ phiên đăng nhập
        $customer = $this->customerService->getCustomerByCustomerId($_SESSION['user_id']);
        if (!$customer || !isset($customer['customer_id'])) {
            return [
                'status' => 400,
                'data' => ['success' => false, 'message' => 'Không tìm thấy thông tin khách hàng']
            ];
        }

        $count = $this->service->getWishlistCount($customer['customer_id']);
        return [
            'status' => 200,
            'data' => [
                'success' => true,
                'count' => $count
            ]
        ];
    }
}
?>