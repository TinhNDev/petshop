<?php
require_once __DIR__ . '/../services/product.service.php';
require_once __DIR__ . '/../middleware/session.middleware.php';
class ProductController
{
    private $service;

    public function __construct()
    {
        $this->service = new ProductService();
    }


    public function create($data)
    {
        requireLogin('admin');
        $result = $this->service->createProduct($data);
        return [
            'status' => $result ? 201 : 400,
            'data' => ['success' => $result]
        ];
    }

    public function update($id, $data)
    {
        requireLogin('admin');
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
    public function getBestSellingProducts()
    {
        // Lấy user_id từ session nếu đã đăng nhập
        $user_id = null;
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
        }
        
        // Lấy limit từ query parameters nếu có
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 3;
        
        // Giới hạn số lượng sản phẩm tối đa
        if ($limit <= 0 || $limit > 20) {
            $limit = 3;
        }
        
        // Lấy danh sách sản phẩm bán chạy
        $products = $this->service->getBestSellingProducts($user_id, $limit);
        
        return [
            'status' => 200,
            'data' => [
                'success' => true,
                'products' => $products
            ]
        ];
    }
}
?>