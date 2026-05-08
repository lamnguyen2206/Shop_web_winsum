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
            <a class="icon-link" title="Tìm kiếm sản phẩm" href="index.php?view=catalog" aria-label="Tìm kiếm">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><line x1="16.65" y1="16.65" x2="21" y2="21"></line></svg>
            </a>
            <a class="icon-link" title="Tài khoản" href="index.php?view=account" aria-label="Tài khoản">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0"></path><circle cx="12" cy="7" r="4"></circle></svg>
            </a>
            <a class="icon-link" title="Giỏ hàng" href="index.php?view=cart" aria-label="Giỏ hàng">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="20" r="1.5"></circle><circle cx="18" cy="20" r="1.5"></circle><path d="M3 4h2l2.2 10.5a1 1 0 0 0 1 .8H19a1 1 0 0 0 1-.8L22 7H7"></path></svg>
                <?php echo $cartCount > 0 ? '<em class="cart-badge">' . $cartCount . '</em>' : ''; ?>
            </a>
        </div>
    </div>
</header>
