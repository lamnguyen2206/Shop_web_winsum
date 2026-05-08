<?php
require_once __DIR__ . '/customer-auth.php';
require_once __DIR__ . '/order-repository.php';

$currentCustomer = customerCurrent($conn);
$orders = [];

if ($currentCustomer) {
    $orders = orderGetCustomerOrders($conn, (int) $currentCustomer['id']);
}
?>

<section class="container orders-page">
    <p class="breadcrumb"><a href="index.php?view=home">Trang chủ</a> / <span>Đơn hàng của tôi</span></p>
    <h1>Đơn hàng của tôi</h1>

    <?php if (!$currentCustomer): ?>
        <div class="empty-state">
            <p>Bạn cần đăng nhập để xem lịch sử đơn hàng.</p>
            <a class="btn-secondary" href="index.php?view=account">Đăng nhập ngay</a>
        </div>
    <?php elseif (empty($orders)): ?>
        <div class="empty-state">
            <p>Bạn chưa có đơn hàng nào.</p>
            <a class="btn-secondary" href="index.php?view=catalog">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <div class="orders-table">
            <div class="orders-head">
                <span>Mã đơn</span>
                <span>Trạng thái</span>
                <span>Thanh toán</span>
                <span>Vận chuyển</span>
                <span>Tổng tiền</span>
                <span>Ngày đặt</span>
                <span>Thao tác</span>
            </div>
            <?php foreach ($orders as $order): ?>
                <article class="orders-row">
                    <span>#<?php echo htmlspecialchars($order['order_code']); ?></span>
                    <span><?php echo htmlspecialchars($order['status']); ?></span>
                    <span><?php echo htmlspecialchars($order['payment_status']); ?></span>
                    <span><?php echo htmlspecialchars($order['fulfillment_status']); ?></span>
                    <span><?php echo number_format((float) $order['grand_total'], 0, ',', '.'); ?>đ</span>
                    <span><?php echo htmlspecialchars((string) $order['ordered_at']); ?></span>
                    <span><a class="btn-secondary order-link" href="index.php?view=order-detail&amp;code=<?php echo urlencode((string) $order['order_code']); ?>">Xem chi tiết</a></span>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
