<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/customer-auth.php';
require_once __DIR__ . '/includes/customer-auth-post.php';
require_once __DIR__ . '/includes/admin-auth.php';

customerBootstrapAdminAccount($conn);
customerAuthHandlePost($conn);

$authFlash = customerAuthConsumeFlash();
$authMessage = $authFlash['message'];
$authSuccess = $authFlash['success'];
$authOpenModal = $authFlash['open'];
$authPrefill = $authFlash['prefill'];
$currentCustomer = customerCurrent($conn);
$isAdmin = adminCurrent();
$storefrontGuest = !$currentCustomer && !$isAdmin;

$view = isset($_GET['view']) ? (string) $_GET['view'] : 'home';
$pageTitle = 'Winsum Home | Nội thất và chiếu sáng cao cấp';

$pageTitles = [
    'catalog' => 'Sản phẩm | Winsum Home',
    'product' => 'Chi tiết sản phẩm | Winsum Home',
    'blog' => 'Tin tức | Winsum Home',
    'post' => 'Chi tiết bài viết | Winsum Home',
    'blog-editor' => 'Soạn bài blog | Winsum Home',
    'cart' => 'Giỏ hàng | Winsum Home',
    'checkout' => 'Thanh toán | Winsum Home',
    'account' => 'Tài khoản | Winsum Home',
    'orders' => 'Đơn hàng của tôi | Winsum Home',
    'order-detail' => 'Chi tiết đơn hàng | Winsum Home',
    'admin-login' => 'Đăng nhập quản trị | Winsum Home',
    'admin-dashboard' => 'Bảng quản trị | Winsum Home',
    'admin-orders' => 'Quản trị đơn hàng | Winsum Home',
    'admin-products' => 'Quản trị sản phẩm | Winsum Home',
    'admin-reviews' => 'Quản trị đánh giá | Winsum Home',
];

if (isset($pageTitles[$view])) {
    $pageTitle = $pageTitles[$view];
}

$viewFiles = [
    'home' => 'includes/home.php',
    'catalog' => 'includes/catalog.php',
    'product' => 'includes/product-detail.php',
    'blog' => 'includes/blog.php',
    'post' => 'includes/blog-detail.php',
    'blog-editor' => 'includes/blog-editor.php',
    'cart' => 'includes/cart.php',
    'checkout' => 'includes/checkout.php',
    'account' => 'includes/account.php',
    'orders' => 'includes/my-orders.php',
    'order-detail' => 'includes/order-detail.php',
    'admin-login' => 'includes/admin-login.php',
    'admin-dashboard' => 'includes/admin-dashboard.php',
    'admin-orders' => 'includes/admin-orders.php',
    'admin-products' => 'includes/admin-products.php',
    'admin-reviews' => 'includes/admin-reviews.php',
];

$includeFile = $viewFiles[$view] ?? $viewFiles['home'];

$extraStyles = ['assets/css/site-search.css'];
$extraScripts = ['assets/js/auth-forms.js', 'assets/js/site-search.js'];

if ($view === 'product') {
    $extraStyles[] = 'assets/css/product-detail.css';
    $extraScripts[] = 'assets/js/product-detail.js';
}

if ($view === 'checkout') {
    $extraScripts[] = 'assets/js/checkout.js';
}

if ($storefrontGuest || $view === 'admin-login') {
    $extraStyles[] = 'assets/css/auth-forms.css';
}

if (str_starts_with($view, 'admin') || $view === 'blog-editor') {
    $extraStyles[] = 'assets/css/admin.css';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/home.css">
    <link rel="stylesheet" href="assets/css/catalog.css">
    <link rel="stylesheet" href="assets/css/blog.css">
    <link rel="stylesheet" href="assets/css/cart.css">
    <link rel="stylesheet" href="assets/css/account.css">
    <?php foreach ($extraStyles as $styleHref): ?>
        <link rel="stylesheet" href="<?php echo htmlspecialchars($styleHref); ?>">
    <?php endforeach; ?>
</head>
<body<?php
$bodyAttr = [];
if ($storefrontGuest) {
    $bodyAttr[] = 'class="has-storefront-auth-ui"';
}
if ($authOpenModal && $storefrontGuest) {
    $bodyAttr[] = 'data-auth-open="' . htmlspecialchars($authOpenModal) . '"';
}
echo $bodyAttr !== [] ? ' ' . implode(' ', $bodyAttr) : '';
?>>
    <?php include 'includes/header.php'; ?>

    <main>
        <?php if ($authMessage !== '' && $view !== 'home'): ?>
            <div class="container auth-page-flash" role="status">
                <p class="auth-notice <?php echo $authSuccess ? 'auth-notice--ok' : 'auth-notice--err'; ?>">
                    <?php echo htmlspecialchars($authMessage); ?>
                </p>
            </div>
        <?php endif; ?>
        <?php include $includeFile; ?>
    </main>

    <?php if ($storefrontGuest) {
        include __DIR__ . '/includes/auth-modals.php';
    } ?>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <?php foreach ($extraScripts as $scriptSrc): ?>
        <script src="<?php echo htmlspecialchars($scriptSrc); ?>"></script>
    <?php endforeach; ?>
</body>
</html>
