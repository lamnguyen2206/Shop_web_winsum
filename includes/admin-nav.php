<?php
$adminView = isset($_GET['view']) ? (string) $_GET['view'] : '';
?>
<nav class="admin-nav" aria-label="Menu quản trị">
    <a class="<?php echo $adminView === 'admin-dashboard' ? 'active' : ''; ?>" href="index.php?view=admin-dashboard">Tổng quan</a>
    <a class="<?php echo $adminView === 'admin-orders' ? 'active' : ''; ?>" href="index.php?view=admin-orders">Đơn hàng</a>
    <a class="<?php echo $adminView === 'admin-products' ? 'active' : ''; ?>" href="index.php?view=admin-products">Sản phẩm</a>
    <a class="<?php echo $adminView === 'admin-reviews' ? 'active' : ''; ?>" href="index.php?view=admin-reviews">Đánh giá</a>
    <a class="<?php echo $adminView === 'blog-editor' ? 'active' : ''; ?>" href="index.php?view=blog-editor">Soạn blog</a>
</nav>
