<?php
require __DIR__ . '/home-repository.php';

$heroBanner = homeGetHeroBanner($conn);
$featuredCategories = homeGetFeaturedCategories($conn, 3);
$featuredProducts = homeGetFeaturedProducts($conn, 3);
$newsPosts = homeGetNewsPosts($conn, 2);

if (empty($featuredCategories)) {
    $featuredCategories = [];
}

if (empty($featuredProducts)) {
    $featuredProducts = [];
}

if (empty($newsPosts)) {
    $newsPosts = [];
}
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
            <h2><span>our</span>CATEGORY</h2>
            <a href="index.php?view=catalog">Xem tất cả</a>
        </div>
        <div class="category-grid">
            <?php if (empty($featuredCategories)): ?>
                <article class="category-card placeholder-card">
                    <h3>Khung danh mục Winsum</h3>
                    <p>Thêm dữ liệu danh mục từ bảng <strong>categories</strong> để hiển thị tự động tại đây.</p>
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

    <section class="home-section container">
        <div class="section-head">
            <h2>Sản phẩm nổi bật</h2>
            <a href="index.php?view=catalog">Tất cả sản phẩm</a>
        </div>
        <div class="product-grid">
            <?php if (empty($featuredProducts)): ?>
                <article class="product-card placeholder-card">
                    <div class="product-info">
                        <p class="product-category">Khung sản phẩm</p>
                        <h3>Thêm dữ liệu từ database</h3>
                        <p class="product-price">Bảng products + product_images</p>
                    </div>
                </article>
            <?php else: ?>
                <?php foreach ($featuredProducts as $product): ?>
                    <article class="product-card">
                        <a href="index.php?view=product&amp;slug=<?php echo urlencode($product['slug'] ?? ''); ?>">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </a>
                        <div class="product-info">
                            <p class="product-category"><?php echo htmlspecialchars($product['category']); ?></p>
                            <h3><a href="index.php?view=product&amp;slug=<?php echo urlencode($product['slug'] ?? ''); ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                            <p class="product-price"><?php echo htmlspecialchars($product['price']); ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="home-section home-news container">
        <div class="section-head">
            <h2><span>our</span>BLOG</h2>
            <a href="index.php?view=blog">Đến trang blog</a>
        </div>
        <div class="news-grid">
            <?php if (empty($newsPosts)): ?>
                <article class="news-card placeholder-card">
                    <div>
                        <h3>Khung blog Winsum</h3>
                        <p>Thêm dữ liệu từ bảng <strong>blog_posts</strong> để hiển thị bài viết mới nhất tại đây.</p>
                    </div>
                </article>
            <?php else: ?>
                <?php foreach ($newsPosts as $post): ?>
                    <article class="news-card">
                        <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                        <div>
                            <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                            <p><?php echo htmlspecialchars($post['excerpt']); ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="home-section container instagram-block">
        <div class="section-head">
            <h2><span>follow</span>INSTAGRAM</h2>
            <a href="#">@winsumhome</a>
        </div>
        <p>Cập nhật xu hướng nội thất và các mẫu đèn mới mỗi tuần.</p>
    </section>
</section>
