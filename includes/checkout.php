<?php
require __DIR__ . '/cart-store.php';
require_once __DIR__ . '/order-repository.php';
require_once __DIR__ . '/customer-auth.php';

$cartItems = cartGetItems();
$orderPlaced = false;
$orderMessage = '';
$orderCode = '';
$shippingMethods = orderGetShippingMethods($conn);
$paymentMethods = orderGetPaymentMethods($conn);
$currentCustomer = customerCurrent($conn);
$selectedShippingId = (int) ($_POST['shipping_method_id'] ?? (!empty($shippingMethods) ? $shippingMethods[0]['id'] : 0));
$selectedPaymentId = (int) ($_POST['payment_method_id'] ?? (!empty($paymentMethods) ? $paymentMethods[0]['id'] : 0));

if (!isset($_SESSION['selected_shipping_fee']) && !empty($shippingMethods)) {
    $_SESSION['selected_shipping_fee'] = (int) $shippingMethods[0]['fee'];
}
$totals = cartCalculateTotals($cartItems);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout_submit'])) {
    $customerName = trim((string) ($_POST['customer_name'] ?? ''));
    $customerPhone = trim((string) ($_POST['customer_phone'] ?? ''));
    $customerEmail = trim((string) ($_POST['customer_email'] ?? ''));
    $customerAddress = trim((string) ($_POST['customer_address'] ?? ''));
    $customerNote = trim((string) ($_POST['customer_note'] ?? ''));
    $shippingMethodId = (int) ($_POST['shipping_method_id'] ?? 0);
    $paymentMethodId = (int) ($_POST['payment_method_id'] ?? 0);
    $selectedShipping = null;
    foreach ($shippingMethods as $method) {
        if ($method['id'] === $shippingMethodId) {
            $selectedShipping = $method;
            break;
        }
    }
    if ($selectedShipping) {
        $_SESSION['selected_shipping_fee'] = (int) $selectedShipping['fee'];
    }
    $totals = cartCalculateTotals($cartItems);

    if ($customerName === '' || $customerPhone === '' || $customerAddress === '') {
        $orderMessage = 'Vui lòng điền đầy đủ thông tin nhận hàng.';
    } elseif (empty($cartItems)) {
        $orderMessage = 'Giỏ hàng đang trống, chưa thể thanh toán.';
    } elseif ($shippingMethodId <= 0 || $paymentMethodId <= 0) {
        $orderMessage = 'Vui lòng chọn phương thức thanh toán và vận chuyển.';
    } else {
        try {
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
                (string) ($_SESSION['cart_coupon'] ?? ''),
                $currentCustomer ? (int) $currentCustomer['id'] : null,
                $shippingMethodId,
                $paymentMethodId
            );
            $orderPlaced = true;
            $orderMessage = 'Đặt hàng thành công. Mã đơn của bạn là ' . $orderCode . '.';
            cartClear();
            $_SESSION['selected_shipping_fee'] = !empty($shippingMethods) ? (int) $shippingMethods[0]['fee'] : 0;
            $cartItems = [];
            $totals = cartCalculateTotals($cartItems);
        } catch (Throwable $e) {
            $orderMessage = 'Không thể lưu đơn hàng vào hệ thống. Vui lòng thử lại.';
        }
    }
}
?>

<section class="container checkout-page">
    <p class="breadcrumb"><a href="index.php?view=home">Trang chủ</a> / <a href="index.php?view=cart">Giỏ hàng</a> / <span>Thanh toán</span></p>
    <h1>Thông tin thanh toán</h1>

    <?php if ($orderMessage !== ''): ?>
        <p class="checkout-notice <?php echo $orderPlaced ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($orderMessage); ?></p>
    <?php endif; ?>

    <div class="checkout-layout">
        <form method="post" action="index.php?view=checkout" class="checkout-form">
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
            <select id="shipping_method_id" name="shipping_method_id" required>
                <option value="">Chọn phương thức vận chuyển</option>
                <?php foreach ($shippingMethods as $method): ?>
                    <option value="<?php echo (int) $method['id']; ?>" <?php echo ($selectedShippingId === (int) $method['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($method['name']); ?> - <?php echo number_format($method['fee'], 0, ',', '.'); ?>đ (<?php echo htmlspecialchars($method['eta_label']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="payment_method_id">Phương thức thanh toán</label>
            <select id="payment_method_id" name="payment_method_id" required>
                <option value="">Chọn phương thức thanh toán</option>
                <?php foreach ($paymentMethods as $method): ?>
                    <option value="<?php echo (int) $method['id']; ?>" <?php echo ($selectedPaymentId === (int) $method['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($method['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" name="checkout_submit" value="1">XÁC NHẬN ĐẶT HÀNG</button>
        </form>

        <aside class="cart-summary">
            <h2>Đơn hàng của bạn</h2>
            <?php foreach ($cartItems as $item): ?>
                <div class="summary-line">
                    <span><?php echo htmlspecialchars($item['name']); ?> x<?php echo (int) $item['qty']; ?></span>
                    <strong><?php echo number_format($item['price'] * $item['qty'], 0, ',', '.'); ?>đ</strong>
                </div>
            <?php endforeach; ?>
            <div class="summary-line">
                <span>Tạm tính</span>
                <strong><?php echo number_format($totals['subtotal'], 0, ',', '.'); ?>đ</strong>
            </div>
            <div class="summary-line">
                <span>Phí vận chuyển</span>
                <strong><?php echo number_format($totals['shipping'], 0, ',', '.'); ?>đ</strong>
            </div>
            <div class="summary-line">
                <span>Giảm giá</span>
                <strong><?php echo number_format($totals['discount'], 0, ',', '.'); ?>đ</strong>
            </div>
            <div class="summary-line total">
                <span>Tổng thanh toán</span>
                <strong><?php echo number_format($totals['total'], 0, ',', '.'); ?>đ</strong>
            </div>
        </aside>
    </div>
</section>
