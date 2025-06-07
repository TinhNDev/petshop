<?php
require_once __DIR__ . '/../model/auth.model.php';

class AuthService
{
    private $authModel;
    private $cus;
    public function __construct($db)
    {
        $this->authModel = new Auth($db);
        $this->cus = new CustomerService($db);
    }

    public function login($user_name, $password)
    {
        if (empty($user_name) || empty($password)) {
            return [
                'error' => true,
                'message' => 'Missing user_name or password'
            ];
        }

        $user = $this->authModel->login($user_name, $password);
        if ($user) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['role'] = $user['role'];
            
            $responseData = [
                'status' => 200,
                'data' => [
                    'success' => true,
                    'message' => 'Đăng nhập thành công',
                    'user' => $user['user_name'], // Mặc định hiển thị user_name
                    'role' => $user['role'],
                    'session_id' => session_id()
                ]
            ];
            
            if ($user['role'] === 'user') {
                try {
                    $customer_id = $this->cus->getCustomerByCustomerId($_SESSION['user_id']);
                    
                    if ($customer_id && isset($customer_id['customer_id'])) {
                        $customer = $this->cus->getCustomerById($customer_id['customer_id']);
                        $_SESSION['customer_id'] = $customer_id['customer_id'];
                        
                        if ($customer) {
                            $responseData['data']['user'] = $customer['customer_name'];
                            $responseData['data']['customer'] = [
                                'customer_id' => $customer['customer_id'],
                                'customer_name' => $customer['customer_name']
                            ];
                        }
                    }
                } catch (Exception $e) {
                    error_log('Error getting customer info: ' . $e->getMessage());
                }
            }
            
            $params = session_get_cookie_params();
            setcookie(session_name(), session_id(), [
                'expires' => time() + 86400,
                'path' => '/',
                'domain' => '',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            return $responseData;
        }
        
        return [
            'status' => 401,
            'data' => [
                'success' => false,
                'message' => 'Đăng nhập thất bại'
            ]
        ];
    }
}