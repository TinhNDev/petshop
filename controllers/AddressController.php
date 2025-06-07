<?php
require_once __DIR__ . '/../services/address.service.php';
require_once __DIR__ . '/../services/customer.service.php';
require_once __DIR__ . '/../middleware/session.middleware.php';

class AddressController
{
    private $addressService;
    private $authService;

    public function __construct($db)
    {
        $this->addressService = new AddressService($db);
        $this->authService = new CustomerService($db);
    }

    public function getAddressesByCustomer()
    {
        requireLogin(['user']);
        
        // Lấy customer_id từ phiên đăng nhập hiện tại
        $customer = $this->authService->getCustomerByCustomerId($_SESSION['user_id']);
        
        if (!$customer || !isset($customer['customer_id'])) {
            return [
                'status' => 400,
                'data' => [
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin khách hàng'
                ]
            ];
        }
        
        $customer_id = $customer['customer_id'];
        $addresses = $this->addressService->getAddressesByCustomer($customer_id);
        
        return [
            'status' => 200,
            'data' => [
                'success' => true,
                'addresses' => $addresses
            ]
        ];
    }
    
    public function getAddressById($id)
    {
        requireLogin(['user']);
        
        // Lấy customer_id từ phiên đăng nhập hiện tại
        $customer = $this->authService->getCustomerByCustomerId($_SESSION['user_id']);
        
        if (!$customer || !isset($customer['customer_id'])) {
            return [
                'status' => 400,
                'data' => [
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin khách hàng'
                ]
            ];
        }
        
        $address = $this->addressService->getAddressById($id);
        
        if (!$address) {
            return [
                'status' => 404,
                'data' => [
                    'success' => false,
                    'message' => 'Không tìm thấy địa chỉ'
                ]
            ];
        }
        
        // Kiểm tra quyền truy cập - địa chỉ có phải của khách hàng này không
        if ($address['customer_id'] !== $customer['customer_id']) {
            return [
                'status' => 403,
                'data' => [
                    'success' => false,
                    'message' => 'Bạn không có quyền truy cập địa chỉ này'
                ]
            ];
        }
        
        return [
            'status' => 200,
            'data' => [
                'success' => true,
                'address' => $address
            ]
        ];
    }
    
    public function addAddress($data)
    {
        requireLogin(['user']);
        
        // Lấy customer_id từ phiên đăng nhập hiện tại
        $customer = $this->authService->getCustomerByCustomerId($_SESSION['user_id']);
        
        if (!$customer || !isset($customer['customer_id'])) {
            return [
                'status' => 400,
                'data' => [
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin khách hàng'
                ]
            ];
        }
        
        // Sử dụng customer_id từ phiên đăng nhập thay vì từ request để đảm bảo an toàn
        $data['customer_id'] = $customer['customer_id'];
        
        // Kiểm tra dữ liệu đầu vào
        if (empty($data['address_line']) || empty($data['ward_id'])) {
            return [
                'status' => 400,
                'data' => [
                    'success' => false,
                    'message' => 'Thiếu thông tin địa chỉ cần thiết'
                ]
            ];
        }
        
        $result = $this->addressService->addAddress($data);
        
        return [
            'status' => $result['success'] ? 200 : 400,
            'data' => $result
        ];
    }
    
    public function updateAddress($data)
    {
        requireLogin(['user']);
        
        if (empty($data['address_id'])) {
            return [
                'status' => 400,
                'data' => [
                    'success' => false,
                    'message' => 'Thiếu ID địa chỉ cần cập nhật'
                ]
            ];
        }
        
        // Lấy customer_id từ phiên đăng nhập hiện tại
        $customer = $this->authService->getCustomerByCustomerId($_SESSION['user_id']);
        
        if (!$customer || !isset($customer['customer_id'])) {
            return [
                'status' => 400,
                'data' => [
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin khách hàng'
                ]
            ];
        }
        
        // Kiểm tra quyền truy cập
        $address = $this->addressService->getAddressById($data['address_id']);
        if (!$address || $address['customer_id'] !== $customer['customer_id']) {
            return [
                'status' => 403,
                'data' => [
                    'success' => false,
                    'message' => 'Bạn không có quyền cập nhật địa chỉ này'
                ]
            ];
        }
        
        $address_id = $data['address_id'];
        unset($data['address_id']); // Loại bỏ address_id khỏi dữ liệu cập nhật
        
        $result = $this->addressService->updateAddress($address_id, $data);
        
        return [
            'status' => $result['success'] ? 200 : 400,
            'data' => $result
        ];
    }
    
    public function deleteAddress($data)
    {
        requireLogin(['user']);
        
        if (empty($data['address_id'])) {
            return [
                'status' => 400,
                'data' => [
                    'success' => false,
                    'message' => 'Thiếu ID địa chỉ cần xóa'
                ]
            ];
        }
        
        // Lấy customer_id từ phiên đăng nhập hiện tại
        $customer = $this->authService->getCustomerByCustomerId($_SESSION['user_id']);
        
        if (!$customer || !isset($customer['customer_id'])) {
            return [
                'status' => 400,
                'data' => [
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin khách hàng'
                ]
            ];
        }
        
        // Kiểm tra quyền truy cập
        $address = $this->addressService->getAddressById($data['address_id']);
        if (!$address || $address['customer_id'] !== $customer['customer_id']) {
            return [
                'status' => 403,
                'data' => [
                    'success' => false,
                    'message' => 'Bạn không có quyền xóa địa chỉ này'
                ]
            ];
        }
        
        $result = $this->addressService->deleteAddress($data['address_id']);
        
        return [
            'status' => $result['success'] ? 200 : 400,
            'data' => $result
        ];
    }
    
    public function setDefaultAddress($data)
    {
        requireLogin(['user']);
        
        if (empty($data['address_id'])) {
            return [
                'status' => 400,
                'data' => [
                    'success' => false,
                    'message' => 'Thiếu ID địa chỉ cần đặt làm mặc định'
                ]
            ];
        }
        
        // Lấy customer_id từ phiên đăng nhập hiện tại
        $customer = $this->authService->getCustomerByCustomerId($_SESSION['user_id']);
        
        if (!$customer || !isset($customer['customer_id'])) {
            return [
                'status' => 400,
                'data' => [
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin khách hàng'
                ]
            ];
        }
        
        $result = $this->addressService->setDefaultAddress($customer['customer_id'], $data['address_id']);
        
        return [
            'status' => $result['success'] ? 200 : 400,
            'data' => $result
        ];
    }
}
?>