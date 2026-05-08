<?php
require_once __DIR__ . '/customer-auth.php';

$accountMessage = '';
$currentCustomer = customerCurrent($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'register') {
        $result = customerRegister(
            $conn,
            (string) ($_POST['full_name'] ?? ''),
            (string) ($_POST['phone'] ?? ''),
            (string) ($_POST['email'] ?? ''),
            (string) ($_POST['password'] ?? '')
        );
        $accountMessage = $result['message'];
        $currentCustomer = customerCurrent($conn);
    } elseif ($action === 'login') {
        $result = customerLogin(
            $conn,
            (string) ($_POST['identifier'] ?? ''),
            (string) ($_POST['password'] ?? '')
        );
        $accountMessage = $result['message'];
        $currentCustomer = customerCurrent($conn);
    } elseif ($action === 'logout') {
        customerLogout();
        $accountMessage = 'Bạn đã đăng xuất.';
        $currentCustomer = null;
    }
}
?>

<section class="container account-page">
    <p class="breadcrumb"><a href="index.php?view=home">Trang chủ</a> / <span>Tài khoản</span></p>
    <h1>Tài khoản khách hàng</h1>

    <?php if ($accountMessage !== ''): ?>
        <p class="account-notice"><?php echo htmlspecialchars($accountMessage); ?></p>
    <?php endif; ?>

    <?php if ($currentCustomer): ?>
        <div class="account-card">
            <h2>Xin chào, <?php echo htmlspecialchars($currentCustomer['full_name']); ?></h2>
            <p>Mã khách hàng: <strong><?php echo htmlspecialchars($currentCustomer['customer_code']); ?></strong></p>
            <p>Số điện thoại: <?php echo htmlspecialchars($currentCustomer['phone']); ?></p>
            <p>Email: <?php echo htmlspecialchars((string) ($currentCustomer['email'] ?? 'Chưa cập nhật')); ?></p>
            <div class="account-actions">
                <a class="btn-secondary" href="index.php?view=orders">Xem đơn hàng của tôi</a>
                <form method="post" action="index.php?view=account">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit">Đăng xuất</button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="account-layout">
            <form method="post" action="index.php?view=account" class="account-form">
                <h2>Đăng nhập</h2>
                <input type="hidden" name="action" value="login">
                <label for="identifier">Số điện thoại hoặc email</label>
                <input id="identifier" type="text" name="identifier" required>
                <label for="login_password">Mật khẩu</label>
                <input id="login_password" type="password" name="password" required>
                <button type="submit">Đăng nhập</button>
            </form>

            <form method="post" action="index.php?view=account" class="account-form">
                <h2>Tạo tài khoản</h2>
                <input type="hidden" name="action" value="register">
                <label for="full_name">Họ và tên</label>
                <input id="full_name" type="text" name="full_name" required>
                <label for="phone">Số điện thoại</label>
                <input id="phone" type="text" name="phone" required>
                <label for="email">Email</label>
                <input id="email" type="email" name="email">
                <label for="register_password">Mật khẩu</label>
                <input id="register_password" type="password" name="password" required>
                <button type="submit">Đăng ký</button>
            </form>
        </div>
    <?php endif; ?>
</section>
