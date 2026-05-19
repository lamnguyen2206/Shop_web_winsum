<?php
/**
 * Khối tài khoản trên trang chủ (biến từ index.php: $currentCustomer, $authMessage, $authSuccess).
 */
if (!$currentCustomer && $authMessage === '') {
    return;
}
?>

<section class="home-auth-section" id="home-auth">
    <div class="container">
        <?php if ($authMessage !== ''): ?>
            <p class="auth-notice <?php echo $authSuccess ? 'auth-notice--ok' : 'auth-notice--err'; ?>">
                <?php echo htmlspecialchars($authMessage); ?>
            </p>
        <?php endif; ?>

        <?php if ($currentCustomer): ?>
            <div class="auth-welcome-card">
                <h2>Xin chào, <?php echo htmlspecialchars($currentCustomer['full_name']); ?></h2>
                <p>Bạn đã đăng nhập · Mã KH: <?php echo htmlspecialchars($currentCustomer['customer_code']); ?></p>
                <div class="auth-welcome-actions">
                    <a href="index.php?view=catalog">Mua sắm ngay</a>
                    <a href="index.php?view=orders" class="btn-outline">Đơn hàng của tôi</a>
                    <a href="index.php?view=account" class="btn-outline">Tài khoản</a>
                    <form method="post" action="">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="auth_action" value="logout">
                        <button type="submit" class="btn-outline">Đăng xuất</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
