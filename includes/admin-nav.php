<?php
require_once __DIR__ . '/helpers.php';

$adminView = isset($_GET['view']) ? (string) $_GET['view'] : '';
?>
<nav class="admin-nav" aria-label="Menu quản trị">
    <a class="<?php echo $adminView === 'admin-dashboard' ? 'active' : ''; ?>" href="<?php echo e(app_url('admin-dashboard')); ?>">Tổng quan</a>
    <a class="<?php echo $adminView === 'admin-orders' || $adminView === 'admin-order-detail' ? 'active' : ''; ?>" href="<?php echo e(app_url('admin-orders')); ?>">Quản lý đơn</a>
    <a class="<?php echo $adminView === 'admin-customers' ? 'active' : ''; ?>" href="<?php echo e(app_url('admin-customers')); ?>">Khách hàng</a>
    <a class="<?php echo $adminView === 'admin-products' ? 'active' : ''; ?>" href="<?php echo e(app_url('admin-products')); ?>">Sản phẩm</a>
    <a class="<?php echo $adminView === 'admin-reviews' ? 'active' : ''; ?>" href="<?php echo e(app_url('admin-reviews')); ?>">Đánh giá</a>
    <a class="<?php echo $adminView === 'blog-editor' ? 'active' : ''; ?>" href="<?php echo e(app_url('blog-editor')); ?>">Soạn blog</a>
</nav>
