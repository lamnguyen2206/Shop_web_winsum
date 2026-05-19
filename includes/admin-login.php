<?php
require_once __DIR__ . '/admin-auth.php';
require_once __DIR__ . '/csrf.php';

$loginMessage = '';
$redirect = trim((string) ($_GET['redirect'] ?? 'admin-dashboard'));
if ($redirect === '' || $redirect === 'admin-login') {
    $redirect = 'admin-dashboard';
}

if (adminCurrent()) {
    header('Location: index.php?view=' . urlencode($redirect));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValidate()) {
        $loginMessage = 'Phiên làm việc không hợp lệ. Vui lòng thử lại.';
    } else {
        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'admin_login') {
            $result = adminLogin(
                trim((string) ($_POST['username'] ?? '')),
                (string) ($_POST['password'] ?? '')
            );
            $loginMessage = $result['message'];
            if ($result['ok']) {
                header('Location: index.php?view=' . urlencode($redirect));
                exit;
            }
        }
    }
}
?>

<section class="auth-page-single">
    <article class="auth-card">
        <h2>Đăng nhập quản trị</h2>
        <?php if ($loginMessage !== ''): ?>
            <p class="auth-hint" style="color:#fca5a5;margin-bottom:12px;"><?php echo htmlspecialchars($loginMessage); ?></p>
        <?php endif; ?>
        <form method="post" action="index.php?view=admin-login&amp;redirect=<?php echo urlencode($redirect); ?>" class="auth-form">
            <?php echo csrfField(); ?>
            <input type="hidden" name="action" value="admin_login">
            <div class="auth-field">
                <input id="username" type="text" name="username" placeholder="Tên đăng nhập" required autocomplete="username">
            </div>
            <div class="auth-field auth-field--toggle">
                <input id="password" type="password" name="password" placeholder="Nhập mật khẩu" required autocomplete="current-password">
                <button type="button" class="auth-toggle-pw" data-toggle-password aria-label="Hiện mật khẩu">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
            <button type="submit" class="auth-submit">Đăng nhập</button>
        </form>
        <p class="auth-footer"><a href="index.php?view=home">← Về trang chủ</a></p>
        <p class="auth-hint" style="margin-top:12px;">Mặc định: admin / admin123</p>
    </article>
</section>
