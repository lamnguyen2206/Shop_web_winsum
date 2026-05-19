<?php
require_once __DIR__ . '/admin-auth.php';
require_once __DIR__ . '/customer-auth.php';
require_once __DIR__ . '/admin-stats.php';
require_once __DIR__ . '/csrf.php';

adminRequire();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrfValidate() && ($_POST['action'] ?? '') === 'admin_logout') {
    customerLogout();
    header('Location: index.php?view=home');
    exit;
}

$stats = adminGetDashboardStats($conn);
$recentOrders = adminGetRecentOrders($conn, 6);
require_once __DIR__ . '/inventory-repository.php';
$inventoryAlerts = inventoryGetUnreadAlerts($conn, 5);
?>

<section class="container admin-page admin-dashboard-page">
    <p class="breadcrumb"><a href="index.php?view=home">Trang chủ</a> / <span>Bảng điều khiển</span></p>

    <div class="admin-page-head">
        <h1>Bảng quản trị Winsum Home</h1>
        <form method="post" class="admin-inline-form">
            <?php echo csrfField(); ?>
            <input type="hidden" name="action" value="admin_logout">
            <button type="submit" class="btn-secondary">Đăng xuất</button>
        </form>
    </div>

    <?php include __DIR__ . '/admin-nav.php'; ?>

    <div class="admin-stats-grid">
        <article class="admin-stat-card">
            <span class="admin-stat-label">Tổng đơn hàng</span>
            <strong class="admin-stat-value"><?php echo (int) $stats['orders_total']; ?></strong>
            <small><?php echo (int) $stats['orders_pending']; ?> đang chờ xử lý</small>
        </article>
        <article class="admin-stat-card">
            <span class="admin-stat-label">Doanh thu (ước tính)</span>
            <strong class="admin-stat-value"><?php echo number_format($stats['revenue_total'], 0, ',', '.'); ?>đ</strong>
            <small>Trừ đơn hủy/trả</small>
        </article>
        <article class="admin-stat-card">
            <span class="admin-stat-label">Sản phẩm</span>
            <strong class="admin-stat-value"><?php echo (int) $stats['products_active']; ?></strong>
            <small>/ <?php echo (int) $stats['products_total']; ?> tổng</small>
        </article>
        <article class="admin-stat-card">
            <span class="admin-stat-label">Khách hàng</span>
            <strong class="admin-stat-value"><?php echo (int) $stats['customers_total']; ?></strong>
        </article>
        <article class="admin-stat-card admin-stat-card--alert">
            <span class="admin-stat-label">Đánh giá chờ duyệt</span>
            <strong class="admin-stat-value"><?php echo (int) $stats['reviews_pending']; ?></strong>
            <small>/ <?php echo (int) $stats['reviews_total']; ?> tổng</small>
        </article>
        <?php if ((int) $stats['inventory_alerts_unread'] > 0): ?>
        <article class="admin-stat-card admin-stat-card--alert">
            <span class="admin-stat-label">Cảnh báo tồn kho</span>
            <strong class="admin-stat-value"><?php echo (int) $stats['inventory_alerts_unread']; ?></strong>
            <small><a href="index.php?view=admin-products">Xem &amp; nhập hàng →</a></small>
        </article>
        <?php endif; ?>
    </div>

    <div class="admin-dashboard-panels">
        <div class="admin-panel">
            <h2>Truy cập nhanh</h2>
            <div class="admin-quick-links">
                <a href="index.php?view=admin-orders">Quản lý đơn hàng</a>
                <a href="index.php?view=admin-products">CRUD sản phẩm</a>
                <a href="index.php?view=admin-reviews">Duyệt đánh giá</a>
                <a href="index.php?view=blog-editor">Soạn bài blog</a>
                <a href="index.php?view=catalog" target="_blank" rel="noopener">Xem cửa hàng</a>
            </div>
        </div>

        <?php if ($inventoryAlerts !== []): ?>
        <div class="admin-panel admin-panel-wide admin-inventory-alerts">
            <h2>Cảnh báo hết tồn kho → Đặt trước</h2>
            <ul class="admin-alert-list">
                <?php foreach ($inventoryAlerts as $alert): ?>
                    <li>
                        <p><?php echo htmlspecialchars($alert['message']); ?></p>
                        <a href="index.php?view=admin-products&amp;edit=<?php echo (int) $alert['product_id']; ?>">Chỉnh sửa &amp; nhập tồn</a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p><a href="index.php?view=admin-products">Quản lý sản phẩm &amp; tồn kho →</a></p>
        </div>
        <?php endif; ?>

        <div class="admin-panel admin-panel-wide">
            <h2>Đơn hàng mới nhất</h2>
            <?php if (empty($recentOrders)): ?>
                <p class="empty-state">Chưa có đơn hàng.</p>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách</th>
                                <th>Tổng</th>
                                <th>Trạng thái</th>
                                <th>Ngày</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['order_code']); ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo number_format((float) $order['grand_total'], 0, ',', '.'); ?>đ</td>
                                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                                    <td><?php echo htmlspecialchars((string) $order['ordered_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p style="margin-top:12px;"><a href="index.php?view=admin-orders">Xem tất cả đơn hàng →</a></p>
            <?php endif; ?>
        </div>
    </div>
</section>
