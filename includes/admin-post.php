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

    $customerAdminActions = ['toggle_customer_block', 'update_customer_status'];
    if (in_array($action, $customerAdminActions, true)) {
        adminHandleCustomersPost($conn);
        return;
    }

    if ($view === 'admin-customers') {
        adminHandleCustomersPost($conn);
        return;
    }

    if ($view === 'admin-products' && csrfValidate()) {
        adminHandleProductsPost($conn);
    }

    if (in_array($view, ['admin-orders', 'admin-order-detail'], true) && csrfValidate()) {
        adminHandleOrdersPost($conn, $view);
    }
}

function adminHandleOrdersPost(mysqli $conn, string $view): void
{
    require_once __DIR__ . '/order-repository.php';

    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'update_payment_status') {
        $orderId = (int) ($_POST['order_id'] ?? 0);
        $paymentStatus = (string) ($_POST['payment_status'] ?? '');
        $ok = orderUpdatePaymentStatus($conn, $orderId, $paymentStatus);
        $stmt = $conn->prepare('SELECT order_code FROM orders WHERE id = ? LIMIT 1');
        $code = '';
        if ($stmt) {
            $stmt->bind_param('i', $orderId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $code = (string) ($row['order_code'] ?? '');
            $stmt->close();
        }
        redirect(app_url('admin-order-detail', [
            'code' => $code,
            'msg' => $ok ? 'Đã cập nhật trạng thái thanh toán.' : 'Không thể cập nhật thanh toán.',
        ]));
    }

    if ($action === 'update_fulfillment_status') {
        $orderId = (int) ($_POST['order_id'] ?? 0);
        $fulfillment = (string) ($_POST['fulfillment_status'] ?? '');
        $ok = orderUpdateFulfillmentStatus($conn, $orderId, $fulfillment);
        $stmt = $conn->prepare('SELECT order_code FROM orders WHERE id = ? LIMIT 1');
        $code = '';
        if ($stmt) {
            $stmt->bind_param('i', $orderId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $code = (string) ($row['order_code'] ?? '');
            $stmt->close();
        }
        redirect(app_url('admin-order-detail', [
            'code' => $code,
            'msg' => $ok ? 'Đã cập nhật trạng thái giao hàng.' : 'Không thể cập nhật giao hàng.',
        ]));
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
    $actingId = adminManagementActingCustomerId();
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

}
