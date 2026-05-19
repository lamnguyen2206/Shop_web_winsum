<?php
require_once __DIR__ . '/customer-auth.php';
require_once __DIR__ . '/order-repository.php';

$currentCustomer = customerCurrent($conn);
$orderCode = trim((string) ($_GET['code'] ?? ''));
$order = null;

if ($currentCustomer && $orderCode !== '') {
    $order = orderGetCustomerOrderDetailByCode($conn, (int) $currentCustomer['id'], $orderCode);
}
?>

<section class="container order-detail-page">
    <p class="breadcrumb"><a href="<?php echo e(app_url('home')); ?>">Trang chủ</a> / <a href="<?php echo e(app_url('orders')); ?>">Đơn hàng của tôi</a> / <span>Chi tiết đơn hàng</span></p>
    <h1>Chi tiết đơn hàng</h1>

    <?php if (!$currentCustomer): ?>
        <div class="empty-state">
            <p>Bạn cần đăng nhập để xem chi tiết đơn hàng.</p>
            <?php
            $loginParams = $orderCode !== '' ? ['code' => $orderCode] : [];
            $loginReturnView = $orderCode !== '' ? 'order-detail' : 'orders';
            ?>
            <a class="btn-secondary" href="<?php echo e(auth_login_url($loginReturnView, $loginParams)); ?>">Đăng nhập ngay</a>
        </div>
    <?php elseif (!$order): ?>
        <div class="empty-state">
            <p>Không tìm thấy đơn hàng với mã bạn yêu cầu.</p>
            <a class="btn-secondary" href="<?php echo e(app_url('orders')); ?>">Quay lại danh sách đơn</a>
        </div>
    <?php else: ?>
        <div class="order-detail-grid">
            <article class="order-card">
                <h2>Thông tin đơn #<?php echo htmlspecialchars($order['order_code']); ?></h2>
                <p><strong>Ngày đặt:</strong> <?php echo htmlspecialchars((string) $order['ordered_at']); ?></p>
                <p><strong>Trạng thái:</strong> <?php echo htmlspecialchars(orderStatusLabel((string) $order['status'])); ?></p>
                <p><strong>Thanh toán:</strong> <?php echo htmlspecialchars(orderPaymentStatusLabel((string) ($order['payment']['status'] ?? $order['payment_status']))); ?></p>
                <p><strong>Vận chuyển:</strong> <?php echo htmlspecialchars(orderFulfillmentStatusLabel((string) $order['fulfillment_status'])); ?></p>
            </article>

            <article class="order-card">
                <h2>Thông tin nhận hàng</h2>
                <p><strong>Người nhận:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                <p><strong>SĐT:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['customer_address']); ?></p>
                <p><strong>Vận chuyển:</strong> <?php echo htmlspecialchars((string) ($order['shipment']['shipping_method_name'] ?? '')); ?></p>
                <p><strong>Thanh toán:</strong> <?php echo htmlspecialchars((string) ($order['payment']['payment_method_name'] ?? '')); ?></p>
            </article>
        </div>

        <div class="orders-table order-items-table">
            <div class="orders-head">
                <span>Sản phẩm</span>
                <span>SKU</span>
                <span>Đơn giá</span>
                <span>Số lượng</span>
                <span>Thành tiền</span>
                <span>Ảnh</span>
            </div>
            <?php foreach ($order['items'] as $item): ?>
                <article class="orders-row">
                    <span><?php echo htmlspecialchars($item['product_name']); ?></span>
                    <span><?php echo htmlspecialchars($item['product_sku']); ?></span>
                    <span><?php echo number_format((float) $item['unit_price'], 0, ',', '.'); ?>đ</span>
                    <span><?php echo (int) $item['quantity']; ?></span>
                    <span><?php echo number_format((float) $item['line_total'], 0, ',', '.'); ?>đ</span>
                    <span><img class="order-item-img" src="<?php echo htmlspecialchars((string) ($item['product_image'] ?? 'assets/images/blog_1.png')); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>"></span>
                </article>
            <?php endforeach; ?>
        </div>

        <aside class="cart-summary order-total-box">
            <h2>Tổng kết thanh toán</h2>
            <div class="summary-line"><span>Tạm tính</span><strong><?php echo number_format((float) $order['subtotal'], 0, ',', '.'); ?>đ</strong></div>
            <div class="summary-line"><span>Phí vận chuyển</span><strong><?php echo number_format((float) $order['shipping_fee'], 0, ',', '.'); ?>đ</strong></div>
            <div class="summary-line"><span>Giảm giá</span><strong><?php echo number_format((float) $order['discount_amount'], 0, ',', '.'); ?>đ</strong></div>
            <div class="summary-line total"><span>Thành tiền</span><strong><?php echo number_format((float) $order['grand_total'], 0, ',', '.'); ?>đ</strong></div>
        </aside>
    <?php endif; ?>
</section>
