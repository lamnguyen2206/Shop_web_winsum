<?php
require __DIR__ . '/cart-store.php';
require_once __DIR__ . '/../config/database.php';

$cartItems = cartGetItems();
$totals = cartCalculateTotals($cartItems);
$orderPlaced = false;
$orderMessage = '';
$orderCode = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout_submit'])) {
    $customerName = trim((string) ($_POST['customer_name'] ?? ''));
    $customerPhone = trim((string) ($_POST['customer_phone'] ?? ''));
    $customerAddress = trim((string) ($_POST['customer_address'] ?? ''));

    if ($customerName === '' || $customerPhone === '' || $customerAddress === '') {
        $orderMessage = 'Vui lòng điền đầy đủ thông tin nhận hàng.';
    } elseif (empty($cartItems)) {
        $orderMessage = 'Giỏ hàng đang trống, chưa thể thanh toán.';
    } else {
        $couponCode = $_SESSION['cart_coupon'] ?? null;
        $orderCode = 'WS' . date('YmdHis') . random_int(10, 99);

        $conn->begin_transaction();
        try {
            $stmtOrder = $conn->prepare("INSERT INTO orders
                (order_code, customer_name, customer_phone, customer_email, customer_address, customer_note, coupon_code, subtotal, shipping_fee, discount_amount, grand_total, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            if (!$stmtOrder) {
                throw new RuntimeException('Không tạo được câu lệnh lưu đơn hàng.');
            }

            $customerEmail = trim((string) ($_POST['customer_email'] ?? ''));
            $customerNote = trim((string) ($_POST['customer_note'] ?? ''));
            $customerEmail = $customerEmail !== '' ? $customerEmail : null;
            $customerNote = $customerNote !== '' ? $customerNote : null;
            $couponCode = $couponCode !== '' ? $couponCode : null;

            $subtotal = (float) $totals['subtotal'];
            $shipping = (float) $totals['shipping'];
            $discount = (float) $totals['discount'];
            $grandTotal = (float) $totals['total'];

            $stmtOrder->bind_param(
                'sssssssdddd',
                $orderCode,
                $customerName,
                $customerPhone,
                $customerEmail,
                $customerAddress,
                $customerNote,
                $couponCode,
                $subtotal,
                $shipping,
                $discount,
                $grandTotal
            );
            $stmtOrder->execute();
            $orderId = (int) $stmtOrder->insert_id;
            $stmtOrder->close();

            $stmtItem = $conn->prepare("INSERT INTO order_items
                (order_id, product_sku, product_name, product_image, unit_price, quantity, line_total)
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            if (!$stmtItem) {
                throw new RuntimeException('Không tạo được câu lệnh lưu chi tiết đơn hàng.');
            }

            foreach ($cartItems as $item) {
                $sku = (string) $item['sku'];
                $name = (string) $item['name'];
                $image = (string) $item['image'];
                $unitPrice = (float) $item['price'];
                $quantity = (int) $item['qty'];
                $lineTotal = $unitPrice * $quantity;
                $stmtItem->bind_param('isssdid', $orderId, $sku, $name, $image, $unitPrice, $quantity, $lineTotal);
                $stmtItem->execute();
            }
            $stmtItem->close();

            $conn->commit();
            $orderPlaced = true;
            $orderMessage = 'Đặt hàng thành công. Mã đơn của bạn là ' . $orderCode . '.';
            $_SESSION['cart_items'] = [];
            $_SESSION['cart_coupon'] = '';
            $cartItems = [];
            $totals = cartCalculateTotals($cartItems);
        } catch (Throwable $e) {
            $conn->rollback();
            $orderMessage = 'Không thể lưu đơn hàng vào hệ thống. Vui lòng thử lại.';
        }
    }
}
?>

<section class="container checkout-page">
    <p class="breadcrumb"><a href="index.php">Trang chủ</a> / <a href="index.php?view=cart">Giỏ hàng</a> / <span>Thanh toán</span></p>
    <h1>Thông tin thanh toán</h1>

    <?php if ($orderMessage !== ''): ?>
        <p class="checkout-notice <?php echo $orderPlaced ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($orderMessage); ?></p>
    <?php endif; ?>

    <div class="checkout-layout">
        <form method="post" action="index.php?view=checkout" class="checkout-form">
            <label for="customer_name">Họ và tên</label>
            <input id="customer_name" type="text" name="customer_name" required>

            <label for="customer_phone">Số điện thoại</label>
            <input id="customer_phone" type="text" name="customer_phone" required>

            <label for="customer_email">Email (không bắt buộc)</label>
            <input id="customer_email" type="email" name="customer_email">

            <label for="customer_address">Địa chỉ nhận hàng</label>
            <textarea id="customer_address" name="customer_address" rows="4" required></textarea>

            <label for="customer_note">Ghi chú đơn hàng</label>
            <textarea id="customer_note" name="customer_note" rows="3"></textarea>

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
