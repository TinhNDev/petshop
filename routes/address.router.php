<?php
require_once __DIR__ . '/../controllers/AddressController.php';

class AddressRouter
{
    private $db;
    private $controller;

    public function __construct($db)
    {
        $this->db = $db;
        $this->controller = new AddressController($db);
    }

    public function addRoutes($router)
    {
        $router->addRoute('GET', '/address', 'AddressController', 'getAddressesByCustomer');
        $router->addRoute('GET', '/addresses/{id}', 'AddressController', 'getAddressById');
        $router->addRoute('POST', '/addresses', 'AddressController', 'addAddress');
        $router->addRoute('PUT', '/addresses/{id}', 'AddressController', 'updateAddress');
        $router->addRoute('DELETE', '/addresses/{id}', 'AddressController', 'deleteAddress');
        $router->addRoute('POST', '/addresses/{id}/default', 'AddressController', 'setDefaultAddress');
    }
}
?>