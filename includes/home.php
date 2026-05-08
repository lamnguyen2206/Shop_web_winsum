<?php
require __DIR__ . '/home-repository.php';

$heroBanner = homeGetHeroBanner($conn);
$featuredCategories = homeGetFeaturedCategories($conn, 3);
$featuredProducts = homeGetFeaturedProducts($conn, 3);
$newsPosts = homeGetNewsPosts($conn, 2);

if (empty($featuredCategories)) {
    $featuredCategories = [
        ['name' => 'Đèn trang trí', 'description' => 'Thiết kế tinh giản, sang trọng cho mọi không gian.'],
        ['name' => 'Nội thất phòng khách', 'description' => 'Tối ưu công năng và tăng điểm nhấn thẩm mỹ.'],
        ['name' => 'Phụ kiện decor', 'description' => 'Hoàn thiện phong cách sống hiện đại và tinh tế.'],
    ];
}

if (empty($featuredProducts)) {
    $featuredProducts = [
        ['slug' => 'den-treo-tran-axis', 'name' => 'Đèn treo trần AXIS', 'category' => 'Chiếu sáng phòng khách', 'price' => '12.800.000đ', 'image' => 'assets/images/blog_1.png'],
        ['slug' => 'den-treo-bauhaus', 'name' => 'Đèn treo BAUHAUS', 'category' => 'Nội thất hiện đại', 'price' => '9.650.000đ', 'image' => 'assets/images/blog_2.png'],
        ['slug' => 'ph5-pendant-lamp', 'name' => 'PH5 Pendant Lamp', 'category' => 'Phong cách Bắc Âu', 'price' => '15.200.000đ', 'image' => 'assets/images/blog_3.png'],
    ];
}

if (empty($newsPosts)) {
    $newsPosts = [
        ['title' => 'Xu hướng thiết kế không gian sống 2026', 'excerpt' => 'Tổng hợp các xu hướng vật liệu, ánh sáng và màu sắc được ưa chuộng.', 'image' => 'assets/images/blog_1.png'],
        ['title' => 'Bí quyết chọn đèn treo trần theo từng khu vực', 'excerpt' => 'Gợi ý giải pháp ánh sáng phù hợp cho phòng khách, bếp và phòng ngủ.', 'image' => 'assets/images/blog_2.png'],
    ];
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

    <section class="home-section container">
        <div class="section-head">
            <h2>Danh mục nổi bật</h2>
            <a href="index.php?view=catalog">Xem tất cả</a>
        </div>
        <div class="category-grid">
            <?php foreach ($featuredCategories as $category): ?>
                <article class="category-card">
                    <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                    <p><?php echo htmlspecialchars($category['description']); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="home-section container">
        <div class="section-head">
            <h2>Sản phẩm được yêu thích</h2>
            <a href="index.php?view=catalog">Tất cả sản phẩm</a>
        </div>
        <div class="product-grid">
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
        </div>
    </section>

    <section class="home-section home-news container">
        <div class="section-head">
            <h2>Tin tức & cảm hứng</h2>
            <a href="index.php?view=blog">Đến trang blog</a>
        </div>
        <div class="news-grid">
            <?php foreach ($newsPosts as $post): ?>
                <article class="news-card">
                    <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                    <div>
                        <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                        <p><?php echo htmlspecialchars($post['excerpt']); ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</section>
