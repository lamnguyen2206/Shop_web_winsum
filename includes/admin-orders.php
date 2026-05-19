<?php
require_once __DIR__ . '/order-repository.php';

$adminMessage = '';
$orders = orderGetAllOrders($conn, 100);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValidate()) {
        $adminMessage = 'Phiên làm việc không hợp lệ.';
    } else {
        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'update_status') {
            $orderId = (int) ($_POST['order_id'] ?? 0);
            $newStatus = trim((string) ($_POST['status'] ?? ''));
            if (orderUpdateStatus($conn, $orderId, $newStatus, 'admin')) {
                $adminMessage = 'Đã cập nhật trạng thái đơn hàng.';
            } else {
                $adminMessage = 'Không thể cập nhật trạng thái đơn hàng.';
            }
            $orders = orderGetAllOrders($conn, 100);
        }
    }
}

$statusOptions = ['pending', 'processing', 'packed', 'shipped', 'delivered', 'cancelled', 'returned'];
?>

<section class="container orders-page admin-page admin-orders-page">
    <p class="breadcrumb"><a href="<?php echo e(app_url('home')); ?>">Trang chủ</a> / <span>Quản trị đơn hàng</span></p>
    <div class="admin-page-head">
        <h1>Quản lý đơn hàng</h1>
        <form method="post" action="index.php?view=admin-orders" class="admin-inline-form">
            <?php echo csrfField(); ?>
            <input type="hidden" name="action" value="admin_logout">
            <button type="submit" class="btn-secondary">Đăng xuất</button>
        </form>
    </div>

    <?php include __DIR__ . '/admin-nav.php'; ?>

    <?php if ($adminMessage !== ''): ?>
        <p class="account-notice"><?php echo htmlspecialchars($adminMessage); ?></p>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div class="empty-state">
            <p>Chưa có đơn hàng nào trong hệ thống.</p>
        </div>
    <?php else: ?>
        <div class="admin-panel admin-panel-wide">
        <div class="orders-table admin-orders-table">
            <div class="orders-head">
                <span>Mã đơn</span>
                <span>Khách hàng</span>
                <span>SĐT</span>
                <span>Trạng thái</span>
                <span>Tổng tiền</span>
                <span>Ngày đặt</span>
                <span>Cập nhật</span>
            </div>
            <?php foreach ($orders as $order): ?>
                <div class="orders-row admin-order-row">
                    <span>#<?php echo htmlspecialchars($order['order_code']); ?></span>
                    <span><?php echo htmlspecialchars($order['customer_name']); ?></span>
                    <span><?php echo htmlspecialchars($order['customer_phone']); ?></span>
                    <span class="order-status-text"><?php echo htmlspecialchars(orderStatusLabel((string) $order['status'])); ?></span>
                    <span><?php echo number_format((float) $order['grand_total'], 0, ',', '.'); ?>đ</span>
                    <span><?php echo htmlspecialchars((string) $order['ordered_at']); ?></span>
                    <span>
                        <form method="post" action="index.php?view=admin-orders" class="admin-status-form admin-order-status-form">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
                            <select name="status" class="admin-order-status-select" aria-label="Trạng thái đơn hàng">
                                <?php foreach ($statusOptions as $status): ?>
                                    <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $order['status'] === $status ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(orderStatusLabel($status)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="admin-btn-save">Lưu</button>
                        </form>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
        </div>
    <?php endif; ?>
</section>
