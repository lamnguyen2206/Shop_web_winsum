<?php
require_once __DIR__ . '/cart-store.php';
require_once __DIR__ . '/admin-auth.php';

$view = isset($_GET['view']) ? (string) $_GET['view'] : 'home';
$cartCount = cartCountItems();
$isAdmin = adminCurrent();
?><header class="site-header">
    <div class="topbar">NỘI THẤT VÀ CHIẾU SÁNG CAO CẤP</div>

    <div class="navbar container">
        <a class="brand" href="index.php?view=home">winsum home</a>

        <nav class="main-nav">
            <ul>
                <li><a class="<?php echo ($view === 'home') ? 'active' : ''; ?>" href="index.php?view=home">Trang chủ</a></li>
                <li><a class="<?php echo ($view === 'catalog' || $view === 'product') ? 'active' : ''; ?>" href="index.php?view=catalog">Sản phẩm</a></li>
                <li><a class="<?php echo ($view === 'blog' || $view === 'post') ? 'active' : ''; ?>" href="index.php?view=blog">Blog</a></li>
                <li><a class="<?php echo ($view === 'orders') ? 'active' : ''; ?>" href="index.php?view=orders">Đơn hàng</a></li>
                <?php if (empty($currentCustomer) && !$isAdmin): ?>
                    <li><a class="<?php echo ($view === 'account') ? 'active' : ''; ?>" href="index.php?view=account">Tài khoản</a></li>
                <?php endif; ?>
                <?php if ($isAdmin): ?>
                    <li><a class="<?php echo (str_starts_with($view, 'admin') || $view === 'blog-editor') ? 'active' : ''; ?>" href="index.php?view=admin-dashboard">Quản trị</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <div class="nav-icons">
            <button type="button" class="icon-link" id="site-search-open" title="Tìm kiếm sản phẩm" aria-label="Tìm kiếm" aria-expanded="false" aria-controls="site-search-overlay">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><line x1="16.65" y1="16.65" x2="21" y2="21"></line></svg>
            </button>
            <div class="nav-account-wrap">
                <button type="button" class="icon-link nav-account-trigger<?php echo !empty($currentCustomer) ? ' nav-account-trigger--active' : ''; ?>" aria-label="Tài khoản" aria-expanded="false" aria-haspopup="true" aria-controls="nav-account-menu" id="nav-account-btn">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </button>
                <div class="nav-account-dropdown" id="nav-account-menu" role="menu" hidden>
                    <?php if (!empty($currentCustomer)): ?>
                        <a role="menuitem" href="index.php?view=account#profile-edit">Sửa thông tin</a>
                        <form method="post" action="" class="nav-account-logout">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="auth_action" value="logout">
                            <button type="submit" class="nav-account-btn">Đăng xuất</button>
                        </form>
                    <?php elseif ($isAdmin): ?>
                        <a role="menuitem" href="index.php?view=admin-dashboard">Bảng quản trị</a>
                    <?php else: ?>
                        <button type="button" class="nav-account-btn" role="menuitem" data-open-auth="login">Đăng nhập</button>
                        <button type="button" class="nav-account-btn" role="menuitem" data-open-auth="register">Đăng ký</button>
                    <?php endif; ?>
                </div>
            </div>
            <a class="icon-link" title="Giỏ hàng" href="index.php?view=cart" aria-label="Giỏ hàng">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="20" r="1.5"></circle><circle cx="18" cy="20" r="1.5"></circle><path d="M3 4h2l2.2 10.5a1 1 0 0 0 1 .8H19a1 1 0 0 0 1-.8L22 7H7"></path></svg>
                <?php echo $cartCount > 0 ? '<em class="cart-badge">' . $cartCount . '</em>' : ''; ?>
            </a>
        </div>
    </div>
    <?php include __DIR__ . '/site-search.php'; ?>
</header>
