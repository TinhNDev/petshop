<?php
require_once __DIR__ . '/../model/address.model.php';

class AddressService {
    private $addressModel;
    
    public function __construct($db) {
        $this->addressModel = new Address($db);
    }
    
    public function getAddressesByCustomer($customer_id) {
        return $this->addressModel->getByCustomer($customer_id);
    }
    
    public function getAddressById($address_id) {
        return $this->addressModel->getById($address_id);
    }
    
    public function getDefaultAddress($customer_id) {
        return $this->addressModel->getDefaultAddress($customer_id);
    }
    
    public function addAddress($data) {
        // Kiểm tra dữ liệu đầu vào
        if (empty($data['customer_id']) || empty($data['address_line']) || empty($data['ward_id'])) {
            return [
                'success' => false, 
                'message' => 'Thiếu thông tin địa chỉ cần thiết'
            ];
        }
        
        // Check max 3 địa chỉ
        $addresses = $this->addressModel->getByCustomer($data['customer_id']);
        if (count($addresses) >= 3) {
            return [
                'success' => false, 
                'message' => 'Chỉ được lưu tối đa 3 địa chỉ'
            ];
        }

        // Chuẩn hóa dữ liệu
        $data['address_id'] = $this->generateAddressId();
        $data['is_default'] = isset($data['is_default']) ? 
            (($data['is_default'] === true || $data['is_default'] === "true" || $data['is_default'] == 1) ? 1 : 0) : 
            (count($addresses) == 0 ? 1 : 0); // Mặc định đầu tiên là default
        
        // Nếu đây là địa chỉ mặc định, cập nhật các địa chỉ khác
        if ($data['is_default'] == 1) {
            $this->addressModel->resetDefaultAddresses($data['customer_id']);
        }
        
        // Thêm địa chỉ mới
        $result = $this->addressModel->add($data);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Thêm địa chỉ thành công',
                'address' => $this->addressModel->getById($data['address_id'])
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Không thể thêm địa chỉ'
        ];
    }

    public function updateAddress($address_id, $data) {
        // Kiểm tra địa chỉ tồn tại
        $address = $this->addressModel->getById($address_id);
        if (!$address) {
            return [
                'success' => false,
                'message' => 'Địa chỉ không tồn tại'
            ];
        }
        
        // Chuẩn hóa dữ liệu
        if (isset($data['is_default'])) {
            $data['is_default'] = ($data['is_default'] === true || $data['is_default'] === "true" || $data['is_default'] == 1) ? 1 : 0;
            
            // Nếu đây là địa chỉ mặc định, cập nhật các địa chỉ khác
            if ($data['is_default'] == 1) {
                $this->addressModel->resetDefaultAddresses($address['customer_id']);
            }
        }
        
        $result = $this->addressModel->update($address_id, $data);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Cập nhật địa chỉ thành công',
                'address' => $this->addressModel->getById($address_id)
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Không thể cập nhật địa chỉ'
        ];
    }

    public function deleteAddress($address_id) {
        // Kiểm tra địa chỉ tồn tại và không phải địa chỉ mặc định
        $address = $this->addressModel->getById($address_id);
        if (!$address) {
            return [
                'success' => false,
                'message' => 'Địa chỉ không tồn tại'
            ];
        }
        
        if ($address['is_default'] == 1) {
            return [
                'success' => false,
                'message' => 'Không thể xóa địa chỉ mặc định'
            ];
        }
        
        $result = $this->addressModel->delete($address_id);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Xóa địa chỉ thành công'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Không thể xóa địa chỉ'
        ];
    }
    
    public function setDefaultAddress($customer_id, $address_id) {
        // Kiểm tra địa chỉ tồn tại
        $address = $this->addressModel->getById($address_id);
        if (!$address || $address['customer_id'] != $customer_id) {
            return [
                'success' => false,
                'message' => 'Địa chỉ không tồn tại hoặc không thuộc về khách hàng này'
            ];
        }
        
        // Reset tất cả địa chỉ thành không mặc định
        $this->addressModel->resetDefaultAddresses($customer_id);
        
        // Đặt địa chỉ được chọn làm mặc định
        $result = $this->addressModel->update($address_id, ['is_default' => 1]);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Đã đặt địa chỉ mặc định'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Không thể đặt địa chỉ mặc định'
        ];
    }
    
    private function generateAddressId() {
        return 'AD' . strtoupper(substr(uniqid(), 0, 8));
    }
}
?>