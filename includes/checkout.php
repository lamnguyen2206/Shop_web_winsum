<?php
require_once __DIR__ . '/cart-store.php';
require_once __DIR__ . '/order-repository.php';

$checkoutBlockedAdmin = adminCurrent();
$orderPlaced = false;
$orderMessage = '';
$orderCode = '';

if (!empty($_SESSION['checkout_result']) && is_array($_SESSION['checkout_result'])) {
    $checkoutResult = $_SESSION['checkout_result'];
    unset($_SESSION['checkout_result']);
    $orderPlaced = !empty($checkoutResult['placed']);
    $orderMessage = (string) ($checkoutResult['message'] ?? '');
    $orderCode = (string) ($checkoutResult['code'] ?? '');
}

$shippingMethods = orderGetShippingMethods($conn);
$paymentMethods = orderGetPaymentMethods($conn);
$currentCustomer = customerCurrent($conn);
$customerId = $currentCustomer ? (int) $currentCustomer['id'] : null;

$defaultShippingId = !empty($shippingMethods) ? (int) $shippingMethods[0]['id'] : 0;
$defaultPaymentId = !empty($paymentMethods) ? (int) $paymentMethods[0]['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedShippingId = (int) ($_POST['shipping_method_id'] ?? 0);
    $selectedPaymentId = (int) ($_POST['payment_method_id'] ?? 0);
} else {
    $selectedShippingId = (int) ($_SESSION['checkout_shipping_method_id'] ?? $defaultShippingId);
    $selectedPaymentId = (int) ($_SESSION['checkout_payment_method_id'] ?? $defaultPaymentId);
}

if ($selectedShippingId > 0) {
    orderApplyShippingToSession($shippingMethods, $selectedShippingId);
} elseif ($defaultShippingId > 0) {
    orderApplyShippingToSession($shippingMethods, $defaultShippingId);
    $selectedShippingId = $defaultShippingId;
}

if ($selectedPaymentId > 0) {
    $_SESSION['checkout_payment_method_id'] = $selectedPaymentId;
} elseif ($defaultPaymentId > 0) {
    $selectedPaymentId = $defaultPaymentId;
    $_SESSION['checkout_payment_method_id'] = $defaultPaymentId;
}

cartSyncPricesFromDb($conn);
$cartItems = cartGetItems();
$totals = cartCalculateTotals($cartItems, $conn, $customerId);
$cartIsEmpty = $cartItems === [];

$checkoutCanSubmit = !$checkoutBlockedAdmin
    && !$cartIsEmpty
    && !$orderPlaced
    && !empty($shippingMethods)
    && !empty($paymentMethods);
?>

