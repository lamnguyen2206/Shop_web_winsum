<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function cartSeedItems(): array
{
    return [
        [
            'id' => 'ws-axis-01',
            'name' => 'Đèn treo trần AXIS thông minh',
            'sku' => 'WS-AXIS-01',
            'price' => 2890000,
            'qty' => 1,
            'image' => 'assets/images/blog_1.png'
        ],
        [
            'id' => 'ws-bau-02',
            'name' => 'Đèn treo BAUHAUS phòng khách',
            'sku' => 'WS-BAU-02',
            'price' => 2190000,
            'qty' => 1,
            'image' => 'assets/images/blog_2.png'
        ],
        [
            'id' => 'ws-ph5-03',
            'name' => 'PH5 Pendant Lamp Bắc Âu',
            'sku' => 'WS-PH5-03',
            'price' => 3250000,
            'qty' => 1,
            'image' => 'assets/images/blog_3.png'
        ]
    ];
}

function cartGetItems(): array
{
    if (!isset($_SESSION['cart_items']) || !is_array($_SESSION['cart_items'])) {
        $_SESSION['cart_items'] = cartSeedItems();
    }
    return $_SESSION['cart_items'];
}

function cartSetItems(array $items): void
{
    $_SESSION['cart_items'] = array_values($items);
}

function cartCalculateTotals(array $items): array
{
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += ((int) $item['price']) * ((int) $item['qty']);
    }

    $shipping = $subtotal > 0 ? 30000 : 0;
    $discount = 0;
    $coupon = $_SESSION['cart_coupon'] ?? '';

    if ($coupon === 'WINSUMXINCHAO') {
        $discount = min(40000, $subtotal);
    }

    return [
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'discount' => $discount,
        'total' => max(0, $subtotal + $shipping - $discount)
    ];
}
