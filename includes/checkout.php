<?php
require_once __DIR__ . '/cart-store.php';
require_once __DIR__ . '/order-repository.php';
require_once __DIR__ . '/customer-auth.php';
require_once __DIR__ . '/admin-auth.php';
require_once __DIR__ . '/csrf.php';

$checkoutBlockedAdmin = adminCurrent();
$orderPlaced = false;
$orderMessage = '';
$orderCode = '';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout_submit'])) {
    if ($checkoutBlockedAdmin) {
        $orderMessage = 'Tài khoản quản trị không thể đặt hàng qua website.';
    } elseif (!csrfValidate()) {
        $orderMessage = 'Phiên làm việc không hợp lệ. Vui lòng tải lại trang và thử lại.';
    } elseif ($cartIsEmpty) {
        $orderMessage = 'Giỏ hàng đang trống, chưa thể thanh toán.';
    } elseif (empty($shippingMethods) || empty($paymentMethods)) {
        $orderMessage = 'Hệ thống chưa cấu hình phương thức vận chuyển hoặc thanh toán.';
    } else {
        $customerName = trim((string) ($_POST['customer_name'] ?? ''));
        $customerPhone = trim((string) ($_POST['customer_phone'] ?? ''));
        $customerEmail = trim((string) ($_POST['customer_email'] ?? ''));
        $customerAddress = trim((string) ($_POST['customer_address'] ?? ''));
        $customerNote = trim((string) ($_POST['customer_note'] ?? ''));
        $shippingMethodId = (int) ($_POST['shipping_method_id'] ?? 0);
        $paymentMethodId = (int) ($_POST['payment_method_id'] ?? 0);

        $selectedShipping = orderFindShippingMethod($shippingMethods, $shippingMethodId);
        $selectedPayment = orderFindPaymentMethod($paymentMethods, $paymentMethodId);

        if ($selectedShipping) {
            orderApplyShippingToSession($shippingMethods, $shippingMethodId);
            $selectedShippingId = $shippingMethodId;
        }
        if ($selectedPayment) {
            $_SESSION['checkout_payment_method_id'] = $paymentMethodId;
            $selectedPaymentId = $paymentMethodId;
        }

        cartSyncPricesFromDb($conn);
        $cartItems = cartGetItems();
        $totals = cartCalculateTotals($cartItems, $conn, $customerId);
        $cartIsEmpty = $cartItems === [];

        if ($cartIsEmpty) {
            $orderMessage = 'Giỏ hàng đang trống, chưa thể thanh toán.';
        } elseif ($customerName === '' || $customerPhone === '' || $customerAddress === '') {
            $orderMessage = 'Vui lòng điền đầy đủ thông tin nhận hàng.';
        } elseif (!$selectedShipping) {
            $orderMessage = 'Vui lòng chọn phương thức vận chuyển hợp lệ.';
        } elseif (!$selectedPayment) {
            $orderMessage = 'Vui lòng chọn phương thức thanh toán hợp lệ.';
        } else {
            $cartCheck = cartValidateForCheckout($conn, $cartItems);
            if (!$cartCheck['ok']) {
                $orderMessage = $cartCheck['message'];
            } else {
                try {
                    $couponCode = $totals['coupon_code'] !== '' ? $totals['coupon_code'] : null;
                    $couponId = $totals['coupon_id'] > 0 ? $totals['coupon_id'] : null;
                    $orderCode = orderCreateFromCheckout(
                        $conn,
                        [
                            'name' => $customerName,
                            'phone' => $customerPhone,
                            'email' => $customerEmail,
                            'address' => $customerAddress,
                            'note' => $customerNote,
                        ],
                        $cartItems,
                        $totals,
                        $couponCode,
                        $customerId,
                        $shippingMethodId,
                        $paymentMethodId,
                        $couponId
                    );
                    $orderPlaced = true;
                    $orderMessage = 'Đặt hàng thành công. Mã đơn của bạn là ' . $orderCode . '.';
                    cartClear();
                    unset($_SESSION['checkout_shipping_method_id'], $_SESSION['checkout_payment_method_id']);
                    if (!empty($shippingMethods)) {
                        $_SESSION['selected_shipping_fee'] = (int) $shippingMethods[0]['fee'];
                    }
                    $cartItems = [];
                    $totals = cartCalculateTotals($cartItems, $conn, $customerId);
                    $cartIsEmpty = true;
                } catch (Throwable $e) {
                    $orderMessage = 'Không thể lưu đơn hàng vào hệ thống. Vui lòng thử lại.';
                }
            }
        }
    }
}

$checkoutCanSubmit = !$checkoutBlockedAdmin
    && !$cartIsEmpty
    && !$orderPlaced
    && !empty($shippingMethods)
    && !empty($paymentMethods);
?>

<section class="container checkout-page">
    <p class="breadcrumb"><a href="index.php?view=home">Trang chủ</a> / <a href="index.php?view=cart">Giỏ hàng</a> / <span>Thanh toán</span></p>
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
                <a class="btn-secondary" href="index.php?view=orders">Xem đơn hàng của tôi</a>
            <?php endif; ?>
            <a class="read-more" href="index.php?view=catalog">Tiếp tục mua sắm</a>
        </div>
    <?php elseif ($cartIsEmpty): ?>
        <div class="checkout-empty">
            <p>Giỏ hàng của bạn đang trống.</p>
            <a class="btn-secondary" href="index.php?view=catalog">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
    <div class="checkout-layout">
        <form method="post" action="index.php?view=checkout" class="checkout-form" data-checkout-form>
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
