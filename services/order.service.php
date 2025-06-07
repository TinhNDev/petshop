<?php
require_once __DIR__ . '/../model/order.model.php';
require_once __DIR__ . '/../model/promotion.model.php';
require_once __DIR__ . '/../model/product.model.php';

class OrderService
{
    private $orderModel;
    private $promotionModel;
    private $productModel;

    public function __construct($db)
    {
        $this->orderModel = new Order($db);
        $this->promotionModel = new Promotion($db);
        $this->productModel = new Product($db);
    }

    private function applyPromotion($promotion_id, $total_amount)
    {
        if (!$promotion_id)
            return ['valid' => true, 'final_total' => $total_amount];

        $promo = $this->promotionModel->findOne($promotion_id);
        if (!$promo)
            return ['valid' => false, 'message' => 'Mã giảm giá không tồn tại'];
        if (!$promo['is_active'])
            return ['valid' => false, 'message' => 'Mã giảm giá không còn hiệu lực'];
        if ($promo['used_voucher'] >= $promo['total_voucher'])
            return ['valid' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng'];

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

    /**
     * Tạo đơn hàng từ giỏ hàng
     * 
     * @param array $data Thông tin đơn hàng
     * @param array $cart_items Các sản phẩm trong giỏ hàng
     * @return array Kết quả tạo đơn hàng
     */
    public function createOrderFromCart($data, $cart_items)
    {
        try {
            // Kiểm tra phương thức thanh toán
            if (empty($data['payment_method'])) {
                return ['success' => false, 'message' => 'Vui lòng chọn hình thức thanh toán'];
            }
    
            if (empty($data['address_id'])) {
                return ['success' => false, 'message' => 'Vui lòng chọn địa chỉ giao hàng'];
            }
    
            // Kiểm tra thông tin khách hàng
            if (empty($data['customer_id'])) {
                return ['success' => false, 'message' => 'Thiếu thông tin khách hàng'];
            }
    
            // Kiểm tra sản phẩm trong giỏ
            if (empty($cart_items)) {
                return ['success' => false, 'message' => 'Giỏ hàng trống'];
            }
    
            // Thiết lập trạng thái mặc định
            if (empty($data['status'])) {
                $data['status'] = 'pending';
            }
    
            // Thiết lập trạng thái thanh toán mặc định
            if (empty($data['payment_status'])) {
                $data['payment_status'] = 'pending';
            }
    
            // Tính tổng tiền của giỏ hàng và kiểm tra tồn kho
            $total_amount = 0;
            $valid_items = [];
            
            foreach ($cart_items as $item) {
                if (empty($item['product_id']) || empty($item['quantity'])) {
                    return ['success' => false, 'message' => 'Thông tin sản phẩm không hợp lệ'];
                }
    
                $product = $this->productModel->findOne($item['product_id']);
                
                if (!$product) {
                    return ['success' => false, 'message' => 'Sản phẩm không tồn tại'];
                }
                
                if ($item['quantity'] > $product['stock']) {
                    return [
                        'success' => false,
                        'message' => "Sản phẩm {$product['product_name']} chỉ còn {$product['stock']} sản phẩm trong kho"
                    ];
                }
                
                // Tính tiền sản phẩm
                $price = isset($product['price']) ? $product['price'] : 
                       (isset($product['base_price']) ? $product['base_price'] : 0);
                       
                if ($price <= 0) {
                    return [
                        'success' => false,
                        'message' => "Lỗi: Giá sản phẩm {$product['product_name']} không hợp lệ"
                    ];
                }
                
                $item_total = $price * $item['quantity'];
                $total_amount += $item_total;
                
                // Lưu thông tin sản phẩm đã xác thực
                $valid_items[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $price
                ];
            }
            
            // Cập nhật tổng tiền vào dữ liệu đơn hàng
            $data['total_amount'] = $total_amount;
    
            // Xử lý khuyến mãi nếu có
            if (!empty($data['promotion_id'])) {
                $promotionResult = $this->applyPromotion(
                    $data['promotion_id'],
                    $total_amount
                );
                
                // Kiểm tra khuyến mãi có hợp lệ không
                if (!$promotionResult['valid']) {
                    return ['success' => false, 'message' => $promotionResult['message']];
                }
                
                // Cập nhật tổng tiền sau khi áp dụng khuyến mãi
                if (isset($promotionResult['final_total']) && is_numeric($promotionResult['final_total'])) {
                    $data['total_amount'] = $promotionResult['final_total'];
                }
            }
    
            // Tạo đơn hàng
            $order_id = $this->orderModel->createOrderFromCart($data, $valid_items);
    
            if (!$order_id) {
                return ['success' => false, 'message' => 'Tạo đơn hàng thất bại'];
            }
    
            // Cập nhật số lượng sử dụng voucher nếu có
            if (!empty($data['promotion_id']) && isset($promotionResult['promotion_id'])) {
                $this->promotionModel->incrementUsedVoucher($promotionResult['promotion_id']);
            }
            
            // Cập nhật số lượng sản phẩm trong kho
            foreach ($valid_items as $item) {
                $product = $this->productModel->findOne($item['product_id']);
                if ($product) {
                    $new_stock = $product['stock'] - $item['quantity'];
                    $this->productModel->updateStock($item['product_id'], $new_stock);
                }
            }
    
            return [
                'success' => true, 
                'order_id' => $order_id,
                'message' => 'Đặt hàng thành công'
            ];
        } catch (Exception $e) {
            error_log("Error in createOrderFromCart: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return ['success' => false, 'message' => 'Đã xảy ra lỗi khi xử lý đơn hàng: ' . $e->getMessage()];
        }
    }

    public function buyNow($data, $product)
    {
        try {
            if (empty($data['payment_method'])) {
                return ['success' => false, 'message' => 'Vui lòng chọn hình thức thanh toán'];
            }
    
            // Kiểm tra sản phẩm
            $productInfo = $this->productModel->findOne($product['product_id']);
            if (!$productInfo) {
                return ['success' => false, 'message' => 'Sản phẩm không tồn tại'];
            }
            
            // Kiểm tra tồn kho
            if ($product['quantity'] > $productInfo['stock']) {
                return [
                    'success' => false,
                    'message' => "Sản phẩm {$productInfo['product_name']} chỉ còn {$productInfo['stock']} sản phẩm trong kho"
                ];
            }
    
            // Thiết lập trạng thái mặc định
            if (empty($data['status'])) {
                $data['status'] = 'pending';
            }
            
            // Tính tổng tiền ban đầu
            $initial_total = $productInfo['base_price'] * $product['quantity'];
            
            // Đảm bảo total_amount luôn có giá trị
            $data['total_amount'] = $initial_total;
    
            // Xử lý khuyến mãi nếu có
            if (!empty($data['promotion_id'])) {
                $promotionResult = $this->applyPromotion(
                    $data['promotion_id'],
                    $initial_total
                );
                
                // Kiểm tra khuyến mãi có hợp lệ không
                if (!$promotionResult['valid']) {
                    return ['success' => false, 'message' => $promotionResult['message']];
                }
                
                // Cập nhật tổng tiền sau khi áp dụng khuyến mãi
                if (isset($promotionResult['final_total']) && is_numeric($promotionResult['final_total'])) {
                    $data['total_amount'] = $promotionResult['final_total'];
                }
            }
    
            // Thêm payment_status nếu chưa có
            if (!isset($data['payment_status'])) {
                $data['payment_status'] = 'pending';
            }
    
            // Tạo đơn hàng
            $order_id = $this->orderModel->buyNow($data, $product);
    
            // Cập nhật số lượng sử dụng voucher
            if ($order_id && !empty($data['promotion_id']) && isset($promotionResult['promotion_id'])) {
                $this->promotionModel->incrementUsedVoucher($promotionResult['promotion_id']);
            }
    
            return $order_id ? 
                ['success' => true, 'order_id' => $order_id, 'message' => 'Đặt hàng thành công'] :
                ['success' => false, 'message' => 'Tạo đơn hàng thất bại'];
        } catch (Exception $e) {
            error_log("Error in buyNow: " . $e->getMessage());
            return ['success' => false, 'message' => 'Đã xảy ra lỗi khi xử lý đơn hàng'];
        }
    }

    public function getAllOrdersByCustomer($customer_id)
    {
        return $this->orderModel->getAllOrdersByCustomer($customer_id);
    }

    public function getOrderDetail($order_id)
    {
        return $this->orderModel->getOrderDetail($order_id);
    }

    public function countOrders()
    {
        return $this->orderModel->countOrders();
    }

    public function countOrdersByStatus()
    {
        return $this->orderModel->countOrdersByStatus();
    }

    public function revenueThisMonth()
    {
        return $this->orderModel->revenueThisMonth();
    }

    public function countActivePromotions()
    {
        return $this->orderModel->countActivePromotions();
    }

    public function countProducts()
    {
        return $this->orderModel->countProducts();
    }

    public function countCustomers()
    {
        return $this->orderModel->countCustomers();
    }

    public function getAllOrders()
    {
        return $this->orderModel->getAllOrders();
    }
    public function filterOrders($filters = [])
    {
        return $this->orderModel->filterOrders($filters);
    }
}