<section class="container checkout-page">
    <p class="breadcrumb"><a href="<?php echo e(app_url('home')); ?>">Trang chủ</a> / <a href="<?php echo e(app_url('cart')); ?>">Giỏ hàng</a> / <span>Thanh toán</span></p>
    <h1>Thông tin thanh toán</h1>

    <?php if ($checkoutBlockedAdmin): ?>
        <p class="checkout-notice error">Tài khoản quản trị không thể đặt hàng qua website. Vui lòng đăng xuất quản trị nếu bạn muốn mua với tư cách khách.</p>
    <?php endif; ?>

    <?php if ($orderMessage !== ''): ?>
        <p class="checkout-notice <?php echo $orderPlaced ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($orderMessage); ?></p>
    <?php endif; ?>

    <?php if ($orderPlaced): ?>
        <div class="checkout-success-box">
            <p>Cảm ơn bạn! Đơn hàng đã được ghi nhận.</p>
            <?php if ($currentCustomer): ?>
                <a class="btn-secondary" href="<?php echo e(app_url('orders')); ?>">Xem đơn hàng của tôi</a>
            <?php else: ?>
                <a class="btn-secondary" href="<?php echo e(auth_login_url('orders')); ?>">Đăng nhập để xem đơn hàng</a>
            <?php endif; ?>
            <a class="read-more" href="<?php echo e(app_url('catalog')); ?>">Tiếp tục mua sắm</a>
        </div>
    <?php elseif ($cartIsEmpty): ?>
        <div class="checkout-empty">
            <p>Giỏ hàng của bạn đang trống.</p>
            <a class="btn-secondary" href="<?php echo e(app_url('catalog')); ?>">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
    <div class="checkout-layout">
        <form method="post" action="<?php echo e(app_url('checkout')); ?>" class="checkout-form" data-checkout-form>
            <?php echo csrfField(); ?>
            <label for="customer_name">Họ và tên</label>
            <input id="customer_name" type="text" name="customer_name" required value="<?php echo htmlspecialchars((string) ($currentCustomer['full_name'] ?? $_POST['customer_name'] ?? '')); ?>">

            <label for="customer_phone">Số điện thoại</label>
            <input id="customer_phone" type="text" name="customer_phone" required value="<?php echo htmlspecialchars((string) ($currentCustomer['phone'] ?? $_POST['customer_phone'] ?? '')); ?>">

            <label for="customer_email">Email (không bắt buộc)</label>
            <input id="customer_email" type="email" name="customer_email" value="<?php echo htmlspecialchars((string) ($currentCustomer['email'] ?? $_POST['customer_email'] ?? '')); ?>">

            <label for="customer_address">Địa chỉ nhận hàng</label>
            <textarea id="customer_address" name="customer_address" rows="4" required><?php echo htmlspecialchars((string) ($_POST['customer_address'] ?? '')); ?></textarea>

            <label for="customer_note">Ghi chú đơn hàng</label>
            <textarea id="customer_note" name="customer_note" rows="3"><?php echo htmlspecialchars((string) ($_POST['customer_note'] ?? '')); ?></textarea>

            <label for="shipping_method_id">Phương thức vận chuyển</label>
            <select id="shipping_method_id" name="shipping_method_id" required data-checkout-shipping>
                <?php if (empty($shippingMethods)): ?>
                    <option value="">Chưa có phương thức vận chuyển</option>
                <?php else: ?>
                    <?php foreach ($shippingMethods as $method): ?>
                        <option
                            value="<?php echo (int) $method['id']; ?>"
                            data-shipping-fee="<?php echo (int) round((float) $method['fee']); ?>"
                            <?php echo ($selectedShippingId === (int) $method['id']) ? 'selected' : ''; ?>
                        >
                            <?php echo htmlspecialchars($method['name']); ?> - <?php echo number_format($method['fee'], 0, ',', '.'); ?>đ (<?php echo htmlspecialchars($method['eta_label']); ?>)
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>

            <label for="payment_method_id">Phương thức thanh toán</label>
            <select id="payment_method_id" name="payment_method_id" required>
                <?php if (empty($paymentMethods)): ?>
                    <option value="">Chưa có phương thức thanh toán</option>
                <?php else: ?>
                    <?php foreach ($paymentMethods as $method): ?>
                        <option value="<?php echo (int) $method['id']; ?>" <?php echo ($selectedPaymentId === (int) $method['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($method['name']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>

            <button type="submit" name="checkout_submit" value="1" <?php echo $checkoutCanSubmit ? '' : 'disabled'; ?>>XÁC NHẬN ĐẶT HÀNG</button>
        </form>

        <aside class="cart-summary" data-checkout-summary data-totals-api="api/checkout-totals.php">
            <h2>Đơn hàng của bạn</h2>
            <?php foreach ($cartItems as $item): ?>
                <div class="summary-line">
                    <span><?php echo htmlspecialchars($item['name']); ?> x<?php echo (int) $item['qty']; ?></span>
                    <strong><?php echo number_format($item['price'] * $item['qty'], 0, ',', '.'); ?>đ</strong>
                </div>
            <?php endforeach; ?>
            <div class="summary-line">
                <span>Tạm tính</span>
                <strong data-checkout-subtotal><?php echo number_format($totals['subtotal'], 0, ',', '.'); ?>đ</strong>
            </div>
            <div class="summary-line">
                <span>Phí vận chuyển</span>
                <strong data-checkout-shipping><?php echo number_format($totals['shipping'], 0, ',', '.'); ?>đ</strong>
            </div>
            <div class="summary-line">
                <span>Giảm giá</span>
                <strong data-checkout-discount><?php echo number_format($totals['discount'], 0, ',', '.'); ?>đ</strong>
            </div>
            <div class="summary-line total">
                <span>Tổng thanh toán</span>
                <strong data-checkout-total><?php echo number_format($totals['total'], 0, ',', '.'); ?>đ</strong>
            </div>
        </aside>
    </div>
    <?php endif; ?>
</section>
