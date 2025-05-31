<?php
function requireLogin($roles = null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['message' => 'Bạn chưa đăng nhập!']);
        exit();
    }
    if ($roles) {
        $userRole = $_SESSION['role'] ?? '';
        if (is_array($roles)) {
            if (!in_array($userRole, $roles)) {
                http_response_code(403);
                echo json_encode(['message' => 'Bạn không có quyền truy cập chức năng này!']);
                exit();
            }
        } else {
            if ($userRole !== $roles) {
                http_response_code(403);
                echo json_encode(['message' => 'Bạn không có quyền truy cập chức năng này!']);
                exit();
            }
        }
    }
}