<?php
require_once __DIR__ . '/../services/product.service.php';

class ProductController
{
    private $service;

    public function __construct()
    {
        $this->service = new ProductService();
    }


    public function create($data)
    {
        $result = $this->service->createProduct($data);
        return [
            'status' => $result ? 201 : 400,
            'data' => ['success' => $result]
        ];
    }

    public function update($id, $data)
    {
        $result = $this->service->updateProduct($id, $data);
        return [
            'status' => $result ? 200 : 400,
            'data' => ['success' => $result]
        ];
    }

    public function getDetail($id)
    {
        $result = $this->service->getDetailProducts($id);
        return [
            'status' => $result ? 200 : 404,
            'data' => $result ? $result : ['message' => 'Product not found']
        ];
    }

    public function getAll()
    {
        $result = $this->service->getAllProducts();
        return [
            'status' => 200,
            'data' => $result
        ];
    }
    public function findByType($pet_type)
    {
        $result = $this->service->findByType($pet_type);
        return [
            'status' => 200,
            'data' => $result
        ];
    }
    public function findByPriceRange($min_price, $max_price)
    {
        $result = $this->service->findByPriceRange($min_price, $max_price);
        return [
            'status' => 200,
            'data' => $result
        ];
    }
}
?>