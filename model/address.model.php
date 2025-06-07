<?php
class Address {
    private $conn;
    private $table_name = "address";
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function getByCustomer($customer_id) {
        $query = "SELECT * FROM {$this->table_name} WHERE customer_id = :customer_id ORDER BY is_default DESC, created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':customer_id', $customer_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($address_id) {
        $query = "SELECT * FROM {$this->table_name} WHERE address_id = :address_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':address_id', $address_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function add($data) {
        $query = "INSERT INTO {$this->table_name} 
                 (address_id, customer_id, address_line, ward_id, is_default, created_at)
                 VALUES (:address_id, :customer_id, :address_line, :ward_id, :is_default, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':address_id', $data['address_id']);
        $stmt->bindParam(':customer_id', $data['customer_id']);
        $stmt->bindParam(':address_line', $data['address_line']);
        $stmt->bindParam(':ward_id', $data['ward_id']);
        $stmt->bindParam(':is_default', $data['is_default']);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    public function update($address_id, $data) {
        $set_parts = [];
        $params = [':address_id' => $address_id];
        
        if(isset($data['address_line'])) {
            $set_parts[] = "address_line = :address_line";
            $params[':address_line'] = $data['address_line'];
        }
        
        if(isset($data['ward_id'])) {
            $set_parts[] = "ward_id = :ward_id";
            $params[':ward_id'] = $data['ward_id'];
        }
        
        if(isset($data['is_default'])) {
            $set_parts[] = "is_default = :is_default";
            $params[':is_default'] = $data['is_default'];
        }
        
        if(empty($set_parts)) {
            return true; // Không có gì để cập nhật
        }
        
        $query = "UPDATE {$this->table_name} SET " . implode(", ", $set_parts) . " WHERE address_id = :address_id";
        
        $stmt = $this->conn->prepare($query);
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        return $stmt->execute();
    }
    
    public function resetDefaultAddresses($customer_id) {
        $query = "UPDATE {$this->table_name} SET is_default = 0 WHERE customer_id = :customer_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':customer_id', $customer_id);
        return $stmt->execute();
    }
    
    public function delete($address_id) {
        $query = "DELETE FROM {$this->table_name} WHERE address_id = :address_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':address_id', $address_id);
        return $stmt->execute();
    }
    
    public function getDefaultAddress($customer_id) {
        $query = "SELECT * FROM {$this->table_name} WHERE customer_id = :customer_id AND is_default = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':customer_id', $customer_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>