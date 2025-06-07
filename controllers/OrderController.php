<?php
require_once __DIR__ . '/../services/order.service.php';
require_once __DIR__ . '/../middleware/session.middleware.php';

class OrderController
{
    private $service;
    private $auth;
    public function __construct($db)
    {
        $this->service = new OrderService($db);
        $this->auth = new CustomerService($db);
    }

    public function createOrderFromCart($data)
    {
        requireLogin(['admin', 'user']);
        
        // Lấy thông tin khách hàng từ session
        $customerData = $this->auth->getCustomerByCustomerId($_SESSION['user_id']);
        
        if (!$customerData || empty($customerData['customer_id'])) {
            return [
                'status' => 400,
                'data' => [
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin khách hàng'
                ]
            ];
        }
        
        // Thêm customer_id vào data
        $data['customer_id'] = $customerData['customer_id'];
        
        // Kiểm tra giỏ hàng
        $cart_items = isset($data['cart_items']) ? $data['cart_items'] : [];
        
        if (empty($cart_items)) {
            return [
                'status' => 400,
                'data' => [
                    'success' => false,
                    'message' => 'Giỏ hàng trống'
                ]
            ];
        }
        
        // Kiểm tra address_id
        if (empty($data['address_id'])) {
            return [
                'status' => 400,
                'data' => [
                    'success' => false,
                    'message' => 'Vui lòng chọn địa chỉ giao hàng'
                ]
            ];
        }
        
        // Thêm các thông tin mặc định nếu chưa có
        if (empty($data['payment_method'])) {
            $data['payment_method'] = 'COD';
        }
        
        if (empty($data['payment_status'])) {
            $data['payment_status'] = 'pending';
        }
        
        if (empty($data['status'])) {
            $data['status'] = 'pending';
        }
        
        // Gọi service để xử lý đặt hàng
        $result = $this->service->createOrderFromCart($data, $cart_items);
    
        // Đảm bảo result và message luôn tồn tại
        if (!is_array($result)) {
            $result = ['success' => false, 'message' => 'Lỗi không xác định'];
        }
        
        if (!isset($result['message'])) {
            $result['message'] = $result['success'] ? 'Đặt hàng thành công' : 'Đặt hàng thất bại';
        }
    
        return [
            'status' => (!empty($result['success']) && $result['success']) ? 201 : 400,
            'data' => !empty($result['success']) && $result['success']
                ? ['order_id' => $result['order_id'], 'message' => $result['message']]
                : ['message' => $result['message']]
        ];
    }

    public function buyNow($data)
    {
        requireLogin(['admin', 'user']);
        
        // Lấy thông tin khách hàng từ session
        $customerData = $this->auth->getCustomerByCustomerId($_SESSION['user_id']);
        
        if (!$customerData || empty($customerData['customer_id'])) {
            return [
                'status' => 400,
                'data' => [
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin khách hàng'
                ]
            ];
        }
        
        // Thêm customer_id vào data
        $data['customer_id'] = $customerData['customer_id'];
        
        // Chuẩn bị dữ liệu sản phẩm
        $product = isset($data['product']) ? $data['product'] : [];
        
        // Kiểm tra thông tin sản phẩm
        if (empty($product['product_id']) || empty($product['quantity'])) {
            return [
                'status' => 400,
                'data' => [
                    'success' => false,
                    'message' => 'Thiếu thông tin sản phẩm cần thiết'
                ]
            ];
        }
        
        // Kiểm tra address_id
        if (empty($data['address_id'])) {
            return [
                'status' => 400,
                'data' => [
                    'success' => false,
                    'message' => 'Vui lòng chọn địa chỉ giao hàng'
                ]
            ];
        }
        
        // Thêm các thông tin mặc định nếu chưa có
        if (empty($data['payment_method'])) {
            $data['payment_method'] = 'COD';
        }
        
        if (empty($data['payment_status'])) {
            $data['payment_status'] = 'pending';
        }
        
        // Gọi service để xử lý đặt hàng
        $result = $this->service->buyNow($data, $product);
    
        // Đảm bảo result và message luôn tồn tại
        if (!is_array($result)) {
            $result = ['success' => false, 'message' => 'Lỗi không xác định'];
        }
        
        if (!isset($result['message'])) {
            $result['message'] = $result['success'] ? 'Đặt hàng thành công' : 'Đặt hàng thất bại';
        }
    
        return [
            'status' => (!empty($result['success']) && $result['success']) ? 201 : 400,
            'data' => !empty($result['success']) && $result['success']
                ? ['order_id' => $result['order_id'], 'message' => $result['message']]
                : ['message' => $result['message']]
        ];
    }

    public function getAllOrdersByCustomer()
    {
        requireLogin(['admin', 'user']);

        $customer_id = $this->auth->getCustomerByCustomerId($_SESSION['user_id']);

        $orders = $this->service->getAllOrdersByCustomer($customer_id['customer_id']);
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
    public function filterOrders()
    {
        requireLogin(['admin']);

        $filters = [
            'payment_status' => $_GET['payment_status'] ?? '',
            'status' => $_GET['status'] ?? '',
            'customer_id' => $_GET['customer_id'] ?? '',
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? ''
        ];

        $orders = $this->service->filterOrders($filters);

        return [
            'status' => 200,
            'data' => [
                'success' => true,
                'orders' => $orders
            ]
        ];
    }
}