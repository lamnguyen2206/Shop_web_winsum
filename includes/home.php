<?php
require __DIR__ . '/home-repository.php';

$heroBanner = homeGetHeroBanner($conn);
$featuredCategories = homeGetFeaturedCategories($conn, 3);
$bestsellerProducts = homeGetBestsellerProducts($conn, 6);
$newsPosts = homeGetNewsPosts($conn, 2);
?>

<section class="home-page">
    <section class="hero">
        <div class="container hero-grid">
            <div class="hero-copy">
                <p class="eyebrow">WINSUM HOME</p>
                <h1><?php echo htmlspecialchars($heroBanner['title'] ?? 'Nội thất và chiếu sáng cao cấp cho không gian sống đẳng cấp'); ?></h1>
                <p class="subtitle"><?php echo htmlspecialchars($heroBanner['subtitle'] ?? 'Khám phá bộ sưu tập đèn trang trí, nội thất nhập khẩu và giải pháp thiết kế đồng bộ theo chuẩn châu Âu.'); ?></p>
                <div class="hero-actions">
                    <a href="<?php echo htmlspecialchars($heroBanner['link_url'] ?? 'index.php?view=catalog'); ?>" class="btn btn-primary">Mua sắm ngay</a>
                    <a href="index.php?view=blog" class="btn btn-ghost">Xem tin tức</a>
                </div>
            </div>
            <div class="hero-highlight">
                <img src="<?php echo htmlspecialchars($heroBanner['image_url'] ?? 'assets/images/blog_3.png'); ?>" alt="Không gian nội thất Winsum">
                <div class="highlight-badge">BST Mới 2026</div>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/home-auth-section.php'; ?>

    <section class="home-promo container">
        <article class="promo-coupon">
            <h3>XIN CHAO</h3>
            <p>Giảm 40.000đ cho toàn bộ đơn hàng.</p>
            <div class="coupon-code">Mã: <strong>WINSUMXINCHAO</strong></div>
            <small>Áp dụng 1 mã trên mỗi khách hàng.</small>
        </article>
        <div class="promo-benefits">
            <article><h4>Giao hàng hỏa tốc</h4><p>Nhận hàng trong vòng 24h</p></article>
            <article><h4>Quà tặng hấp dẫn</h4><p>Nhiều ưu đãi khuyến mãi hot</p></article>
            <article><h4>Bảo đảm chất lượng</h4><p>Sản phẩm đã được kiểm định</p></article>
            <article><h4>Hotline: 0387239676</h4><p>Dịch vụ hỗ trợ bạn 24/7</p></article>
        </div>
    </section>

    <section class="home-section container">
        <div class="section-head">
            <h2>Our Category</h2>
            <a href="index.php?view=catalog">Xem tất cả</a>
        </div>
        <div class="category-grid">
            <?php if (empty($featuredCategories)): ?>
                <article class="category-card placeholder-card">
                    <h3>Khung danh mục Winsum</h3>
                    <p>Thêm dữ liệu danh mục từ bảng <strong>categories</strong>.</p>
                </article>
            <?php else: ?>
                <?php foreach ($featuredCategories as $category): ?>
                    <article class="category-card">
                        <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                        <p><?php echo htmlspecialchars($category['description']); ?></p>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="home-section container home-bestsellers">
        <div class="section-head">
            <h2>Sản phẩm chủ lực</h2>
            <a href="index.php?view=catalog">Tất cả sản phẩm</a>
        </div>
        <p class="home-section-note">Đề xuất theo số lượng khách đã mua trên cửa hàng.</p>
        <div class="product-grid">
            <?php if (empty($bestsellerProducts)): ?>
                <article class="product-card placeholder-card">
                    <div class="product-info">
                        <h3>Chưa có dữ liệu bán hàng</h3>
                        <p>Đánh dấu sản phẩm nổi bật trong quản trị hoặc chờ đơn hàng đầu tiên.</p>
                    </div>
                </article>
            <?php else: ?>
                <?php foreach ($bestsellerProducts as $product): ?>
                    <article class="product-card">
                        <a href="index.php?view=product&amp;slug=<?php echo urlencode($product['slug'] ?? ''); ?>" class="product-card-image" title="Xem chi tiết: <?php echo htmlspecialchars($product['name']); ?>" aria-label="Xem chi tiết <?php echo htmlspecialchars($product['name']); ?>">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </a>
                        <div class="product-info">
                            <p class="product-category"><?php echo htmlspecialchars($product['category']); ?></p>
                            <h3><a href="index.php?view=product&amp;slug=<?php echo urlencode($product['slug'] ?? ''); ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                            <p class="product-price"><?php echo htmlspecialchars($product['price']); ?></p>
                            <?php if (!empty($product['units_sold'])): ?>
                                <p class="product-sold-badge">Đã bán <?php echo (int) $product['units_sold']; ?></p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="home-section home-news container">
        <div class="section-head">
            <h2>Our Blog</h2>
            <a href="index.php?view=blog">Đến trang blog</a>
        </div>
        <div class="news-grid">
            <?php if (empty($newsPosts)): ?>
                <article class="news-card placeholder-card"><div><h3>Chưa có bài viết</h3></div></article>
            <?php else: ?>
                <?php foreach ($newsPosts as $post): ?>
                    <article class="news-card">
                        <a href="index.php?view=post&amp;slug=<?php echo urlencode($post['slug']); ?>">
                            <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                        </a>
                        <div>
                            <h3><a href="index.php?view=post&amp;slug=<?php echo urlencode($post['slug']); ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                            <p><?php echo htmlspecialchars($post['excerpt']); ?></p>
                            <a class="read-more" href="index.php?view=post&amp;slug=<?php echo urlencode($post['slug']); ?>">Xem ngay</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</section>
