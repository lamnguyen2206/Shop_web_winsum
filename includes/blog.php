<?php
require __DIR__ . '/blog-repository.php';

$blogPosts = blogGetAllPosts($conn);
$featuredPosts = blogGetFeaturedPosts($conn, 3);
if (empty($featuredPosts)) {
    $featuredPosts = array_slice($blogPosts, 0, 3);
}
?>

<section class="blog-page container">
    <div class="blog-hero">
        <p class="breadcrumb"><a href="index.php">Trang chủ</a> / <span>Tin tức</span></p>
        <h1 class="blog-title">Tin tức</h1>
    </div>

    <div class="blog-layout">
        <div class="blog-list-vertical">
            <?php foreach ($blogPosts as $post): ?>
                <article class="blog-item">
                    <a href="index.php?view=post&amp;slug=<?php echo urlencode($post['slug']); ?>" class="blog-item-thumb">
                        <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                    </a>
                    <div class="blog-item-content">
                        <h2><a href="index.php?view=post&amp;slug=<?php echo urlencode($post['slug']); ?>"><?php echo htmlspecialchars($post['title']); ?></a></h2>
                        <p class="meta"><?php echo htmlspecialchars($post['date_label']); ?> <?php echo htmlspecialchars($post['read_time']); ?></p>
                        <p><?php echo htmlspecialchars($post['excerpt']); ?></p>
                        <a href="index.php?view=post&amp;slug=<?php echo urlencode($post['slug']); ?>" class="read-more">Đọc tiếp</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <aside class="blog-sidebar">
            <div class="sidebar-block">
                <h3>DANH MỤC TIN TỨC</h3>
                <ul class="sidebar-links">
                    <li><a href="index.php">Trang chủ</a></li>
                    <li><a href="#">Tất cả sản phẩm</a></li>
                    <li><a href="#">Danh mục</a></li>
                    <li><a href="index.php?view=blog">Blog</a></li>
                </ul>
            </div>

            <div class="sidebar-block">
                <h3>TIN NỔI BẬT</h3>
                <div class="featured-list">
                    <?php foreach ($featuredPosts as $post): ?>
                        <a href="index.php?view=post&amp;slug=<?php echo urlencode($post['slug']); ?>" class="featured-item">
                            <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                            <div>
                                <span><?php echo htmlspecialchars($post['title']); ?></span>
                                <small><?php echo htmlspecialchars($post['date_label']); ?></small>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>
    </div>
</section>
