<?php
require_once __DIR__ . '/../model/customer.model.php';

class CustomerService
{
    private $customerModel;

    public function __construct($db)
    {
        $this->customerModel = new Customer($db);
    }

    public function getCustomerById($id)
    {
        return $this->customerModel->findOne($id);
    }

}