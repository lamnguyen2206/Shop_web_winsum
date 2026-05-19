<?php
require __DIR__ . '/blog-repository.php';

$slug = isset($_GET['slug']) ? trim((string) $_GET['slug']) : '';
$currentPost = blogGetPostBySlug($conn, $slug);

if ($currentPost === null) {
    http_response_code(404);
    ?>
    <section class="container post-not-found">
        <h1>Không tìm thấy bài viết</h1>
        <p>Bài viết bạn đang tìm không tồn tại hoặc đã được cập nhật đường dẫn.</p>
        <a href="<?php echo e(app_url('blog')); ?>" class="read-more">Quay lại trang blog</a>
    </section>
    <?php
    return;
}

$relatedPosts = blogGetRelatedPosts($conn, $currentPost['category'], $currentPost['slug'], 3);
?>

<section class="container blog-detail">
    <p class="breadcrumb">
        <a href="<?php echo e(app_url('home')); ?>">Trang chủ</a> /
        <a href="<?php echo e(app_url('blog')); ?>">Tin tức</a> /
        <span><?php echo htmlspecialchars($currentPost['title']); ?></span>
    </p>

    <article class="post-article">
        <img class="post-cover" src="<?php echo htmlspecialchars($currentPost['image']); ?>" alt="<?php echo htmlspecialchars($currentPost['title']); ?>">
        <div class="post-header">
            <span class="post-category"><?php echo htmlspecialchars($currentPost['category']); ?></span>
            <h1><?php echo htmlspecialchars($currentPost['title']); ?></h1>
            <p class="meta"><?php echo htmlspecialchars($currentPost['date_label']); ?> · <?php echo htmlspecialchars($currentPost['read_time']); ?></p>
        </div>

        <div class="post-content">
            <?php if (!empty($currentPost['content_html'])): ?>
                <?php echo blogSanitizeHtml($currentPost['content_html']); ?>
            <?php else: ?>
                <?php foreach ($currentPost['content'] as $paragraph): ?>
                    <p><?php echo htmlspecialchars($paragraph); ?></p>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </article>

    <?php if (!empty($relatedPosts)): ?>
        <section class="related-posts">
            <h2>Bài viết liên quan</h2>
            <div class="blog-list">
                <?php foreach ($relatedPosts as $post): ?>
                    <article class="blog-card">
                        <a href="index.php?view=post&amp;slug=<?php echo urlencode($post['slug']); ?>" class="blog-thumb">
                            <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                        </a>
                        <span class="post-category"><?php echo htmlspecialchars($post['category']); ?></span>
                        <h3><a href="index.php?view=post&amp;slug=<?php echo urlencode($post['slug']); ?>"><?php echo htmlspecialchars($post['title']); ?></a></h3>
                        <p class="meta"><?php echo htmlspecialchars($post['date_label']); ?> · <?php echo htmlspecialchars($post['read_time']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</section>
