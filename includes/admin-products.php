<?php
require_once __DIR__ . '/product-admin-repository.php';
require_once __DIR__ . '/product-repository.php';
require_once __DIR__ . '/inventory-repository.php';

$adminMessage = '';
$editId = (int) ($_GET['edit'] ?? 0);
$editing = $editId > 0 ? productAdminGetById($conn, $editId) : null;
$products = productAdminList($conn);
$categories = productGetFilterCategories($conn);
$topSellers = productGetBestSellers($conn, 6);
$featuredFromSalesLimit = 6;
$inventoryAlerts = inventoryGetUnreadAlerts($conn, 10);

if (isset($_GET['msg'])) {
    $adminMessage = (string) $_GET['msg'];
}

$form = $editing ?: [
    'id' => 0,
    'name' => '',
    'slug' => '',
    'sku' => '',
    'category_id' => $categories[0]['id'] ?? '',
    'short_description' => '',
    'description' => '',
    'base_price' => '',
    'compare_at_price' => '',
    'stock_status' => 'in_stock',
    'material' => '',
    'color' => '',
    'warranty_months' => '',
    'is_featured' => 0,
    'is_active' => 1,
    'primary_image' => 'assets/images/blog_1.png',
    'stock_quantity' => 50,
];
?>

<section class="container admin-page">
    <p class="breadcrumb"><a href="<?php echo e(app_url('home')); ?>">Trang chủ</a> / <span>Quản trị sản phẩm</span></p>

    <div class="admin-page-head">
        <h1>Quản lý sản phẩm</h1>
        <form method="post" action="index.php?view=admin-products" class="admin-inline-form">
            <?php echo csrfField(); ?>
            <input type="hidden" name="action" value="admin_logout">
            <button type="submit" class="btn-secondary">Đăng xuất</button>
        </form>
    </div>

    <?php include __DIR__ . '/admin-nav.php'; ?>

    <?php if ($adminMessage !== ''): ?>
        <p class="admin-notice"><?php echo htmlspecialchars($adminMessage); ?></p>
    <?php endif; ?>

    <div class="admin-split">
        <aside class="admin-panel">
            <h2><?php echo $editId > 0 ? 'Chỉnh sửa chi tiết sản phẩm' : 'Thêm sản phẩm'; ?></h2>
            <form method="post" action="index.php?view=admin-products<?php echo $editId > 0 ? '&amp;edit=' . $editId : ''; ?>" class="admin-form">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" value="save_product">
                <input type="hidden" name="id" value="<?php echo (int) ($form['id'] ?? 0); ?>">

                <label>Tên sản phẩm
                    <input type="text" name="name" required value="<?php echo htmlspecialchars((string) ($form['name'] ?? '')); ?>">
                </label>
                <label>Slug (URL)
                    <input type="text" name="slug" value="<?php echo htmlspecialchars((string) ($form['slug'] ?? '')); ?>" placeholder="tu-dong-neu-de-trong">
                </label>
                <label>SKU
                    <input type="text" name="sku" required value="<?php echo htmlspecialchars((string) ($form['sku'] ?? '')); ?>">
                </label>
                <label>Danh mục
                    <select name="category_id" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo (int) $cat['id']; ?>" <?php echo (int) ($form['category_id'] ?? 0) === (int) $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Giá bán (VNĐ)
                    <input type="number" name="base_price" min="0" step="1000" required value="<?php echo htmlspecialchars((string) ($form['base_price'] ?? '')); ?>">
                </label>
                <label>Số lượng tồn kho (kho chính)
                    <input type="number" name="stock_quantity" min="0" step="1" value="<?php echo (int) ($form['stock_quantity'] ?? 0); ?>">
                </label>
                <label>Tình trạng kho
                    <select name="stock_status">
                        <option value="in_stock" <?php echo ($form['stock_status'] ?? '') === 'in_stock' ? 'selected' : ''; ?>>Còn hàng</option>
                        <option value="preorder" <?php echo ($form['stock_status'] ?? '') === 'preorder' ? 'selected' : ''; ?>>Đặt trước</option>
                        <option value="out_of_stock" <?php echo ($form['stock_status'] ?? '') === 'out_of_stock' ? 'selected' : ''; ?>>Hết hàng</option>
                    </select>
                </label>
                <label>Ảnh (đường dẫn)
                    <input type="text" name="image_url" value="<?php echo htmlspecialchars((string) ($form['primary_image'] ?? '')); ?>" placeholder="assets/images/...">
                </label>
                <label>Mô tả ngắn
                    <textarea name="short_description" rows="2"><?php echo htmlspecialchars((string) ($form['short_description'] ?? '')); ?></textarea>
                </label>
                <label>Mô tả chi tiết
                    <textarea name="description" rows="5"><?php echo htmlspecialchars((string) ($form['description'] ?? '')); ?></textarea>
                </label>
                <label>Chất liệu
                    <input type="text" name="material" value="<?php echo htmlspecialchars((string) ($form['material'] ?? '')); ?>">
                </label>
                <label>Màu sắc
                    <input type="text" name="color" value="<?php echo htmlspecialchars((string) ($form['color'] ?? '')); ?>">
                </label>
                <label>Bảo hành (tháng)
                    <input type="number" name="warranty_months" min="0" value="<?php echo htmlspecialchars((string) ($form['warranty_months'] ?? '')); ?>">
                </label>
                <label class="admin-check">
                    <input type="checkbox" name="is_featured" value="1" <?php echo !empty($form['is_featured']) ? 'checked' : ''; ?>> Sản phẩm nổi bật
                </label>
                <label class="admin-check">
                    <input type="checkbox" name="is_active" value="1" <?php echo !isset($form['is_active']) || !empty($form['is_active']) ? 'checked' : ''; ?>> Hiển thị trên web
                </label>
                <button type="submit"><?php echo $editId > 0 ? 'Cập nhật' : 'Thêm mới'; ?></button>
                <?php if ($editId > 0): ?>
                    <a class="btn-secondary" href="index.php?view=admin-products">Hủy sửa</a>
                <?php endif; ?>
            </form>
        </aside>

        <div class="admin-panel admin-panel-wide">
            <?php if ($inventoryAlerts !== []): ?>
            <div class="admin-inventory-alerts">
                <h2>Cảnh báo tồn kho (<?php echo count($inventoryAlerts); ?>)</h2>
                <p class="admin-hint">Sản phẩm hết kho sau đơn hàng — đã chuyển sang <strong>Đặt trước</strong>. Nhập thêm tồn kho hoặc xử lý đơn đặt trước.</p>
                <ul class="admin-alert-list">
                    <?php foreach ($inventoryAlerts as $alert): ?>
                        <li>
                            <p><?php echo htmlspecialchars($alert['message']); ?></p>
                            <div class="admin-alert-actions">
                                <a href="index.php?view=admin-products&amp;edit=<?php echo (int) $alert['product_id']; ?>">Sửa SP</a>
                                <form method="post" class="admin-inline-form">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="action" value="mark_inventory_read">
                                    <input type="hidden" name="alert_id" value="<?php echo (int) $alert['id']; ?>">
                                    <button type="submit" class="link-muted">Đã xử lý</button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <form method="post" class="admin-inline-form">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="mark_all_inventory_read">
                    <button type="submit" class="btn-secondary">Đánh dấu tất cả đã xử lý</button>
                </form>
            </div>
            <?php endif; ?>

            <div class="admin-bestsellers-box">
                <h2>Đề xuất sản phẩm chủ lực</h2>
                <p class="admin-hint">Dựa trên tổng số lượng đã bán trong các đơn hàng (không tính đơn hủy/trả).</p>
                <?php if ($topSellers === []): ?>
                    <p class="admin-muted">Chưa có đơn hàng — chưa thể đề xuất theo lượt mua.</p>
                <?php else: ?>
                    <ol class="admin-top-sellers">
                        <?php foreach ($topSellers as $i => $ts): ?>
                            <li>
                                <span class="admin-rank"><?php echo $i + 1; ?>.</span>
                                <a href="index.php?view=admin-products&amp;edit=<?php echo (int) $ts['id']; ?>"><?php echo htmlspecialchars($ts['name']); ?></a>
                                <span class="admin-sold-qty"><?php echo (int) $ts['units_sold']; ?> đã bán</span>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                    <form method="post" action="index.php?view=admin-products" class="admin-inline-form admin-featured-apply">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="apply_featured_from_sales">
                        <input type="hidden" name="featured_limit" value="<?php echo (int) $featuredFromSalesLimit; ?>">
                        <button type="submit" class="btn-secondary">Áp dụng <?php echo (int) $featuredFromSalesLimit; ?> SP chủ lực lên trang chủ</button>
                    </form>
                <?php endif; ?>
            </div>

            <h2>Danh sách sản phẩm</h2>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Tên</th>
                            <th>SKU</th>
                            <th>Danh mục</th>
                            <th>Giá</th>
                            <th>Đã bán</th>
                            <th>Tồn</th>
                            <th>TT kho</th>
                            <th>TT</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($p['name']); ?></strong>
                                    <?php if (!(int) $p['is_active']): ?><em class="badge-muted">Ẩn</em><?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($p['sku']); ?></td>
                                <td><?php echo htmlspecialchars($p['category_name']); ?></td>
                                <td><?php echo htmlspecialchars($p['price_label']); ?></td>
                                <td><?php echo (int) ($p['units_sold'] ?? 0); ?></td>
                                <td><?php echo (int) ($p['stock_quantity'] ?? 0); ?></td>
                                <td><?php echo htmlspecialchars(productStockStatusLabel($p['stock_status'])); ?></td>
                                <td><?php echo (int) $p['is_featured'] ? '★' : '—'; ?></td>
                                <td class="admin-actions-cell">
                                    <a href="index.php?view=product&amp;slug=<?php echo urlencode($p['slug']); ?>" target="_blank" rel="noopener">Xem</a>
                                    <a href="index.php?view=admin-products&amp;edit=<?php echo (int) $p['id']; ?>">Sửa chi tiết</a>
                                    <form method="post" action="index.php?view=admin-products" class="admin-inline-form" onsubmit="return confirm('Ẩn sản phẩm này?');">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="action" value="delete_product">
                                        <input type="hidden" name="product_id" value="<?php echo (int) $p['id']; ?>">
                                        <button type="submit" class="link-danger">Ẩn</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
