<?php
require_once __DIR__ . '/../config/database.php';

function couponGetByCode(mysqli $conn, string $code): ?array
{
    $code = strtoupper(trim($code));
    if ($code === '') {
        return null;
    }

    $stmt = $conn->prepare("SELECT id, code, name, discount_type, discount_value, min_order_amount,
                                   max_discount_amount, total_usage_limit, per_customer_limit,
                                   starts_at, ends_at, is_active
                            FROM coupons
                            WHERE code = ?
                            LIMIT 1");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('s', $code);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function couponCountCustomerUses(mysqli $conn, int $couponId, ?int $customerId): int
{
    if ($customerId === null || $customerId <= 0) {
        return 0;
    }
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM coupon_redemptions WHERE coupon_id = ? AND customer_id = ?");
    if (!$stmt) {
        return 0;
    }
    $stmt->bind_param('ii', $couponId, $customerId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['total'] ?? 0);
}

function couponCountTotalUses(mysqli $conn, int $couponId): int
{
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM coupon_redemptions WHERE coupon_id = ?");
    if (!$stmt) {
        return 0;
    }
    $stmt->bind_param('i', $couponId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) ($row['total'] ?? 0);
}

function couponValidate(mysqli $conn, string $code, float $subtotal, ?int $customerId): array
{
    $coupon = couponGetByCode($conn, $code);
    if (!$coupon) {
        return ['ok' => false, 'message' => 'Mã giảm giá không tồn tại.'];
    }
    if ((int) $coupon['is_active'] !== 1) {
        return ['ok' => false, 'message' => 'Mã giảm giá không còn hiệu lực.'];
    }

    $now = time();
    if (!empty($coupon['starts_at']) && strtotime((string) $coupon['starts_at']) > $now) {
        return ['ok' => false, 'message' => 'Mã giảm giá chưa đến thời gian áp dụng.'];
    }
    if (!empty($coupon['ends_at']) && strtotime((string) $coupon['ends_at']) < $now) {
        return ['ok' => false, 'message' => 'Mã giảm giá đã hết hạn.'];
    }

    $minOrder = (float) ($coupon['min_order_amount'] ?? 0);
    if ($minOrder > 0 && $subtotal < $minOrder) {
        return ['ok' => false, 'message' => 'Đơn hàng chưa đạt giá trị tối thiểu để dùng mã này.'];
    }

    $couponId = (int) $coupon['id'];
    $perCustomerLimit = $coupon['per_customer_limit'] !== null ? (int) $coupon['per_customer_limit'] : null;
    if ($perCustomerLimit !== null && $customerId) {
        if (couponCountCustomerUses($conn, $couponId, $customerId) >= $perCustomerLimit) {
            return ['ok' => false, 'message' => 'Bạn đã sử dụng hết lượt cho mã giảm giá này.'];
        }
    }

    $totalLimit = $coupon['total_usage_limit'] !== null ? (int) $coupon['total_usage_limit'] : null;
    if ($totalLimit !== null && couponCountTotalUses($conn, $couponId) >= $totalLimit) {
        return ['ok' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng.'];
    }

    return ['ok' => true, 'message' => 'Áp mã thành công.', 'coupon' => $coupon];
}

function couponCalculateDiscount(array $coupon, float $subtotal, float $shipping): float
{
    $type = (string) $coupon['discount_type'];
    $value = (float) $coupon['discount_value'];
    $discount = 0.0;

    if ($type === 'fixed') {
        $discount = $value;
    } elseif ($type === 'percent') {
        $discount = $subtotal * ($value / 100);
        $maxDiscount = $coupon['max_discount_amount'] !== null ? (float) $coupon['max_discount_amount'] : null;
        if ($maxDiscount !== null) {
            $discount = min($discount, $maxDiscount);
        }
    } elseif ($type === 'shipping') {
        $discount = min($shipping, $value > 0 ? $value : $shipping);
    }

    return max(0, min($discount, $subtotal + $shipping));
}

function couponRecordRedemption(mysqli $conn, int $couponId, ?int $customerId, int $orderId): void
{
    $stmt = $conn->prepare("INSERT INTO coupon_redemptions (coupon_id, customer_id, order_id) VALUES (?, ?, ?)");
    if (!$stmt) {
        return;
    }
    $customerIdValue = $customerId ?: null;
    $stmt->bind_param('iii', $couponId, $customerIdValue, $orderId);
    $stmt->execute();
    $stmt->close();
}
