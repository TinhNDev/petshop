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
        $query = "SELECT * FROM `order` WHERE customer_id = :customer_id ORDER BY order_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':customer_id', $customer_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getOrderDetail($order_id)
    {
        $query = "SELECT o.*, od.order_detail_id, od.product_id, od.quantity, od.unit_price, p.product_name
                  FROM `order` o
                  JOIN order_detail od ON od.order_id = o.order_id
                  JOIN product p ON p.product_id = od.product_id
                  WHERE o.order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}