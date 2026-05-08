<?php
require_once __DIR__ . '/../config/database.php';

function orderGetShippingMethods(mysqli $conn): array
{
    $result = $conn->query("SELECT id, code, name, fee, eta_label FROM shipping_methods WHERE is_active = 1 ORDER BY fee ASC, id ASC");
    if (!$result) {
        return [];
    }
    $methods = [];
    while ($row = $result->fetch_assoc()) {
        $methods[] = [
            'id' => (int) $row['id'],
            'code' => $row['code'],
            'name' => $row['name'],
            'fee' => (float) $row['fee'],
            'eta_label' => $row['eta_label'] ?? ''
        ];
    }
    return $methods;
}

function orderGetPaymentMethods(mysqli $conn): array
{
    $result = $conn->query("SELECT id, code, name, description FROM payment_methods WHERE is_active = 1 ORDER BY id ASC");
    if (!$result) {
        return [];
    }
    $methods = [];
    while ($row = $result->fetch_assoc()) {
        $methods[] = [
            'id' => (int) $row['id'],
            'code' => $row['code'],
            'name' => $row['name'],
            'description' => $row['description'] ?? ''
        ];
    }
    return $methods;
}

function orderGenerateCode(): string
{
    return 'WS' . date('YmdHis') . random_int(100, 999);
}

