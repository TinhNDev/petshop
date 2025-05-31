<?php
class Categories
{
    private $conn;
    private $table_name = "category";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Hàm sinh UUID v4
    private function generateID()
    {
        return strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
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
        $query = "SELECT * FROM {$this->table_name} WHERE category_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($category_name, $description)
    {
        $category_id = $this->generateID();
        $query = "INSERT INTO {$this->table_name} (category_id, category_name, description) 
                  VALUES (:category_id, :category_name, :description)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':category_name', $category_name);
        $stmt->bindParam(':description', $description);

        if ($stmt->execute()) {
            return [
                'category_id' => $category_id,
                'category_name' => $category_name,
                'description' => $description,
            ];
        }
        return false;
    }

    public function update($id, $category_name, $description)
    {
        $query = "UPDATE {$this->table_name} 
                  SET category_name = :category_name, description = :description
                  WHERE category_id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':category_name', $category_name);
        $stmt->bindParam(':description', $description);

        if ($stmt->execute()) {
            return [
                'category_id' => $id,
                'category_name' => $category_name,
                'description' => $description,
            ];
        }
        return false;
    }

    public function findProductsByCategory($category_id)
    {
        $query = "SELECT * FROM product WHERE category_id = :category_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}