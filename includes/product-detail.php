<?php
require_once __DIR__ . '/product-repository.php';
require_once __DIR__ . '/cart-store.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$product = productGetBySlug($conn, $slug);
$detailNotice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_to_cart' && $product) {
    $qty = max(1, (int) ($_POST['qty'] ?? 1));
    if ($product['stock_status'] === 'out_of_stock') {
        $detailNotice = 'Sản phẩm đang tạm hết hàng.';
    } else {
        cartAddItem([
            'id' => 'product-' . $product['id'],
            'product_id' => $product['id'],
            'slug' => $product['slug'],
            'name' => $product['name'],
            'sku' => $product['sku'],
            'price' => (int) round($product['base_price']),
            'image' => $product['images'][0]['url'] ?? 'assets/images/blog_1.png'
        ], $qty);
        $detailNotice = 'Đã thêm sản phẩm vào giỏ hàng.';
    }
}

if (!$product) {
    http_response_code(404);
    ?>
    <section class="container product-page">
        <h1>Không tìm thấy sản phẩm</h1>
        <p>Sản phẩm có thể đã được cập nhật hoặc không còn hiển thị.</p>
        <a href="index.php?view=catalog" class="btn-secondary">Quay lại danh mục</a>
    </section>
    <?php
    return;
}

$related = productGetRelatedByCategory($conn, $product['category_id'], $product['id'], 4);
?>

<section class="container product-page">
    <p class="breadcrumb">
        <a href="index.php?view=home">Trang chủ</a> /
        <a href="index.php?view=catalog&amp;category=<?php echo urlencode($product['category_slug']); ?>"><?php echo htmlspecialchars($product['category_name']); ?></a> /
        <span><?php echo htmlspecialchars($product['name']); ?></span>
    </p>

    <?php if ($detailNotice !== ''): ?>
        <p class="catalog-notice"><?php echo htmlspecialchars($detailNotice); ?></p>
    <?php endif; ?>

    <div class="product-layout">
        <div class="product-gallery">
            <img class="product-cover" src="<?php echo htmlspecialchars($product['images'][0]['url']); ?>" alt="<?php echo htmlspecialchars($product['images'][0]['alt']); ?>">
            <?php if (count($product['images']) > 1): ?>
                <div class="thumb-grid">
                    <?php foreach ($product['images'] as $image): ?>
                        <img src="<?php echo htmlspecialchars($image['url']); ?>" alt="<?php echo htmlspecialchars($image['alt']); ?>">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="product-summary">
            <p class="catalog-category"><?php echo htmlspecialchars($product['category_name']); ?></p>
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="price-block">
                <strong><?php echo htmlspecialchars($product['price_label']); ?></strong>
                <?php if ($product['compare_price_label']): ?>
                    <span><?php echo htmlspecialchars($product['compare_price_label']); ?></span>
                <?php endif; ?>
            </div>
            <p class="product-short"><?php echo htmlspecialchars($product['short_description']); ?></p>

            <ul class="product-attrs">
                <?php if ($product['material'] !== ''): ?><li><span>Chất liệu:</span> <?php echo htmlspecialchars($product['material']); ?></li><?php endif; ?>
                <?php if ($product['color'] !== ''): ?><li><span>Màu sắc:</span> <?php echo htmlspecialchars($product['color']); ?></li><?php endif; ?>
                <?php if ($product['warranty_months'] !== null): ?><li><span>Bảo hành:</span> <?php echo (int) $product['warranty_months']; ?> tháng</li><?php endif; ?>
                <li><span>SKU:</span> <?php echo htmlspecialchars($product['sku']); ?></li>
            </ul>

            <form method="post" action="index.php?view=product&amp;slug=<?php echo urlencode($product['slug']); ?>" class="add-cart-form">
                <input type="hidden" name="action" value="add_to_cart">
                <label for="qty">Số lượng</label>
                <input id="qty" type="number" name="qty" min="1" value="1">
                <button type="submit" <?php echo $product['stock_status'] === 'out_of_stock' ? 'disabled' : ''; ?>>
                    <?php echo $product['stock_status'] === 'out_of_stock' ? 'Hết hàng' : 'Thêm vào giỏ hàng'; ?>
                </button>
            </form>
        </div>
    </div>

    <article class="product-description">
        <h2>Mô tả sản phẩm</h2>
        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
    </article>

    <?php if (!empty($related)): ?>
        <section class="related-products">
            <h2>Sản phẩm liên quan</h2>
            <div class="catalog-grid">
                <?php foreach ($related as $item): ?>
                    <article class="catalog-card">
                        <a href="index.php?view=product&amp;slug=<?php echo urlencode($item['slug']); ?>" class="catalog-image">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        </a>
                        <div class="catalog-content">
                            <p class="catalog-category"><?php echo htmlspecialchars($item['category_name']); ?></p>
                            <h3><a href="index.php?view=product&amp;slug=<?php echo urlencode($item['slug']); ?>"><?php echo htmlspecialchars($item['name']); ?></a></h3>
                            <p class="catalog-price"><?php echo htmlspecialchars($item['price_label']); ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</section>