function orderCreateFromCheckout(
    mysqli $conn,
    array $customer,
    array $cartItems,
    array $totals,
    ?string $couponCode,
    ?int $customerId,
    int $shippingMethodId,
    int $paymentMethodId
): string {
    $orderCode = orderGenerateCode();
    $couponCode = $couponCode ?: null;
    $customerEmail = $customer['email'] !== '' ? $customer['email'] : null;
    $customerNote = $customer['note'] !== '' ? $customer['note'] : null;

    $conn->begin_transaction();
    try {
        $stmtOrder = $conn->prepare("INSERT INTO orders
            (order_code, customer_id, customer_name, customer_phone, customer_email, customer_address, customer_note,
             coupon_code, subtotal, shipping_fee, discount_amount, grand_total, status, fulfillment_status, payment_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', 'unpaid')");
        if (!$stmtOrder) {
            throw new RuntimeException('Không tạo được lệnh lưu đơn hàng.');
        }

        $customerIdValue = $customerId ?: null;
        $subtotal = (float) $totals['subtotal'];
        $shipping = (float) $totals['shipping'];
        $discount = (float) $totals['discount'];
        $grandTotal = (float) $totals['total'];

        $stmtOrder->bind_param(
            'sissssssdddd',
            $orderCode,
            $customerIdValue,
            $customer['name'],
            $customer['phone'],
            $customerEmail,
            $customer['address'],
            $customerNote,
            $couponCode,
            $subtotal,
            $shipping,
            $discount,
            $grandTotal
        );
        $stmtOrder->execute();
        $orderId = (int) $stmtOrder->insert_id;
        $stmtOrder->close();

        $stmtItem = $conn->prepare("INSERT INTO order_items
            (order_id, product_id, product_sku, product_name, product_image, unit_price, quantity, line_total)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmtItem) {
            throw new RuntimeException('Không tạo được lệnh lưu chi tiết đơn hàng.');
        }
        foreach ($cartItems as $item) {
            $productId = isset($item['product_id']) && (int) $item['product_id'] > 0 ? (int) $item['product_id'] : null;
            $sku = (string) $item['sku'];
            $name = (string) $item['name'];
            $image = (string) $item['image'];
            $unitPrice = (float) $item['price'];
            $quantity = (int) $item['qty'];
            $lineTotal = $unitPrice * $quantity;
            $stmtItem->bind_param('iisssdid', $orderId, $productId, $sku, $name, $image, $unitPrice, $quantity, $lineTotal);
            $stmtItem->execute();
        }
        $stmtItem->close();

        $stmtShipment = $conn->prepare("INSERT INTO order_shipments
            (order_id, shipping_method_id, recipient_name, recipient_phone, recipient_address, shipping_fee, status)
            VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        if (!$stmtShipment) {
            throw new RuntimeException('Không tạo được lệnh vận chuyển.');
        }
        $stmtShipment->bind_param(
            'iisssd',
            $orderId,
            $shippingMethodId,
            $customer['name'],
            $customer['phone'],
            $customer['address'],
            $shipping
        );
        $stmtShipment->execute();
        $stmtShipment->close();

        $transactionCode = null;
        $gatewayResponse = null;
        $paymentStatus = 'pending';
        $paidAt = null;
        $stmtPayment = $conn->prepare("INSERT INTO order_payments
            (order_id, payment_method_id, amount, transaction_code, gateway_response, status, paid_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmtPayment) {
            throw new RuntimeException('Không tạo được lệnh thanh toán.');
        }
        $stmtPayment->bind_param('iidssss', $orderId, $paymentMethodId, $grandTotal, $transactionCode, $gatewayResponse, $paymentStatus, $paidAt);
        $stmtPayment->execute();
        $stmtPayment->close();

        $stmtHistory = $conn->prepare("INSERT INTO order_status_histories
            (order_id, from_status, to_status, note, changed_by)
            VALUES (?, NULL, 'pending', 'Đơn hàng mới tạo từ storefront', 'customer')");
        if ($stmtHistory) {
            $stmtHistory->bind_param('i', $orderId);
            $stmtHistory->execute();
            $stmtHistory->close();
        }

        $conn->commit();
        return $orderCode;
    } catch (Throwable $e) {
        $conn->rollback();
        throw $e;
    }
}

function orderGetCustomerOrders(mysqli $conn, int $customerId): array
{
    $stmt = $conn->prepare("SELECT id, order_code, status, payment_status, fulfillment_status, grand_total, ordered_at
                            FROM orders
                            WHERE customer_id = ?
                            ORDER BY id DESC");
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('i', $customerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    $stmt->close();
    return $orders;
}

function orderGetCustomerOrderDetailByCode(mysqli $conn, int $customerId, string $orderCode): ?array
{
    $stmt = $conn->prepare("SELECT id, order_code, customer_name, customer_phone, customer_email, customer_address, customer_note,
                                   subtotal, shipping_fee, discount_amount, grand_total, status, payment_status, fulfillment_status, ordered_at
                            FROM orders
                            WHERE customer_id = ?
                              AND order_code = ?
                            LIMIT 1");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('is', $customerId, $orderCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();
    if (!$order) {
        return null;
    }

    $orderId = (int) $order['id'];
    $stmtItems = $conn->prepare("SELECT product_sku, product_name, product_image, unit_price, quantity, line_total
                                 FROM order_items
                                 WHERE order_id = ?
                                 ORDER BY id ASC");
    $items = [];
    if ($stmtItems) {
        $stmtItems->bind_param('i', $orderId);
        $stmtItems->execute();
        $itemsResult = $stmtItems->get_result();
        while ($row = $itemsResult->fetch_assoc()) {
            $items[] = $row;
        }
        $stmtItems->close();
    }

    $stmtShipment = $conn->prepare("SELECT os.recipient_name, os.recipient_phone, os.recipient_address, os.shipping_fee, os.status,
                                           sm.name AS shipping_method_name, sm.eta_label
                                    FROM order_shipments os
                                    LEFT JOIN shipping_methods sm ON sm.id = os.shipping_method_id
                                    WHERE os.order_id = ?
                                    ORDER BY os.id DESC
                                    LIMIT 1");
    $shipment = null;
    if ($stmtShipment) {
        $stmtShipment->bind_param('i', $orderId);
        $stmtShipment->execute();
        $shipment = $stmtShipment->get_result()->fetch_assoc() ?: null;
        $stmtShipment->close();
    }

    $stmtPayment = $conn->prepare("SELECT op.status, op.amount, pm.name AS payment_method_name
                                   FROM order_payments op
                                   LEFT JOIN payment_methods pm ON pm.id = op.payment_method_id
                                   WHERE op.order_id = ?
                                   ORDER BY op.id DESC
                                   LIMIT 1");
    $payment = null;
    if ($stmtPayment) {
        $stmtPayment->bind_param('i', $orderId);
        $stmtPayment->execute();
        $payment = $stmtPayment->get_result()->fetch_assoc() ?: null;
        $stmtPayment->close();
    }

    $order['items'] = $items;
    $order['shipment'] = $shipment;
    $order['payment'] = $payment;
    return $order;
}
