<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $view = isset($_GET['view']) ? (string) $_GET['view'] : 'home';
    $pageTitle = 'Winsum Home | Nội thất và chiếu sáng cao cấp';
    if ($view === 'catalog') {
        $pageTitle = 'Sản phẩm | Winsum Home';
    } elseif ($view === 'product') {
        $pageTitle = 'Chi tiết sản phẩm | Winsum Home';
    } elseif ($view === 'blog') {
        $pageTitle = 'Tin tức | Winsum Home';
    } elseif ($view === 'post') {
        $pageTitle = 'Chi tiết bài viết | Winsum Home';
    } elseif ($view === 'cart') {
        $pageTitle = 'Giỏ hàng | Winsum Home';
    } elseif ($view === 'checkout') {
        $pageTitle = 'Thanh toán | Winsum Home';
    } elseif ($view === 'account') {
        $pageTitle = 'Tài khoản | Winsum Home';
    } elseif ($view === 'orders') {
        $pageTitle = 'Đơn hàng của tôi | Winsum Home';
    } elseif ($view === 'order-detail') {
        $pageTitle = 'Chi tiết đơn hàng | Winsum Home';
    }
    ?>
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/home.css">
    <link rel="stylesheet" href="assets/css/catalog.css">
    <link rel="stylesheet" href="assets/css/blog.css">
    <link rel="stylesheet" href="assets/css/cart.css">
    <link rel="stylesheet" href="assets/css/account.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <?php
        if ($view === 'home') {
            include 'includes/home.php';
        } elseif ($view === 'catalog') {
            include 'includes/catalog.php';
        } elseif ($view === 'product') {
            include 'includes/product-detail.php';
        } elseif ($view === 'blog') {
            include 'includes/blog.php';
        } elseif ($view === 'post') {
            include 'includes/blog-detail.php';
        } elseif ($view === 'cart') {
            include 'includes/cart.php';
        } elseif ($view === 'checkout') {
            include 'includes/checkout.php';
        } elseif ($view === 'account') {
            include 'includes/account.php';
        } elseif ($view === 'orders') {
            include 'includes/my-orders.php';
        } elseif ($view === 'order-detail') {
            include 'includes/order-detail.php';
        } else {
            include 'includes/home.php';
        }
        ?>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>
