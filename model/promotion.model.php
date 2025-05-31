<?php
class Promotion
{
    private $conn;
    private $table_name = "promotion";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    private function generateID()
    {
        return strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
    }

    public function create($code, $description, $discount_percent, $total_voucher, $used_voucher, $start_date, $end_date, $is_active)
    {
        $promotion_id = $this->generateID();
        $query = "INSERT INTO {$this->table_name} 
            (promotion_id, code, description, discount_percent, total_voucher, used_voucher, start_date, end_date, is_active)
            VALUES (:promotion_id, :code, :description, :discount_percent, :total_voucher, :used_voucher, :start_date, :end_date, :is_active)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':promotion_id', $promotion_id);
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':discount_percent', $discount_percent);
        $stmt->bindParam(':total_voucher', $total_voucher);
        $stmt->bindParam(':used_voucher', $used_voucher);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->bindParam(':is_active', $is_active);

        if ($stmt->execute()) {
            return [
                'promotion_id' => $promotion_id,
                'code' => $code,
                'description' => $description,
                'discount_percent' => $discount_percent,
                'total_voucher' => $total_voucher,
                'used_voucher' => $used_voucher,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'is_active' => $is_active,
            ];
        }
        return false;
    }
    public function update($id, $code, $description, $discount_percent, $total_voucher, $used_voucher, $start_date, $end_date, $is_active)
    {
        $query = "UPDATE {$this->table_name} 
            SET code = :code, description = :description, discount_percent = :discount_percent, 
                total_voucher = :total_voucher, used_voucher = :used_voucher, start_date = :start_date, 
                end_date = :end_date, is_active = :is_active 
            WHERE promotion_id = :promotion_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':promotion_id', $id);
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':discount_percent', $discount_percent);
        $stmt->bindParam(':total_voucher', $total_voucher);
        $stmt->bindParam(':used_voucher', $used_voucher);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->bindParam(':is_active', $is_active);

        if ($stmt->execute()) {
            return [
                'promotion_id' => $id,
                'code' => $code,
                'description' => $description,
                'discount_percent' => $discount_percent,
                'total_voucher' => $total_voucher,
                'used_voucher' => $used_voucher,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'is_active' => $is_active,
            ];
        }
        return false;
    }
    public function findOne($id)
    {
        $query = "SELECT * FROM {$this->table_name} WHERE promotion_id = :promotion_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':promotion_id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function findAll($page = 1, $limit = 10)
    {
        $offset = ($page - 1) * $limit;
        $query = "SELECT * FROM {$this->table_name} LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function findByCode($keyword)
    {
        $query = "SELECT * FROM {$this->table_name} WHERE code LIKE :keyword";
        $stmt = $this->conn->prepare($query);
        $likeKeyword = '%' . $keyword . '%';
        $stmt->bindParam(':keyword', $likeKeyword, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function validatePromotion($promotion_id)
    {
        $query = "SELECT * FROM promotion WHERE promotion_id = :promotion_id AND is_active = 1 AND start_date <= NOW() AND end_date >= NOW()";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':promotion_id', $promotion_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function incrementUsedVoucher($promotion_id)
    {
        $query = "UPDATE promotion SET used_voucher = used_voucher + 1 WHERE promotion_id = :promotion_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':promotion_id', $promotion_id);
        return $stmt->execute();
    }

}