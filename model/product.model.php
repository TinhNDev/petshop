<?php
class Product
{
    private $conn;
    private $table_name = "product";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    private function generateID()
    {
        return strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
    }

    public function create($product_name, $category_id, $description, $pet_type, $base_price, $discount_price, $stock, $image_url, $is_active)
    {
        $product_id = $this->generateID();
        $query = "INSERT INTO {$this->table_name} 
            (product_id, product_name, category_id, description, pet_type, base_price, discount_price, stock, image_url, is_active)
            VALUES (:product_id, :product_name, :category_id, :description, :pet_type, :base_price, :discount_price, :stock, :image_url, :is_active)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':product_name', $product_name);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':pet_type', $pet_type);
        $stmt->bindParam(':base_price', $base_price);
        $stmt->bindParam(':discount_price', $discount_price);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':is_active', $is_active);

        if ($stmt->execute()) {
            return [
                'product_id' => $product_id,
                'product_name' => $product_name,
                'category_id' => $category_id,
                'description' => $description,
                'pet_type' => $pet_type,
                'base_price' => $base_price,
                'discount_price' => $discount_price,
                'stock' => $stock,
                'image_url' => $image_url,
                'is_active' => $is_active,
            ];
        }
        return false;
    }

    public function update($id, $product_name, $category_id, $description, $pet_type, $base_price, $discount_price, $stock, $image_url, $is_active)
    {
        $query = "UPDATE {$this->table_name} SET 
            product_name = :product_name,
            category_id = :category_id,
            description = :description,
            pet_type = :pet_type,
            base_price = :base_price,
            discount_price = :discount_price,
            stock = :stock,
            image_url = :image_url,
            is_active = :is_active
            WHERE product_id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':product_name', $product_name);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':pet_type', $pet_type);
        $stmt->bindParam(':base_price', $base_price);
        $stmt->bindParam(':discount_price', $discount_price);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':is_active', $is_active);

        if ($stmt->execute()) {
            return [
                'product_id' => $id,
                'product_name' => $product_name,
                'category_id' => $category_id,
                'description' => $description,
                'pet_type' => $pet_type,
                'base_price' => $base_price,
                'discount_price' => $discount_price,
                'stock' => $stock,
                'image_url' => $image_url,
                'is_active' => $is_active,
            ];
        }
        return false;
    }

    public function findAll()
    {
        $query = "SELECT p.*, c.category_name 
                  FROM {$this->table_name} p
                  LEFT JOIN category c ON p.category_id = c.category_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findOne($id)
    {
        $query = "SELECT * FROM {$this->table_name} WHERE product_id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByType($pet_type, $page = 1, $limit = 10)
    {
        $offset = ($page - 1) * $limit;
        $query = "SELECT * FROM {$this->table_name} WHERE pet_type = :pet_type LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':pet_type', $pet_type);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByPriceRange($min_price, $max_price, $page = 1, $limit = 10)
    {
        $offset = ($page - 1) * $limit;
        $query = "SELECT * FROM {$this->table_name} WHERE base_price BETWEEN :min_price AND :max_price LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':min_price', $min_price);
        $stmt->bindParam(':max_price', $max_price);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
        public function updateStock($product_id, $stock)
{
    // Đảm bảo stock không âm
    $stock = max(0, $stock);
    
    $query = "UPDATE {$this->table_name} SET stock = :stock WHERE product_id = :product_id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':stock', $stock);
    $stmt->bindParam(':product_id', $product_id);
    
    return $stmt->execute();
}
/**
 * Lấy danh sách sản phẩm bán chạy nhất
 * 
 * @param int $limit Số lượng sản phẩm muốn lấy
 * @return array Danh sách sản phẩm
 */
/**
 * Lấy danh sách sản phẩm ngẫu nhiên
 * 
 * @param int $limit Số lượng sản phẩm muốn lấy
 * @return array Danh sách sản phẩm
 */
public function getBestSellingProducts($limit = 3)
{
    try {
        // Thay vì sắp xếp theo số lượng bán, chúng ta sắp xếp ngẫu nhiên
        $query = "SELECT p.product_id as id, p.product_name as name, 
                    CASE 
                        WHEN p.discount_price > 0 THEN p.discount_price 
                        ELSE p.base_price 
                    END as price,
                    p.image_url as image, 
                    c.category_name as category,
                    p.description,
                    p.stock
                  FROM {$this->table_name} p
                  LEFT JOIN category c ON p.category_id = c.category_id
                  WHERE p.is_active = 1 AND p.stock > 0
                  ORDER BY RAND() 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting random products: " . $e->getMessage());
        return [];
    }
}

/**
 * Kiểm tra sản phẩm có trong danh sách yêu thích của người dùng hay không
 * 
 * @param string $product_id ID sản phẩm
 * @param string $user_id ID người dùng
 * @return boolean
 */
public function isProductFavorited($product_id, $user_id)
{
    try {
        $query = "SELECT COUNT(*) FROM wishlist 
                  WHERE product_id = :product_id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return (int)$stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        error_log("Error checking favorite status: " . $e->getMessage());
        return false;
    }
}
}