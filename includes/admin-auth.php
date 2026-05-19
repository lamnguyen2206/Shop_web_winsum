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

function adminCurrent(): bool
{
    return !empty($_SESSION['admin_logged_in']);
}

function adminLogin(string $username, string $password): array
{
    $config = adminConfig();
    $expectedUser = (string) ($config['username'] ?? '');
    $passwordHash = (string) ($config['password_hash'] ?? '');

    if ($expectedUser === '' || $passwordHash === '') {
        return ['ok' => false, 'message' => 'Chưa cấu hình tài khoản admin. Tạo file config/admin.php từ config/admin.example.php.'];
    }

    if ($username !== $expectedUser || !password_verify($password, $passwordHash)) {
        return ['ok' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu admin không đúng.'];
    }

    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_username'] = $expectedUser;
    return ['ok' => true, 'message' => 'Đăng nhập admin thành công.'];
}

function adminLogout(): void
{
    unset($_SESSION['admin_logged_in'], $_SESSION['admin_username']);
}

function adminRequire(): void
{
    if (!adminCurrent()) {
        header('Location: index.php?view=admin-login&redirect=' . urlencode((string) ($_GET['view'] ?? 'admin-dashboard')));
        exit;
    }
}
