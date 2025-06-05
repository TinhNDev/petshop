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
        $customer = $this->service->getCustomerById($id);
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
}
?>