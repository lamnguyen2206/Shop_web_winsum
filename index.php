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
    $view = isset($_GET['view']) ? (string) $_GET['view'] : 'blog';
    $pageTitle = 'Tin tức | Winsum Home';
    if ($view === 'post') {
        $pageTitle = 'Chi tiết bài viết | Winsum Home';
    } elseif ($view === 'cart') {
        $pageTitle = 'Giỏ hàng | Winsum Home';
    } elseif ($view === 'checkout') {
        $pageTitle = 'Thanh toán | Winsum Home';
    }
    ?>
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/blog.css">
    <link rel="stylesheet" href="assets/css/cart.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <?php
        if ($view === 'post') {
            include 'includes/blog-detail.php';
        } elseif ($view === 'cart') {
            include 'includes/cart.php';
        } elseif ($view === 'checkout') {
            include 'includes/checkout.php';
        } else {
            include 'includes/blog.php';
        }
        ?>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>
