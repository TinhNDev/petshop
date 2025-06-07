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
    public function getBestSellingProducts($user_id = null, $limit = 3)
    {
        // Lấy danh sách sản phẩm bán chạy từ model
        $products = $this->productModel->getBestSellingProducts($limit);
        
        // Định dạng lại cho phù hợp với yêu cầu UI
        $formattedProducts = [];
        
        foreach ($products as $product) {
            // Kiểm tra sản phẩm có trong danh sách yêu thích của người dùng không
            $isFavorite = false;
            if ($user_id) {
                $isFavorite = $this->productModel->isProductFavorited($product['id'], $user_id);
            }
            
            // Định dạng giá về dạng số
            $price = (float) $product['price'];
            
            $formattedProducts[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $price,
                'image' => $product['image'],
                'isFavorite' => $isFavorite,
                'category' => $product['category'],
                'description' => $product['description'],
                'stock' => (int) $product['stock']
            ];
        }
        
        return $formattedProducts;
    }
}
?>