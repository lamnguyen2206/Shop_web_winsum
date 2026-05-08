<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function cartGetItems(): array
{
    if (!isset($_SESSION['cart_items']) || !is_array($_SESSION['cart_items'])) {
        $_SESSION['cart_items'] = [];
    }
    return $_SESSION['cart_items'];
}

function cartSetItems(array $items): void
{
    $_SESSION['cart_items'] = array_values($items);
}

function cartAddItem(array $item, int $qty = 1): void
{
    $items = cartGetItems();
    $itemId = (string) $item['id'];
    $qty = max(1, $qty);

    foreach ($items as &$existing) {
        if ((string) $existing['id'] === $itemId) {
            $existing['qty'] = (int) $existing['qty'] + $qty;
            cartSetItems($items);
            return;
        }
    }
    unset($existing);

    $items[] = [
        'id' => $itemId,
        'product_id' => (int) ($item['product_id'] ?? 0),
        'name' => (string) $item['name'],
        'slug' => (string) ($item['slug'] ?? ''),
        'sku' => (string) $item['sku'],
        'price' => (int) $item['price'],
        'qty' => $qty,
        'image' => (string) $item['image']
    ];
    cartSetItems($items);
}

function cartRemoveItemById(string $itemId): void
{
    $items = array_values(array_filter(cartGetItems(), static function (array $item) use ($itemId) {
        return (string) $item['id'] !== $itemId;
    }));
    cartSetItems($items);
}

function cartUpdateQuantities(array $qtyMap): void
{
    $items = cartGetItems();
    foreach ($items as &$item) {
        if (isset($qtyMap[$item['id']])) {
            $item['qty'] = max(1, (int) $qtyMap[$item['id']]);
        }
    }
    unset($item);
    cartSetItems($items);
}

function cartCountItems(): int
{
    $count = 0;
    foreach (cartGetItems() as $item) {
        $count += (int) $item['qty'];
    }
    return $count;
}

function cartClear(): void
{
    $_SESSION['cart_items'] = [];
    $_SESSION['cart_coupon'] = '';
}

function cartCalculateTotals(array $items): array
{
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += ((int) $item['price']) * ((int) $item['qty']);
    }

    $shipping = isset($_SESSION['selected_shipping_fee']) ? (int) $_SESSION['selected_shipping_fee'] : ($subtotal > 0 ? 30000 : 0);
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
