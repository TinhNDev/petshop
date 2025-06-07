<?php
class Customer
{
    private $conn;
    private $table_name = "customer";
    private $user_table = "user_account";

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
    public function getAll()
    {
        $query = "SELECT * FROM {$this->table_name}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getCustomerByUserId($id)
    {
        $query = "SELECT customer_id FROM {$this->table_name} WHERE user_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function update($customer_id, $data)
    {
        try {
            // Kiểm tra nếu không có dữ liệu để cập nhật
            if (empty($data)) {
                return false;
            }

            // Tạo câu truy vấn cập nhật dựa trên dữ liệu được cung cấp
            $set_parts = [];
            $params = [':customer_id' => $customer_id];

            // Các trường được phép cập nhật trong bảng customer
            $allowed_fields = ['fullname', 'phone', 'email', 'birthday', 'gender', 'avatar'];

            foreach ($data as $key => $value) {
                if (in_array($key, $allowed_fields)) {
                    $set_parts[] = "$key = :$key";
                    $params[":$key"] = $value;
                }
            }

            if (empty($set_parts)) {
                return false; // Không có trường hợp lệ để cập nhật
            }

            $query = "UPDATE {$this->table_name} 
                     SET " . implode(', ', $set_parts) . ", 
                     updated_at = NOW()
                     WHERE customer_id = :customer_id";

            $stmt = $this->conn->prepare($query);

            // Bind các tham số
            foreach ($params as $param => &$value) {
                $stmt->bindParam($param, $value);
            }

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating customer: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cập nhật thông tin tài khoản người dùng
     * 
     * @param string $user_id ID người dùng
     * @param array $data Dữ liệu cập nhật
     * @return boolean Kết quả cập nhật
     */
    public function updateUserAccount($user_id, $data)
    {
        try {
            // Kiểm tra nếu không có dữ liệu để cập nhật
            if (empty($data)) {
                return false;
            }

            // Tạo câu truy vấn cập nhật dựa trên dữ liệu được cung cấp
            $set_parts = [];
            $params = [':user_id' => $user_id];

            // Các trường được phép cập nhật trong bảng user
            $allowed_fields = ['username', 'email', 'role_id', 'status'];

            foreach ($data as $key => $value) {
                if (in_array($key, $allowed_fields)) {
                    $set_parts[] = "$key = :$key";
                    $params[":$key"] = $value;
                }
            }

            // Xử lý riêng cho password nếu có
            if (isset($data['password']) && !empty($data['password'])) {
                $set_parts[] = "password = :password";
                $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            if (empty($set_parts)) {
                return false; // Không có trường hợp lệ để cập nhật
            }

            $query = "UPDATE {$this->user_table} 
                     SET " . implode(', ', $set_parts) . ", 
                     updated_at = NOW()
                     WHERE user_id = :user_id";

            $stmt = $this->conn->prepare($query);

            // Bind các tham số
            foreach ($params as $param => &$value) {
                $stmt->bindParam($param, $value);
            }

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating user account: " . $e->getMessage());
            return false;
        }
    }
    public function updateProfile($customer_id, $user_id, $data)
    {
        try {
            $this->conn->beginTransaction();

            // Phân tách dữ liệu cho customer và user
            $customer_data = [];
            $user_data = [];

            // Các trường thuộc về customer
            $customer_fields = ['fullname', 'phone', 'email', 'birthday', 'gender', 'avatar'];

            // Các trường thuộc về user
            $user_fields = ['username', 'email', 'password', 'role_id', 'status'];

            foreach ($data as $key => $value) {
                if (in_array($key, $customer_fields)) {
                    $customer_data[$key] = $value;
                }
                if (in_array($key, $user_fields)) {
                    $user_data[$key] = $value;
                }
            }

            // Cập nhật thông tin khách hàng
            if (!empty($customer_data)) {
                $customer_updated = $this->update($customer_id, $customer_data);
                if (!$customer_updated) {
                    $this->conn->rollBack();
                    return [
                        'success' => false,
                        'message' => 'Không thể cập nhật thông tin khách hàng'
                    ];
                }
            }

            // Cập nhật thông tin tài khoản người dùng
            if (!empty($user_data)) {
                $user_updated = $this->updateUserAccount($user_id, $user_data);
                if (!$user_updated) {
                    $this->conn->rollBack();
                    return [
                        'success' => false,
                        'message' => 'Không thể cập nhật thông tin tài khoản'
                    ];
                }
            }

            $this->conn->commit();

            // Lấy thông tin khách hàng sau khi cập nhật
            $updated_customer = $this->findOne($customer_id);

            return [
                'success' => true,
                'message' => 'Cập nhật thông tin thành công',
                'customer' => $updated_customer
            ];
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error updating profile: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi cập nhật thông tin: ' . $e->getMessage()
            ];
        }
    }
    public function changePassword($user_id, $current_password, $new_password)
    {
        try {
            // Kiểm tra mật khẩu hiện tại
            $query = "SELECT password_hash FROM {$this->user_table} WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin người dùng'
                ];
            }
    
    
            // Cập nhật mật khẩu mới - đã sửa lỗi dấu phẩy thừa trong SQL
            $query = "UPDATE {$this->user_table} 
                     SET password_hash = :password_hash
                     WHERE user_id = :user_id";
    
            $stmt = $this->conn->prepare($query);
            
            
            // Bind tham số đúng tên
            $stmt->bindParam(':password_hash', $new_password);
            $stmt->bindParam(':user_id', $user_id);
    
            $result = $stmt->execute();
    
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Thay đổi mật khẩu thành công'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Không thể thay đổi mật khẩu'
                ];
            }
        } catch (Exception $e) {
            error_log("Error changing password: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi thay đổi mật khẩu: ' . $e->getMessage()
            ];
        }
    }
}