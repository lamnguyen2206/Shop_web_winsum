<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

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
        redirect(app_url('home'));
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
        redirect(app_url('admin-customers', ['msg' => 'Phiên làm việc không hợp lệ.']));
    }

    $filters = customerAdminParseFilters(array_merge($_GET, $_POST));
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
            redirect($redirect . '&msg=' . urlencode($result['message']) . '&msg_ok=1');
        }
        redirect(customerAdminBuildListUrl($filters, $page) . '&msg=' . urlencode($result['message']) . '&msg_ok=0');
    }

    if ($action === 'toggle_customer_block') {
        $customerId = (int) ($_POST['customer_id'] ?? 0);
        $result = customerAdminToggleBlock($conn, $customerId, $actingId);
        if ($result['ok']) {
            $redirect = customerAdminBuildListUrl($filters, $page);
            if ($detailId === $customerId || $customerId > 0) {
                $redirect .= '&id=' . $customerId;
            }
            redirect($redirect . '&msg=' . urlencode($result['message']) . '&msg_ok=1');
        }
        redirect(customerAdminBuildListUrl($filters, $page) . '&msg=' . urlencode($result['message']) . '&msg_ok=0');
    }

    if ($action === 'delete_customer') {
        $customerId = (int) ($_POST['customer_id'] ?? $_POST['delete_customer_id'] ?? 0);
        if ($customerId <= 0) {
            redirect(customerAdminBuildListUrl($filters, $page) . '&msg=' . urlencode('Không xác định được khách hàng cần xóa. Hãy bấm biểu tượng thùng rác trên dòng khách hàng rồi xác nhận lại.') . '&msg_ok=0');
        }
        $result = customerAdminDelete($conn, $customerId, $actingId);
        if ($result['ok'] && $detailId === $customerId) {
            $detailId = 0;
        }
        $url = customerAdminBuildListUrl($filters, $page);
        if ($detailId > 0) {
            $url .= '&id=' . $detailId;
        }
        redirect($url . '&msg=' . urlencode($result['message']) . ($result['ok'] ? '&msg_ok=1' : '&msg_ok=0'));
    }

    redirect(customerAdminBuildListUrl($filters, $page) . '&msg=' . urlencode('Thao tác không hợp lệ.') . '&msg_ok=0');
}

function adminHandleProductsPost(mysqli $conn): void
{
    require_once __DIR__ . '/product-admin-repository.php';
    require_once __DIR__ . '/inventory-repository.php';

    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'save_product') {
        $result = productAdminSave($conn, $_POST);
        $msg = $result['message'];
        if (!$result['ok']) {
            $params = ['msg' => $msg];
            $editId = (int) ($_POST['id'] ?? 0);
            if ($editId > 0) {
                $params['edit'] = $editId;
            }
            redirect(app_url('admin-products', $params));
        }
        redirect(app_url('admin-products', ['msg' => $msg]));
    }

    if ($action === 'delete_product') {
        $deleteId = (int) ($_POST['product_id'] ?? 0);
        if (productAdminDelete($conn, $deleteId)) {
            redirect(app_url('admin-products', ['msg' => 'Đã ẩn sản phẩm khỏi cửa hàng.']));
        }
        return;
    }

    if ($action === 'mark_inventory_read') {
        $alertId = (int) ($_POST['alert_id'] ?? 0);
        if ($alertId > 0 && inventoryMarkAlertRead($conn, $alertId)) {
            redirect(app_url('admin-products', ['msg' => 'Đã đánh dấu đã xử lý cảnh báo tồn kho.']));
        }
        return;
    }

    if ($action === 'mark_all_inventory_read') {
        inventoryMarkAllAlertsRead($conn);
        redirect(app_url('admin-products', ['msg' => 'Đã đánh dấu tất cả cảnh báo tồn kho.']));
    }

    if ($action === 'apply_featured_from_sales') {
        $limit = max(1, min(12, (int) ($_POST['featured_limit'] ?? 6)));
        $result = productAdminApplyFeaturedFromSales($conn, $limit);
        redirect(app_url('admin-products', ['msg' => $result['message']]));
    }
}
