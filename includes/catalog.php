<?php
require_once __DIR__ . '/product-repository.php';
require_once __DIR__ . '/cart-store.php';

$catalogNotice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_to_cart') {
    $productId = (int) ($_POST['product_id'] ?? 0);
    $qty = max(1, (int) ($_POST['qty'] ?? 1));
    $product = productGetById($conn, $productId);
    if ($product && $product['stock_status'] !== 'out_of_stock') {
        cartAddItem([
            'id' => 'product-' . $product['id'],
            'product_id' => $product['id'],
            'slug' => $product['slug'],
            'name' => $product['name'],
            'sku' => $product['sku'],
            'price' => $product['price'],
            'image' => $product['image']
        ], $qty);
        $catalogNotice = 'Đã thêm sản phẩm vào giỏ hàng.';
    } else {
        $catalogNotice = 'Sản phẩm hiện không khả dụng.';
    }
}

$filters = productBuildFiltersFromRequest();
$categories = productGetFilterCategories($conn);
$brands = productGetFilterBrands($conn);
$materials = productGetFilterMaterialOptions($conn);
$colors = productGetFilterColorOptions($conn);
$priceRange = productGetAvailablePriceRange($conn);
$products = productSearchProducts($conn, $filters, 24);
?>

<section class="container catalog-page">
    <p class="breadcrumb"><a href="index.php?view=home">Trang chủ</a> / <span>Sản phẩm</span></p>
    <div class="catalog-head">
        <h1>Tất cả sản phẩm</h1>
        <p>Khám phá bộ sưu tập nội thất và chiếu sáng cao cấp từ Winsum Home.</p>
    </div>

    <?php if ($catalogNotice !== ''): ?>
        <p class="catalog-notice"><?php echo htmlspecialchars($catalogNotice); ?></p>
    <?php endif; ?>

    <div class="catalog-layout">
        <aside class="filter-panel">
            <h2>Bộ lọc</h2>
            <form method="get" action="index.php" class="filter-form">
                <input type="hidden" name="view" value="catalog">

                <label for="q">Tìm kiếm</label>
                <input id="q" type="text" name="q" value="<?php echo htmlspecialchars($filters['q']); ?>" placeholder="Tên sản phẩm...">

                <label for="category">Danh mục</label>
                <select id="category" name="category">
                    <option value="">Tất cả</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['slug']); ?>" <?php echo $filters['category'] === $category['slug'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="brand">Thương hiệu</label>
                <select id="brand" name="brand">
                    <option value="">Tất cả</option>
                    <?php foreach ($brands as $brand): ?>
                        <option value="<?php echo htmlspecialchars($brand['slug']); ?>" <?php echo $filters['brand'] === $brand['slug'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($brand['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="material">Chất liệu</label>
                <select id="material" name="material">
                    <option value="">Tất cả</option>
                    <?php foreach ($materials as $material): ?>
                        <option value="<?php echo htmlspecialchars($material); ?>" <?php echo $filters['material'] === $material ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($material); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="color">Màu sắc</label>
                <select id="color" name="color">
                    <option value="">Tất cả</option>
                    <?php foreach ($colors as $color): ?>
                        <option value="<?php echo htmlspecialchars($color); ?>" <?php echo $filters['color'] === $color ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($color); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="stock">Tình trạng</label>
                <select id="stock" name="stock">
                    <option value="">Tất cả</option>
                    <option value="in_stock" <?php echo $filters['stock'] === 'in_stock' ? 'selected' : ''; ?>>Còn hàng</option>
                    <option value="preorder" <?php echo $filters['stock'] === 'preorder' ? 'selected' : ''; ?>>Đặt trước</option>
                    <option value="out_of_stock" <?php echo $filters['stock'] === 'out_of_stock' ? 'selected' : ''; ?>>Hết hàng</option>
                </select>

                <div class="price-row">
                    <div>
                        <label for="min_price">Giá từ</label>
                        <input id="min_price" type="number" min="0" name="min_price" value="<?php echo (int) $filters['min_price']; ?>" placeholder="<?php echo (int) $priceRange['min']; ?>">
                    </div>
                    <div>
                        <label for="max_price">Đến</label>
                        <input id="max_price" type="number" min="0" name="max_price" value="<?php echo (int) $filters['max_price']; ?>" placeholder="<?php echo (int) $priceRange['max']; ?>">
                    </div>
                </div>

                <label for="sort">Sắp xếp</label>
                <select id="sort" name="sort">
                    <option value="featured" <?php echo $filters['sort'] === 'featured' ? 'selected' : ''; ?>>Nổi bật</option>
                    <option value="latest" <?php echo $filters['sort'] === 'latest' ? 'selected' : ''; ?>>Mới nhất</option>
                    <option value="price_asc" <?php echo $filters['sort'] === 'price_asc' ? 'selected' : ''; ?>>Giá tăng dần</option>
                    <option value="price_desc" <?php echo $filters['sort'] === 'price_desc' ? 'selected' : ''; ?>>Giá giảm dần</option>
                    <option value="name_asc" <?php echo $filters['sort'] === 'name_asc' ? 'selected' : ''; ?>>Tên A-Z</option>
                </select>

                <button type="submit">Áp dụng bộ lọc</button>
            </form>
        </aside>

        <div class="catalog-results">
            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <p>Chưa có sản phẩm phù hợp với bộ lọc bạn đã chọn.</p>
                </div>
            <?php else: ?>
                <div class="catalog-grid">
                    <?php foreach ($products as $product): ?>
                        <article class="catalog-card">
                            <a href="index.php?view=product&amp;slug=<?php echo urlencode($product['slug']); ?>" class="catalog-image">
                                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </a>
                            <div class="catalog-content">
                                <p class="catalog-category"><?php echo htmlspecialchars($product['category_name']); ?> · <?php echo htmlspecialchars($product['brand_name']); ?></p>
                                <h3><a href="index.php?view=product&amp;slug=<?php echo urlencode($product['slug']); ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                                <p class="catalog-price"><?php echo htmlspecialchars($product['price_label']); ?></p>
                                <p class="catalog-desc"><?php echo htmlspecialchars($product['short_description']); ?></p>
                                <div class="catalog-actions">
                                    <a href="index.php?view=product&amp;slug=<?php echo urlencode($product['slug']); ?>" class="btn-secondary">Xem chi tiết</a>
                                    <form method="post" action="index.php?view=catalog">
                                        <input type="hidden" name="action" value="add_to_cart">
                                        <input type="hidden" name="product_id" value="<?php echo (int) $product['id']; ?>">
                                        <input type="hidden" name="qty" value="1">
                                        <button type="submit" <?php echo $product['stock_status'] === 'out_of_stock' ? 'disabled' : ''; ?>>
                                            <?php echo $product['stock_status'] === 'out_of_stock' ? 'Hết hàng' : 'Thêm giỏ'; ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
