<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function customerGenerateCode(): string
{
    return 'CUS' . date('Ymd') . random_int(1000, 9999);
}

function customerGetById(mysqli $conn, int $customerId): ?array
{
    $stmt = $conn->prepare("SELECT id, customer_code, full_name, phone, email, status
                            FROM customers
                            WHERE id = ?
                            LIMIT 1");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $customerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function customerCurrent(mysqli $conn): ?array
{
    $customerId = isset($_SESSION['customer_id']) ? (int) $_SESSION['customer_id'] : 0;
    if ($customerId <= 0) {
        return null;
    }
    return customerGetById($conn, $customerId);
}

function customerRegister(mysqli $conn, string $fullName, string $phone, string $email, string $password): array
{
    $fullName = trim($fullName);
    $phone = trim($phone);
    $email = trim($email);

    if ($fullName === '' || $phone === '' || $password === '') {
        return ['ok' => false, 'message' => 'Vui lòng nhập đầy đủ họ tên, số điện thoại và mật khẩu.'];
    }

    $stmtExists = $conn->prepare("SELECT id FROM customers WHERE phone = ? OR (email = ? AND email IS NOT NULL)");
    if (!$stmtExists) {
        return ['ok' => false, 'message' => 'Không kiểm tra được tài khoản hiện có.'];
    }
    $emailParam = $email !== '' ? $email : null;
    $stmtExists->bind_param('ss', $phone, $emailParam);
    $stmtExists->execute();
    $exists = $stmtExists->get_result()->fetch_assoc();
    $stmtExists->close();
    if ($exists) {
        return ['ok' => false, 'message' => 'Số điện thoại hoặc email đã tồn tại.'];
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $customerCode = customerGenerateCode();
    $status = 'active';
    $stmt = $conn->prepare("INSERT INTO customers (customer_code, full_name, phone, email, password_hash, status)
                            VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        return ['ok' => false, 'message' => 'Không thể tạo tài khoản.'];
    }
    $stmt->bind_param('ssssss', $customerCode, $fullName, $phone, $emailParam, $passwordHash, $status);
    $stmt->execute();
    $customerId = (int) $stmt->insert_id;
    $stmt->close();

    $_SESSION['customer_id'] = $customerId;
    return ['ok' => true, 'message' => 'Đăng ký thành công.', 'customer_id' => $customerId];
}

function customerLogin(mysqli $conn, string $identifier, string $password): array
{
    $identifier = trim($identifier);
    if ($identifier === '' || $password === '') {
        return ['ok' => false, 'message' => 'Vui lòng nhập số điện thoại/email và mật khẩu.'];
    }

    $stmt = $conn->prepare("SELECT id, password_hash, status FROM customers WHERE phone = ? OR email = ? LIMIT 1");
    if (!$stmt) {
        return ['ok' => false, 'message' => 'Không thể đăng nhập lúc này.'];
    }
    $stmt->bind_param('ss', $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row || !$row['password_hash'] || !password_verify($password, $row['password_hash'])) {
        return ['ok' => false, 'message' => 'Thông tin đăng nhập không chính xác.'];
    }
    if ($row['status'] !== 'active') {
        return ['ok' => false, 'message' => 'Tài khoản hiện không khả dụng.'];
    }

    $_SESSION['customer_id'] = (int) $row['id'];
    return ['ok' => true, 'message' => 'Đăng nhập thành công.'];
}

function customerLogout(): void
{
    unset($_SESSION['customer_id']);
}
