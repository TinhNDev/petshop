<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/customer.service.php';

class CustomerController
{
    private $service;

    public function __construct()
    {
        $db = (new Database())->getConnection();
        $this->service = new CustomerService($db);
    }

    public function getCustomer($id)
    {
        requireLogin(['admin', 'user']);
        $customer_data = $this->service->getCustomerByCustomerId($_SESSION['user_id']);
        
        if (!$customer_data || !isset($customer_data['customer_id'])) {
            return [
                'status' => 400,
                'data' => ['message' => 'Không tìm thấy thông tin khách hàng']
            ];
        }
        
        $customer_id = $customer_data['customer_id'];
        $customer = $this->service->getCustomerById($customer_id);
         return [
            'status' => $customer ? 201 : 400,
            'data' => ['success' => $customer]
        ];
    }

    public function getAllCustomers()
    {
        requireLogin(['admin']);
        $customers = $this->service->getAllCustomers();
        return [
            'status' => 200,
            'data' => ['success' => true, 'customers' => $customers]
        ];
    }

    public function changePassword($data)
    {
        requireLogin(['user', 'admin']);
        
        // Kiểm tra dữ liệu đầu vào
        if (empty($data['current_password']) || 
            empty($data['new_password']) || 
            empty($data['confirm_password'])) {
            return [
                'status' => 400,
                'data' => [
                    'success' => false,
                    'message' => 'Vui lòng cung cấp đầy đủ thông tin mật khẩu'
                ]
            ];
        }
        
        // Lấy user_id từ session
        $user_id = $_SESSION['user_id'];
        
        // Gọi service để thực hiện đổi mật khẩu
        $result = $this->service->changePassword(
            $user_id,
            $data['current_password'],
            $data['new_password'],
            $data['confirm_password']
        );
        
        return [
            'status' => $result['success'] ? 200 : 400,
            'data' => $result
        ];
    }
}
?>