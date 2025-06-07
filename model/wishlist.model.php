<?php
class Wishlist
{
    private $conn;
    private $wishlist_table = "wishlist";
    private $wishlist_item_table = "wishlist_item";
public function __construct($db)
    {
        $this->conn = $db;
    }
    // Các phương thức hiện tại giữ nguyên
    private function generateID()
    {
        return strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
    }

    // Lấy wishlist_id cho khách hàng, tạo mới nếu chưa có
    private function getOrCreateWishlistId($customer_id)
    {
        // Kiểm tra xem khách hàng đã có wishlist chưa
        $query = "SELECT wishlist_id FROM {$this->wishlist_table} 
                  WHERE customer_id = :customer_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':customer_id', $customer_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Nếu có, trả về wishlist_id
        if ($result) {
            return $result['wishlist_id'];
        }
        
        // Nếu không, tạo mới
        $wishlist_id = $this->generateID();
        $created_at = date('Y-m-d H:i:s');
        
        try {
            $query = "INSERT INTO {$this->wishlist_table} 
                     (wishlist_id, customer_id, created_at)
                     VALUES (:wishlist_id, :customer_id, :created_at)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':wishlist_id', $wishlist_id);
            $stmt->bindParam(':customer_id', $customer_id);
            $stmt->bindParam(':created_at', $created_at);
            
            if ($stmt->execute()) {
                return $wishlist_id;
            }
        } catch (PDOException $e) {
            error_log("Database error in getOrCreateWishlistId: " . $e->getMessage());
        }
        
        return null;
    }

    public function add($customer_id, $product_id)
    {
        try {
            // Lấy hoặc tạo wishlist_id cho khách hàng
            $wishlist_id = $this->getOrCreateWishlistId($customer_id);
            
            if (!$wishlist_id) {
                return [
                    'success' => false,
                    'message' => 'Không thể tạo danh sách yêu thích'
                ];
            }
            
            // Kiểm tra xem sản phẩm đã có trong wishlist_item chưa
            if ($this->checkExists($customer_id, $product_id)) {
                return $this->remove($customer_id, $product_id);
            }

            $created_at = date('Y-m-d H:i:s');

            // Kiểm tra cấu trúc bảng wishlist_item trước khi thực hiện INSERT
            $tableCheck = "SHOW COLUMNS FROM {$this->wishlist_item_table}";
            $tableStmt = $this->conn->prepare($tableCheck);
            $tableStmt->execute();
            $columns = $tableStmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Xây dựng câu truy vấn dựa trên cấu trúc thực tế của bảng
            $fields = [];
            $values = [];
            $params = [];

            if (in_array('wishlist_id', $columns)) {
                $fields[] = 'wishlist_id';
                $values[] = ':wishlist_id';
                $params[':wishlist_id'] = $wishlist_id;
            }

            if (in_array('product_id', $columns)) {
                $fields[] = 'product_id';
                $values[] = ':product_id';
                $params[':product_id'] = $product_id;
            }

            if (in_array('created_at', $columns)) {
                $fields[] = 'created_at';
                $values[] = ':created_at';
                $params[':created_at'] = $created_at;
            }

            if (empty($fields)) {
                throw new Exception('Không có cột hợp lệ trong bảng wishlist_item');
            }

            $query = "INSERT INTO {$this->wishlist_item_table} 
                     (" . implode(', ', $fields) . ")
                     VALUES (" . implode(', ', $values) . ")";

            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Đã thêm vào danh sách yêu thích'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Không thể thêm vào danh sách yêu thích'
            ];
        } catch (PDOException $e) {
            error_log("Database error in add wishlist item: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            error_log("General error in add wishlist item: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ];
        }
    }
    // Thêm phương thức getByCustomer nếu chưa có
    public function getByCustomer($customer_id)
    {
        try {
            // Lấy wishlist_id của khách hàng
            $query = "SELECT wishlist_id FROM {$this->wishlist_table} 
                    WHERE customer_id = :customer_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':customer_id', $customer_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return []; // Khách hàng không có wishlist
            }
            
            $wishlist_id = $result['wishlist_id'];
            
            // Lấy các sản phẩm trong wishlist
            $query = "SELECT wi.product_id,
                    p.product_name, p.description, p.base_price, p.image_url as image, p.stock,
                    c.category_name
                    FROM {$this->wishlist_item_table} wi
                    JOIN product p ON wi.product_id = p.product_id
                    LEFT JOIN category c ON p.category_id = c.category_id
                    WHERE wi.wishlist_id = :wishlist_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':wishlist_id', $wishlist_id);
            $stmt->execute();

            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format giá và thêm các thông tin bổ sung
            foreach ($items as &$item) {
                $item['price_formatted'] = number_format($item['base_price'], 0, ',', '.') . 'đ';
            }

            return $items;
        } catch (PDOException $e) {
            error_log("Database error in getByCustomer: " . $e->getMessage());
            return [];
        }
    }

    // Thêm phương thức mới để phù hợp với service
    public function getWishlistByCustomer($customer_id)
    {
        return $this->getByCustomer($customer_id);
    }

    public function checkExists($customer_id, $product_id)
    {
        try {
            // Lấy wishlist_id của khách hàng
            $query = "SELECT wishlist_id FROM {$this->wishlist_table} 
                    WHERE customer_id = :customer_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':customer_id', $customer_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return false; // Khách hàng không có wishlist
            }
            
            $wishlist_id = $result['wishlist_id'];
            
            // Kiểm tra sản phẩm có trong wishlist không
            $query = "SELECT COUNT(*) FROM {$this->wishlist_item_table}
                    WHERE wishlist_id = :wishlist_id AND product_id = :product_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':wishlist_id', $wishlist_id);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Database error in checkExists: " . $e->getMessage());
            return false;
        }
    }
    
    public function countByCustomer($customer_id)
    {
        try {
            // Lấy wishlist_id của khách hàng
            $query = "SELECT wishlist_id FROM {$this->wishlist_table} 
                    WHERE customer_id = :customer_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':customer_id', $customer_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return 0; // Khách hàng không có wishlist
            }
            
            $wishlist_id = $result['wishlist_id'];
            
            // Đếm số sản phẩm trong wishlist
            $query = "SELECT COUNT(*) FROM {$this->wishlist_item_table}
                    WHERE wishlist_id = :wishlist_id";
                    
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':wishlist_id', $wishlist_id);
            $stmt->execute();
            
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Database error in countByCustomer: " . $e->getMessage());
            return 0;
        }
    }

    public function remove($customer_id, $product_id)
    {
        try {
            // Lấy wishlist_id của khách hàng
            $query = "SELECT wishlist_id FROM {$this->wishlist_table} 
                    WHERE customer_id = :customer_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':customer_id', $customer_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy danh sách yêu thích'
                ];
            }
            
            $wishlist_id = $result['wishlist_id'];
            
            // Xóa sản phẩm khỏi wishlist_item
            $query = "DELETE FROM {$this->wishlist_item_table} 
                    WHERE wishlist_id = :wishlist_id AND product_id = :product_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':wishlist_id', $wishlist_id);
            $stmt->bindParam(':product_id', $product_id);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Đã xóa khỏi danh sách yêu thích'
                ];
            }

            return [
                'success' => false,
                'message' => 'Không thể xóa khỏi danh sách yêu thích'
            ];
        } catch (PDOException $e) {
            error_log("Database error in remove: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi cơ sở dữ liệu khi xóa sản phẩm'
            ];
        }
    }
}
?>