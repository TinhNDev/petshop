<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/product.model.php';

class ProductService
{
    private $productModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->getConnection();
        $this->productModel = new Product($db);
    }

    public function createProduct($data)
    {
        return $this->productModel->create(
            $data['product_name'],
            $data['category_id'],
            $data['description'],
            $data['pet_type'],
            $data['base_price'],
            $data['discount_price'],
            $data['stock'],
            $data['image_url'],
            $data['is_active']
        );
    }

    public function updateProduct($id, $data)
    {
        return $this->productModel->update(
            $id,
            $data['product_name'],
            $data['category_id'],
            $data['description'],
            $data['pet_type'],
            $data['base_price'],
            $data['discount_price'],
            $data['stock'],
            $data['image_url'],
            $data['is_active']
        );
    }

    public function getDetailProducts($id)
    {
        return $this->productModel->findOne($id);
    }

    public function getAllProducts()
    {
        return $this->productModel->findAll();
    }

    public function findByType($pet_type)
    {
        return $this->productModel->findByType($pet_type);
    }

    public function findByPriceRange($min_price, $max_price)
    {
        return $this->productModel->findByPriceRange($min_price, $max_price);
    }
}
?>