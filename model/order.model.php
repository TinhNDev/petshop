<?php
class Order
{
    private $conn;
    private $table_name = "order";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    private function generateID()
    {
        return strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
    }

    public function createOrderFromCart($data, $cart_items)
    {
        $order_id = $this->generateID();
        $order_date = date('Y-m-d H:i:s');

        $query = "INSERT INTO `order` (
            order_id, customer_id, address_id, promotion_id, order_date, status, total_amount, payment_method, payment_status
        ) VALUES (
            :order_id, :customer_id, :address_id, :promotion_id, :order_date, :status, :total_amount, :payment_method, :payment_status
        )";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->bindParam(':customer_id', $data['customer_id']);
        $stmt->bindParam(':address_id', $data['address_id']);
        $stmt->bindParam(':promotion_id', $data['promotion_id']);
        $stmt->bindParam(':order_date', $order_date);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':total_amount', $data['total_amount']);
        $stmt->bindParam(':payment_method', $data['payment_method']);
        $stmt->bindParam(':payment_status', $data['payment_status']);

        if ($stmt->execute()) {
            foreach ($cart_items as $item) {
                $order_detail_id = $this->generateID();
                $detailQuery = "INSERT INTO order_detail (
                    order_detail_id, order_id, product_id, quantity, unit_price
                ) VALUES (
                    :order_detail_id, :order_id, :product_id, :quantity, :unit_price
                )";
                $detailStmt = $this->conn->prepare($detailQuery);
                $detailStmt->bindParam(':order_detail_id', $order_detail_id);
                $detailStmt->bindParam(':order_id', $order_id);
                $detailStmt->bindParam(':product_id', $item['product_id']);
                $detailStmt->bindParam(':quantity', $item['quantity']);
                $detailStmt->bindParam(':unit_price', $item['unit_price']);
                $detailStmt->execute();
            }
            return $order_id;
        }
        return false;
    }

    public function buyNow($data, $product)
    {
        $order_id = $this->generateID();
        $order_date = date('Y-m-d H:i:s');

        $query = "INSERT INTO `order` (
            order_id, customer_id, address_id, promotion_id, order_date, status, total_amount, payment_method, payment_status
        ) VALUES (
            :order_id, :customer_id, :address_id, :promotion_id, :order_date, :status, :total_amount, :payment_method, :payment_status
        )";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->bindParam(':customer_id', $data['customer_id']);
        $stmt->bindParam(':address_id', $data['address_id']);
        $stmt->bindParam(':promotion_id', $data['promotion_id']);
        $stmt->bindParam(':order_date', $order_date);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':total_amount', $data['total_amount']);
        $stmt->bindParam(':payment_method', $data['payment_method']);
        $stmt->bindParam(':payment_status', $data['payment_status']);

        if ($stmt->execute()) {
            $order_detail_id = $this->generateID();
            $detailQuery = "INSERT INTO order_detail (
                order_detail_id, order_id, product_id, quantity, unit_price
            ) VALUES (
                :order_detail_id, :order_id, :product_id, :quantity, :unit_price
            )";
            $detailStmt = $this->conn->prepare($detailQuery);
            $detailStmt->bindParam(':order_detail_id', $order_detail_id);
            $detailStmt->bindParam(':order_id', $order_id);
            $detailStmt->bindParam(':product_id', $product['product_id']);
            $detailStmt->bindParam(':quantity', $product['quantity']);
            $detailStmt->bindParam(':unit_price', $product['unit_price']);
            $detailStmt->execute();

            return $order_id;
        }
        return false;
    }
    public function getAllOrdersByCustomer($customer_id)
    {
        $query = "SELECT 
            o.order_id, 
            o.order_date, 
            o.status, 
            o.total_amount,
            o.payment_method, 
            o.payment_status,
            c.customer_name, 
            c.phone,
            a.address_line, 
            a.ward_id,
            COUNT(od.product_id) as total_items
          FROM `order` o
          JOIN customer c ON o.customer_id = c.customer_id
          JOIN address a ON o.address_id = a.address_id
          LEFT JOIN order_detail od ON o.order_id = od.order_id
          WHERE o.customer_id = :customer_id
          GROUP BY o.order_id, o.order_date, o.status, o.total_amount, 
                  o.payment_method, o.payment_status, c.customer_name, 
                  c.phone, a.address_line, a.ward_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':customer_id', $customer_id);
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($orders as &$order) {
            $detailQuery = "SELECT 
                          od.product_id,
                          od.quantity as count,
                          od.unit_price as price,
                          p.product_name as name,
                          p.stock as quantity,
                          p.image_url,
                          c.category_name as category
                        FROM order_detail od
                        JOIN product p ON od.product_id = p.product_id
                        LEFT JOIN category c ON p.category_id = c.category_id
                        WHERE od.order_id = :order_id";

            $detailStmt = $this->conn->prepare($detailQuery);
            $detailStmt->bindParam(':order_id', $order['order_id']);
            $detailStmt->execute();
            $items = $detailStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($items as &$item) {
                $item['price'] = number_format($item['price'], 0, ',', '.');
                $item['count'] = (string) $item['count'];
                $item['quantity'] = (string) $item['quantity'];
                $item['image'] = $item['image_url'];
                unset($item['image_url']);
            }

            $order['items'] = $items;
            $order['id'] = $order['order_id'];
            $order['status'] = $order['status'];
            $order['total'] = number_format($order['total_amount'], 0, ',', '.') . 'đ';

            if (!empty($items)) {
                $firstItem = $items[0];
                $order['image'] = $firstItem['image'];
                $order['name'] = $firstItem['name'];
                $order['category'] = $firstItem['category'];
                $order['quantity'] = $firstItem['quantity'];
                $order['count'] = $firstItem['count'];
                $order['price'] = $firstItem['price'];

                $numericPrice = (int) str_replace('.', '', $firstItem['price']);
                $numericCount = (int) $firstItem['count'];
                $order['total'] = number_format($numericPrice * $numericCount, 0, ',', '.') . 'đ';
            }
        }

        return $orders;
    }

    public function getOrderDetail($order_id)
    {
        $query = "SELECT o.*, od.order_detail_id, od.product_id, od.quantity, od.unit_price, 
                  p.product_name, p.image_url as image, c.category_name as category
                  FROM `order` o
                  JOIN order_detail od ON od.order_id = o.order_id
                  JOIN product p ON p.product_id = od.product_id
                  LEFT JOIN category c ON p.category_id = c.category_id
                  WHERE o.order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countOrders()
    {
        $query = "SELECT COUNT(*) as total FROM `order`";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function countOrdersByStatus()
    {
        $query = "SELECT status, COUNT(*) as total FROM `order` GROUP BY status";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function revenueThisMonth()
    {
        $query = "SELECT SUM(total_amount) as revenue FROM `order` 
                  WHERE status = 'completed' AND MONTH(order_date) = MONTH(CURDATE()) AND YEAR(order_date) = YEAR(CURDATE())";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['revenue'] ?? 0;
    }
    public function countActivePromotions()
    {
        $query = "SELECT COUNT(*) as total FROM promotion WHERE is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    public function countProducts()
    {
        $query = "SELECT COUNT(*) as total FROM product";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    public function countCustomers()
    {
        $query = "SELECT COUNT(*) as total FROM user_account WHERE role = 'user'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    public function getAllOrders()
    {
        $query = "SELECT * FROM `order` ORDER BY order_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function filterOrders($filters = [])
    {
        $query = "SELECT 
            o.order_id, 
            o.order_date, 
            o.status, 
            o.total_amount,
            o.payment_method, 
            o.payment_status,
            c.customer_name, 
            c.phone,
            a.address_line, 
            a.ward_id,
            COUNT(od.product_id) as total_items
          FROM `order` o
          JOIN customer c ON o.customer_id = c.customer_id
          JOIN address a ON o.address_id = a.address_id
          LEFT JOIN order_detail od ON o.order_id = od.order_id
          WHERE 1=1";
        
        $params = [];
        
        // Lọc theo payment_status
        if (isset($filters['payment_status']) && $filters['payment_status'] !== '') {
            $query .= " AND o.payment_status = :payment_status";
            $params[':payment_status'] = $filters['payment_status'];
        }
        
        // Lọc theo status
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query .= " AND o.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        // Lọc theo customer_id (nếu cần)
        if (isset($filters['customer_id']) && $filters['customer_id'] !== '') {
            $query .= " AND o.customer_id = :customer_id";
            $params[':customer_id'] = $filters['customer_id'];
        }
        
        // Lọc theo khoảng thời gian (nếu cần)
        if (isset($filters['start_date']) && $filters['start_date'] !== '') {
            $query .= " AND o.order_date >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        
        if (isset($filters['end_date']) && $filters['end_date'] !== '') {
            $query .= " AND o.order_date <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        
        $query .= " GROUP BY o.order_id, o.order_date, o.status, o.total_amount, 
                  o.payment_method, o.payment_status, c.customer_name, 
                  c.phone, a.address_line, a.ward_id
                  ORDER BY o.order_date DESC";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind các tham số
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($orders as &$order) {
            $detailQuery = "SELECT 
                          od.product_id,
                          od.quantity as count,
                          od.unit_price as price,
                          p.product_name as name,
                          p.stock as quantity,
                          p.image_url,
                          c.category_name as category
                        FROM order_detail od
                        JOIN product p ON od.product_id = p.product_id
                        LEFT JOIN category c ON p.category_id = c.category_id
                        WHERE od.order_id = :order_id";
    
            $detailStmt = $this->conn->prepare($detailQuery);
            $detailStmt->bindParam(':order_id', $order['order_id']);
            $detailStmt->execute();
            $items = $detailStmt->fetchAll(PDO::FETCH_ASSOC);
    
            foreach ($items as &$item) {
                $item['price'] = number_format($item['price'], 0, ',', '.');
                $item['count'] = (string) $item['count'];
                $item['quantity'] = (string) $item['quantity'];
                $item['image'] = $item['image_url'];
                unset($item['image_url']);
            }
    
            $order['items'] = $items;
            $order['id'] = $order['order_id'];
            $order['total'] = number_format($order['total_amount'], 0, ',', '.') . 'đ';
    
            if (!empty($items)) {
                $firstItem = $items[0];
                $order['image'] = $firstItem['image'];
                $order['name'] = $firstItem['name'];
                $order['category'] = $firstItem['category'];
                $order['quantity'] = $firstItem['quantity'];
                $order['count'] = $firstItem['count'];
                $order['price'] = $firstItem['price'];
    
                $numericPrice = (int) str_replace('.', '', $firstItem['price']);
                $numericCount = (int) $firstItem['count'];
                $order['total'] = number_format($numericPrice * $numericCount, 0, ',', '.') . 'đ';
            }
        }
        
        return $orders;
    }

}