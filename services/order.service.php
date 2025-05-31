<?php
require_once __DIR__ . '/../model/order.model.php';
require_once __DIR__ . '/../model/promotion.model.php';
class OrderService
{
    private $orderModel;
    private $promotionModel;

    public function __construct($db)
    {
        $this->orderModel = new Order($db);
        $this->promotionModel = new Promotion($db);
    }

    private function applyPromotion($promotion_id, $total_amount)
    {
        if (!$promotion_id) return ['valid' => true, 'final_total' => $total_amount];

        $promo = $this->promotionModel->findOne($promotion_id);
        if (!$promo) return ['valid' => false, 'message' => 'Mã giảm giá không tồn tại'];
        if (!$promo['is_active']) return ['valid' => false, 'message' => 'Mã giảm giá không còn hiệu lực'];
        if ($promo['used_voucher'] >= $promo['total_voucher']) return ['valid' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng'];

        $now = date('Y-m-d');
        if ($now < $promo['start_date'] || $now > $promo['end_date']) {
            return ['valid' => false, 'message' => 'Mã giảm giá hết hạn sử dụng'];
        }

        $discount_percent = floatval($promo['discount_percent']);
        $discount_amount = $total_amount * ($discount_percent / 100);
        $final_total = $total_amount - $discount_amount;

        return [
            'valid' => true,
            'final_total' => round($final_total, 2),
            'promotion_id' => $promotion_id
        ];
    }

    public function createOrderFromCart($data, $cart_items)
    {
        $promotionResult = $this->applyPromotion($data['promotion_id'], $data['total_amount']);

        if (!$promotionResult['valid']) {
            return ['success' => false, 'message' => $promotionResult['message']];
        }

        $data['total_amount'] = $promotionResult['final_total'];

        $order_id = $this->orderModel->createOrderFromCart($data, $cart_items);

        if ($order_id && isset($promotionResult['promotion_id'])) {
            $this->promotionModel->incrementUsedVoucher($promotionResult['promotion_id']);
        }

        return $order_id ? ['success' => true, 'order_id' => $order_id] :
                           ['success' => false, 'message' => 'Tạo đơn hàng thất bại'];
    }

    public function buyNow($data, $product)
    {
        $promotionResult = $this->applyPromotion($data['promotion_id'], $data['total_amount']);

        if (!$promotionResult['valid']) {
            return ['success' => false, 'message' => $promotionResult['message']];
        }

        $data['total_amount'] = $promotionResult['final_total'];

        $order_id = $this->orderModel->buyNow($data, $product);

        if ($order_id && isset($promotionResult['promotion_id'])) {
            $this->promotionModel->incrementUsedVoucher($promotionResult['promotion_id']);
        }

        return $order_id ? ['success' => true, 'order_id' => $order_id] :
                           ['success' => false, 'message' => 'Tạo đơn hàng thất bại'];
    }

    public function getAllOrdersByCustomer($customer_id)
    {
        return $this->orderModel->getAllOrdersByCustomer($customer_id);
    }

    public function getOrderDetail($order_id)
    {
        return $this->orderModel->getOrderDetail($order_id);
    }
}
