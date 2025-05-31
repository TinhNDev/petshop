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
        $query = "SELECT * FROM {$this->table_name}";
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
}