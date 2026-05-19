<?php
require_once __DIR__ . '/admin-auth.php';

if (adminCurrent()) {
    header('Location: index.php?view=admin-dashboard');
    exit;
}

$_SESSION['auth_flash'] = [
    'message' => 'Admin đăng nhập chung với khách: dùng tài khoản admin / admin123 tại form Đăng nhập trên trang chủ.',
    'success' => false,
    'open' => 'login',
    'prefill' => ['identifier' => 'admin'],
];
header('Location: index.php?view=home');
exit;
