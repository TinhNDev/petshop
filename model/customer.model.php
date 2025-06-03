<?php 
class Customer
{
    private $conn;
    private $table_name = "customer";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    private function generateID()
    {
        return strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
    }
    public function findOne($id)
    {
        $query = "SELECT * FROM {$this->table_name} WHERE customer_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}