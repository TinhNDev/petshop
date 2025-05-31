<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/promotion.model.php';

class PromotionService
{
    private $promotionModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->getConnection();
        $this->promotionModel = new Promotion($db);
    }

    public function createPromotion($data)
    {
        return $this->promotionModel->create(
            $data['code'],
            $data['description'],
            $data['discount_percent'],
            $data['total_voucher'],
            $data['used_voucher'],
            $data['start_date'],
            $data['end_date'],
            $data['is_active']
        );
    }

    public function updatePromotion($id, $data)
    {
        return $this->promotionModel->update(
            $id,
            $data['code'],
            $data['description'],
            $data['discount_percent'],
            $data['total_voucher'],
            $data['used_voucher'],
            $data['start_date'],
            $data['end_date'],
            $data['is_active']
        );
    }

    public function getDetailPromotion($id)
    {
        return $this->promotionModel->findOne($id);
    }

    public function getAllPromotions()
    {
        return $this->promotionModel->findAll();
    }

    public function findByCode($code)
    {
        return $this->promotionModel->findByCode($code);
    }
}
?>