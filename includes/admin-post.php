<?php

declare(strict_types=1);

/**
 * Xử lý POST trang quản trị trước khi xuất HTML (tránh lỗi headers already sent).
 */
function adminHandlePost(mysqli $conn, string $view): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    require_once __DIR__ . '/csrf.php';
    require_once __DIR__ . '/customer-auth.php';
    require_once __DIR__ . '/admin-auth.php';

    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'admin_logout') {
        if (!csrfValidate()) {
            return;
        }
        customerLogout();
        $_SESSION['auth_flash'] = [
            'message' => 'Bạn đã đăng xuất.',
            'success' => true,
            'open' => null,
            'prefill' => [],
        ];
        header('Location: index.php?view=home', true, 303);
        exit;
    }

    if (!adminCurrent()) {
        return;
    }

    if ($view === 'admin-customers') {
        adminHandleCustomersPost($conn);
        return;
    }

    if ($view === 'admin-products' && csrfValidate()) {
        adminHandleProductsPost($conn);
    }
}

function adminHandleCustomersPost(mysqli $conn): void
{
    require_once __DIR__ . '/customer-admin-repository.php';

    if (!csrfValidate()) {
        header('Location: index.php?view=admin-customers&msg=' . urlencode('Phiên làm việc không hợp lệ.'));
        exit;
    }

    $filters = customerAdminParseFilters($_GET);
    $perPage = 20;
    $total = customerAdminCount($conn, $filters);
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page = min($filters['page'], $totalPages);
    $detailId = (int) ($_GET['id'] ?? 0);
    $actingId = (int) ($_SESSION['customer_id'] ?? 0);
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'update_customer_status') {
        $customerId = (int) ($_POST['customer_id'] ?? 0);
        $newStatus = trim((string) ($_POST['status'] ?? ''));
        $result = customerAdminUpdateStatus($conn, $customerId, $newStatus, $actingId);
        if ($result['ok']) {
            $redirect = customerAdminBuildListUrl($filters, $page) . '&id=' . $customerId;
            header('Location: ' . $redirect . '&msg=' . urlencode($result['message']));
            exit;
        }
        header('Location: ' . customerAdminBuildListUrl($filters, $page) . '&msg=' . urlencode($result['message']));
        exit;
    }

    if ($action === 'toggle_customer_block') {
        $customerId = (int) ($_POST['customer_id'] ?? 0);
        $result = customerAdminToggleBlock($conn, $customerId, $actingId);
        if ($result['ok']) {
            $redirect = customerAdminBuildListUrl($filters, $page);
            if ($detailId === $customerId || $customerId > 0) {
                $redirect .= '&id=' . $customerId;
            }
            header('Location: ' . $redirect . '&msg=' . urlencode($result['message']));
            exit;
        }
        header('Location: ' . customerAdminBuildListUrl($filters, $page) . '&msg=' . urlencode($result['message']));
        exit;
    }

    if ($action === 'delete_customer') {
        $customerId = (int) ($_POST['customer_id'] ?? 0);
        $result = customerAdminDelete($conn, $customerId, $actingId);
        $url = customerAdminBuildListUrl($filters, $page) . '&msg=' . urlencode($result['message']);
        header('Location: ' . $url);
        exit;
    }
}

function adminHandleProductsPost(mysqli $conn): void
{
    require_once __DIR__ . '/product-admin-repository.php';
    require_once __DIR__ . '/inventory-repository.php';

    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'save_product') {
        $result = productAdminSave($conn, $_POST);
        if ($result['ok']) {
            header('Location: index.php?view=admin-products&msg=' . urlencode($result['message']));
            exit;
        }
        return;
    }

    if ($action === 'delete_product') {
        $deleteId = (int) ($_POST['product_id'] ?? 0);
        if (productAdminDelete($conn, $deleteId)) {
            header('Location: index.php?view=admin-products&msg=' . urlencode('Đã ẩn sản phẩm khỏi cửa hàng.'));
            exit;
        }
        return;
    }

    if ($action === 'mark_inventory_read') {
        $alertId = (int) ($_POST['alert_id'] ?? 0);
        if ($alertId > 0 && inventoryMarkAlertRead($conn, $alertId)) {
            header('Location: index.php?view=admin-products&msg=' . urlencode('Đã đánh dấu đã xử lý cảnh báo tồn kho.'));
            exit;
        }
        return;
    }

    if ($action === 'mark_all_inventory_read') {
        inventoryMarkAllAlertsRead($conn);
        header('Location: index.php?view=admin-products&msg=' . urlencode('Đã đánh dấu tất cả cảnh báo tồn kho.'));
        exit;
    }

    if ($action === 'apply_featured_from_sales') {
        $limit = max(1, min(12, (int) ($_POST['featured_limit'] ?? 6)));
        $result = productAdminApplyFeaturedFromSales($conn, $limit);
        header('Location: index.php?view=admin-products&msg=' . urlencode($result['message']));
        exit;
    }
}
