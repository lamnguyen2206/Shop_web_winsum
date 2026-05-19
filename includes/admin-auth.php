<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function adminConfig(): array
{
    $path = __DIR__ . '/../config/admin.php';
    if (!is_file($path)) {
        return [];
    }
    $config = require $path;
    return is_array($config) ? $config : [];
}

function customerIsAdminRole(?array $customer): bool
{
    return $customer !== null && ($customer['role'] ?? 'customer') === 'admin';
}

function adminSyncSessionForCustomer(?array $customer): void
{
    if (customerIsAdminRole($customer)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = (string) ($customer['full_name'] ?? 'Admin');
        $_SESSION['customer_role'] = 'admin';
        return;
    }

    unset($_SESSION['admin_logged_in'], $_SESSION['admin_username'], $_SESSION['customer_role']);
}

function adminCurrent(): bool
{
    if (!empty($_SESSION['admin_logged_in']) || ($_SESSION['customer_role'] ?? '') === 'admin') {
        return true;
    }

    return false;
}

/**
 * @deprecated Đăng nhập admin qua form khách (customerLogin).
 */
function adminLogin(string $username, string $password): array
{
    return ['ok' => false, 'message' => 'Vui lòng đăng nhập bằng form tài khoản trên trang chủ.'];
}

function adminLogout(): void
{
    unset($_SESSION['admin_logged_in'], $_SESSION['admin_username'], $_SESSION['customer_role']);
}

function adminRequire(): void
{
    if (adminCurrent()) {
        return;
    }

    $_SESSION['auth_flash'] = [
        'message' => 'Vui lòng đăng nhập tài khoản quản trị.',
        'success' => false,
        'open' => 'login',
        'prefill' => [],
    ];
    header('Location: index.php?view=home');
    exit;
}
