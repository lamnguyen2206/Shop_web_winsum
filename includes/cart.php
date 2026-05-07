<?php
require __DIR__ . '/cart-store.php';

$cartNotice = '';
$cartItems = cartGetItems();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? (string) $_POST['action'] : '';

    if ($action === 'update_qty' && isset($_POST['qty']) && is_array($_POST['qty'])) {
        $qtyMap = $_POST['qty'];
        foreach ($cartItems as &$item) {
            if (isset($qtyMap[$item['id']])) {
                $item['qty'] = max(1, (int) $qtyMap[$item['id']]);
            }
        }
        unset($item);
        cartSetItems($cartItems);
        $cartNotice = 'Đã cập nhật số lượng sản phẩm.';
    } elseif ($action === 'remove_item' && isset($_POST['item_id'])) {
        $itemId = (string) $_POST['item_id'];
        $cartItems = array_values(array_filter($cartItems, static function ($item) use ($itemId) {
            return $item['id'] !== $itemId;
        }));
        cartSetItems($cartItems);
        $cartNotice = 'Đã xóa sản phẩm khỏi giỏ hàng.';
    } elseif ($action === 'apply_coupon') {
        $coupon = strtoupper(trim((string) ($_POST['coupon_code'] ?? '')));
        if ($coupon === 'WINSUMXINCHAO') {
            $_SESSION['cart_coupon'] = $coupon;
            $cartNotice = 'Áp mã thành công: giảm 40.000đ.';
        } else {
            $_SESSION['cart_coupon'] = '';
            $cartNotice = 'Mã giảm giá không hợp lệ.';
        }
    }

    $cartItems = cartGetItems();
}

$totals = cartCalculateTotals($cartItems);
?>

<section class="container cart-page">
    <p class="breadcrumb"><a href="index.php">Trang chủ</a> / <span>Giỏ hàng</span></p>
    <h1>Giỏ hàng của bạn</h1>

    <?php if ($cartNotice !== ''): ?>
        <p class="cart-notice"><?php echo htmlspecialchars($cartNotice); ?></p>
    <?php endif; ?>

    <div class="cart-layout">
        <div>
            <?php if (empty($cartItems)): ?>
                <div class="cart-empty">
                    <p>Giỏ hàng của bạn đang trống.</p>
                    <a class="read-more" href="index.php?view=blog">Tiếp tục mua sắm</a>
                </div>
            <?php else: ?>
                <form method="post" action="index.php?view=cart">
                    <input type="hidden" name="action" value="update_qty">
                    <div class="cart-table">
                        <div class="cart-table-header">
                            <span>Sản phẩm</span>
                            <span>Đơn giá</span>
                            <span>Số lượng</span>
                            <span>Thành tiền</span>
                        </div>

                        <?php foreach ($cartItems as $index => $item): ?>
                            <article class="cart-row">
                                <div class="cart-product">
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    <div>
                                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                        <p>Mã sản phẩm: <?php echo htmlspecialchars($item['sku']); ?></p>
                                        <button class="remove-item-btn" type="submit" form="remove-<?php echo htmlspecialchars($item['id']); ?>">Xóa</button>
                                    </div>
                                </div>

                                <div class="cart-price">
                                    <?php echo number_format($item['price'], 0, ',', '.'); ?>đ
                                </div>

                                <div class="qty-control">
                                    <input type="number" min="1" value="<?php echo $item['qty']; ?>" name="qty[<?php echo htmlspecialchars($item['id']); ?>]" aria-label="Số lượng sản phẩm <?php echo $index + 1; ?>">
                                </div>

                                <div class="cart-total">
                                    <?php echo number_format($item['price'] * $item['qty'], 0, ',', '.'); ?>đ
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <button class="update-cart-btn" type="submit">CẬP NHẬT GIỎ HÀNG</button>
                </form>

                <?php foreach ($cartItems as $item): ?>
                    <form id="remove-<?php echo htmlspecialchars($item['id']); ?>" method="post" action="index.php?view=cart">
                        <input type="hidden" name="action" value="remove_item">
                        <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['id']); ?>">
                    </form>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <aside class="cart-summary">
            <h2>Tóm tắt đơn hàng</h2>
            <div class="summary-line">
                <span>Tạm tính</span>
                <strong><?php echo number_format($totals['subtotal'], 0, ',', '.'); ?>đ</strong>
            </div>
            <div class="summary-line">
                <span>Phí vận chuyển</span>
                <strong><?php echo number_format($totals['shipping'], 0, ',', '.'); ?>đ</strong>
            </div>
            <div class="summary-line">
                <span>Giảm giá</span>
                <strong><?php echo number_format($totals['discount'], 0, ',', '.'); ?>đ</strong>
            </div>
            <div class="summary-line total">
                <span>Tổng cộng</span>
                <strong><?php echo number_format($totals['total'], 0, ',', '.'); ?>đ</strong>
            </div>

            <form method="post" action="index.php?view=cart" class="coupon-form">
                <input type="hidden" name="action" value="apply_coupon">
                <input type="text" name="coupon_code" placeholder="Nhập mã giảm giá" value="<?php echo htmlspecialchars($_SESSION['cart_coupon'] ?? ''); ?>">
                <button type="submit">Áp dụng</button>
            </form>

            <a class="checkout-btn-link" href="index.php?view=checkout">TIẾN HÀNH THANH TOÁN</a>
            <p class="cart-help">Hotline hỗ trợ đặt hàng nhanh: <a href="tel:0387239676">0387 239 676</a></p>
        </aside>
    </div>
</section>
