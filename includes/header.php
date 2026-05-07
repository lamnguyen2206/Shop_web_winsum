<header class="site-header">
    <div class="topbar">NỘI THẤT VÀ CHIẾU SÁNG CAO CẤP</div>

    <div class="navbar container">
        <a class="brand" href="index.php">winsum home</a>

        <nav class="main-nav">
            <ul>
                <li><a href="index.php">Trang chủ</a></li>
                <li><a href="#">Tất cả sản phẩm</a></li>
                <li><a href="#">Danh mục</a></li>
                <li><a class="<?php echo (!isset($_GET['view']) || $_GET['view'] === 'blog' || $_GET['view'] === 'post') ? 'active' : ''; ?>" href="index.php?view=blog">Blog</a></li>
                <li><a class="<?php echo (isset($_GET['view']) && ($_GET['view'] === 'cart' || $_GET['view'] === 'checkout')) ? 'active' : ''; ?>" href="index.php?view=cart">Giỏ hàng</a></li>
            </ul>
        </nav>

        <div class="nav-icons">
            <span title="Search">🔍</span>
            <span title="User">👤</span>
            <span title="Cart">🛍️</span>
        </div>
    </div>
</header>
