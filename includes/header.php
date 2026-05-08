<?php
require_once __DIR__ . '/cart-store.php';
$view = isset($_GET['view']) ? (string) $_GET['view'] : 'home';
$cartCount = cartCountItems();
?>
<header class="site-header">
    <div class="topbar">NỘI THẤT VÀ CHIẾU SÁNG CAO CẤP</div>

    <div class="navbar container">
        <a class="brand" href="index.php">winsum home</a>

        <nav class="main-nav">
            <ul>
                <li><a class="<?php echo ($view === 'home') ? 'active' : ''; ?>" href="index.php?view=home">Trang chủ</a></li>
                <li><a class="<?php echo ($view === 'catalog' || $view === 'product') ? 'active' : ''; ?>" href="index.php?view=catalog">Sản phẩm</a></li>
                <li><a class="<?php echo ($view === 'blog' || $view === 'post') ? 'active' : ''; ?>" href="index.php?view=blog">Blog</a></li>
                <li><a class="<?php echo ($view === 'orders') ? 'active' : ''; ?>" href="index.php?view=orders">Đơn hàng</a></li>
                <li><a class="<?php echo ($view === 'account') ? 'active' : ''; ?>" href="index.php?view=account">Tài khoản</a></li>
            </ul>
        </nav>

        <div class="nav-icons">
            <a title="Tìm kiếm sản phẩm" href="index.php?view=catalog">🔍</a>
            <a title="Tài khoản" href="index.php?view=account">👤</a>
            <a title="Giỏ hàng" href="index.php?view=cart">🛍️<?php echo $cartCount > 0 ? '<em class="cart-badge">' . $cartCount . '</em>' : ''; ?></a>
        </div>
    </div>
</header>
