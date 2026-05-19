<?php
require_once __DIR__ . '/blog-repository.php';
require_once __DIR__ . '/admin-auth.php';
require_once __DIR__ . '/csrf.php';

adminRequire();

$editorMessage = '';
$editorSuccess = false;
$categoryOptions = blogGetCategoryOptions($conn);

function blogEditorSlugify(string $text): string
{
    $text = trim($text);
    if ($text === '') {
        return '';
    }
    if (function_exists('transliterator_transliterate')) {
        $text = transliterator_transliterate('Any-Latin; Latin-ASCII', $text);
    } else {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    }
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text) ?? '';
    $text = preg_replace('/[\s_-]+/', '-', $text) ?? '';
    return trim($text, '-');
}

function blogEditorSaveCoverUpload(): ?string
{
    if (empty($_FILES['cover_image']['tmp_name']) || !is_uploaded_file($_FILES['cover_image']['tmp_name'])) {
        return null;
    }

    $file = $_FILES['cover_image'];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }

    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        return null;
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
    if ($finfo) {
        finfo_close($finfo);
    }

    if (!isset($allowed[$mime])) {
        return null;
    }

    $uploadDir = dirname(__DIR__) . '/assets/images/blog-uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = 'blog_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    $dest = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return null;
    }

    return 'assets/images/blog-uploads/' . $filename;
}

$formDefaults = [
    'title' => '',
    'slug' => '',
    'excerpt' => '',
    'content_html' => '<p>Nhập nội dung bài viết tại đây...</p>',
    'image' => '',
    'category' => $categoryOptions[0] ?? 'Tin tức',
    'published_at' => date('Y-m-d'),
    'is_featured' => false,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_blog_post'])) {
    $posted = [
        'title' => trim((string) ($_POST['title'] ?? '')),
        'slug' => trim((string) ($_POST['slug'] ?? '')),
        'excerpt' => trim((string) ($_POST['excerpt'] ?? '')),
        'content_html' => (string) ($_POST['content_html'] ?? ''),
        'image' => trim((string) ($_POST['image'] ?? '')),
        'category' => trim((string) ($_POST['category'] ?? 'Tin tức')),
        'published_at' => trim((string) ($_POST['published_at'] ?? date('Y-m-d'))),
        'is_featured' => !empty($_POST['is_featured']),
    ];

    if (!csrfValidate()) {
        $editorMessage = 'Phiên làm việc không hợp lệ. Vui lòng thử lại.';
        $formDefaults = array_merge($formDefaults, $posted);
    } else {
        $title = $posted['title'];
        $slug = $posted['slug'] !== '' ? $posted['slug'] : blogEditorSlugify($title);
        $excerpt = $posted['excerpt'];
        $contentHtml = blogSanitizeHtml(trim($posted['content_html']));
        $image = $posted['image'];
        $category = $posted['category'];
        $publishedAt = $posted['published_at'];
        $isFeatured = $posted['is_featured'];

        $uploadedImage = blogEditorSaveCoverUpload();
        if ($uploadedImage !== null) {
            $image = $uploadedImage;
        }

        $plainContent = trim(strip_tags($contentHtml));
        if ($title === '' || $slug === '' || $excerpt === '' || $plainContent === '') {
            $editorMessage = 'Vui lòng nhập đầy đủ tiêu đề, slug, mô tả ngắn và nội dung.';
            $formDefaults = array_merge($formDefaults, $posted, ['slug' => $slug, 'content_html' => $contentHtml]);
        } else {
            $readTime = blogEstimateReadTime($contentHtml);
            $ok = blogCreatePost($conn, [
                'title' => $title,
                'slug' => $slug,
                'excerpt' => $excerpt,
                'content' => $contentHtml,
                'category' => $category !== '' ? $category : 'Tin tức',
                'image' => $image !== '' ? $image : 'assets/images/blog_1.png',
                'read_time' => $readTime,
                'published_at' => $publishedAt !== '' ? $publishedAt : date('Y-m-d'),
                'is_featured' => $isFeatured,
            ]);
            if ($ok) {
                $editorSuccess = true;
                $editorMessage = 'Đã đăng bài viết thành công.';
                $formDefaults = [
                    'title' => '',
                    'slug' => '',
                    'excerpt' => '',
                    'content_html' => '<p>Nhập nội dung bài viết tại đây...</p>',
                    'image' => '',
                    'category' => $categoryOptions[0] ?? 'Tin tức',
                    'published_at' => date('Y-m-d'),
                    'is_featured' => false,
                ];
            } else {
                $editorMessage = 'Không thể lưu bài viết. Kiểm tra slug có thể đã tồn tại.';
                $formDefaults = array_merge($formDefaults, $posted, [
                    'slug' => $slug,
                    'content_html' => $contentHtml,
                    'image' => $image,
                ]);
            }
        }
    }
}

$f = $formDefaults;
$coverPreviewUrl = $f['image'] !== '' ? $f['image'] : '';
$showCoverPreview = $coverPreviewUrl !== '';
?>

<section class="container blog-editor-page admin-page">
    <p class="breadcrumb"><a href="index.php?view=home">Trang chủ</a> / <a href="index.php?view=blog">Blog</a> / <span>Soạn bài</span></p>

    <div class="admin-page-head">
        <h1>Soạn bài blog</h1>
    </div>

    <?php include __DIR__ . '/admin-nav.php'; ?>

    <p class="editor-intro">Soạn nội dung và đăng bài lên cửa hàng. Thời gian đọc được tính tự động từ nội dung.</p>

    <?php if ($editorMessage !== ''): ?>
        <p class="blog-editor-notice <?php echo $editorSuccess ? 'blog-editor-notice--success' : 'blog-editor-notice--error'; ?>">
            <?php echo htmlspecialchars($editorMessage); ?>
        </p>
    <?php endif; ?>

    <form method="post" action="index.php?view=blog-editor" class="blog-editor-form" id="blogEditorForm" enctype="multipart/form-data">
        <?php echo csrfField(); ?>

        <div class="blog-editor-layout">
            <div class="blog-editor-main">
                <div class="blog-editor-field">
                    <label for="blogTitle">Tiêu đề</label>
                    <input type="text" id="blogTitle" name="title" required value="<?php echo htmlspecialchars($f['title']); ?>" placeholder="Nhập tiêu đề bài viết...">
                </div>

                <div class="blog-editor-field">
                    <label for="blogExcerpt">Mô tả ngắn</label>
                    <textarea id="blogExcerpt" name="excerpt" rows="3" required placeholder="Tóm tắt ngắn gọn cho danh sách bài viết..."><?php echo htmlspecialchars($f['excerpt']); ?></textarea>
                </div>

                <div class="blog-editor-field">
                    <label>Nội dung bài viết</label>
                    <div class="blog-editor-compose">
                        <div class="blog-editor-toolbar" role="toolbar" aria-label="Định dạng văn bản">
                            <button type="button" data-cmd="bold" title="In đậm"><strong>B</strong></button>
                            <button type="button" data-cmd="italic" title="In nghiêng"><em>I</em></button>
                            <button type="button" data-cmd="formatBlock" data-val="h2" title="Tiêu đề H2">H2</button>
                            <button type="button" data-cmd="formatBlock" data-val="h3" title="Tiêu đề H3">H3</button>
                            <button type="button" data-cmd="insertUnorderedList" title="Danh sách">• List</button>
                            <button type="button" id="insertImageBtn" title="Chèn ảnh">Ảnh</button>
                            <button type="button" id="insertLinkBtn" title="Chèn liên kết">Link</button>
                        </div>
                        <div
                            id="editorSurface"
                            class="blog-editor-surface"
                            contenteditable="true"
                            data-placeholder="Nhập nội dung bài viết tại đây..."
                        ><?php echo $f['content_html']; ?></div>
                    </div>
                    <textarea name="content_html" id="contentHtml" class="blog-editor-html-input" required aria-hidden="true"></textarea>
                </div>
            </div>

            <aside class="blog-editor-sidebar">
                <div class="blog-editor-panel">
                    <h2>Cấu hình bài viết</h2>

                    <div class="blog-editor-field">
                        <label for="blogCategory">Danh mục</label>
                        <select id="blogCategory" name="category" required>
                            <?php foreach ($categoryOptions as $catName): ?>
                                <option value="<?php echo htmlspecialchars($catName); ?>" <?php echo $f['category'] === $catName ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($catName); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="blog-editor-field">
                        <label for="blogPublishedAt">Ngày đăng</label>
                        <input type="date" id="blogPublishedAt" name="published_at" required value="<?php echo htmlspecialchars($f['published_at']); ?>">
                    </div>

                    <div class="blog-editor-field">
                        <label for="blogSlug">Slug (URL)</label>
                        <input type="text" id="blogSlug" name="slug" required readonly value="<?php echo htmlspecialchars($f['slug']); ?>" data-manual="0" placeholder="tu-dong-tu-tieu-de">
                    </div>

                    <div class="blog-editor-field blog-cover-upload">
                        <label>Ảnh đại diện</label>
                        <input type="hidden" name="image" id="existingImagePath" value="<?php echo htmlspecialchars($f['image']); ?>">
                        <input type="file" id="coverImageInput" name="cover_image" class="blog-cover-input" accept="image/jpeg,image/png,image/webp,image/gif">
                        <div id="coverDropzone" class="blog-cover-dropzone" role="button" tabindex="0"<?php echo $showCoverPreview ? ' style="display:none"' : ''; ?>>
                            <svg width="32" height="32" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M19 7h-3V6a4 4 0 0 0-8 0v1H5a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2zm-9-1a2 2 0 0 1 4 0v1h-4V6zm5 12H9l-2.5-3.2L6 17H4l4.5-6 3 4 2-2.5L18 17h-3l-2-2z"/></svg>
                            <span>Click hoặc kéo thả để tải ảnh</span>
                            <small>JPG, PNG, WebP · Tối đa 5MB</small>
                        </div>
                        <div id="coverPreview" class="blog-cover-preview<?php echo $showCoverPreview ? ' is-visible' : ''; ?>">
                            <img id="coverPreviewImg" src="<?php echo $showCoverPreview ? htmlspecialchars($coverPreviewUrl) : ''; ?>" alt="Xem trước ảnh đại diện">
                            <div class="blog-cover-preview-actions">
                                <button type="button" id="coverRemoveBtn" class="blog-cover-remove">Gỡ ảnh</button>
                            </div>
                        </div>
                    </div>

                    <label class="blog-editor-check">
                        <input type="checkbox" name="is_featured" value="1" <?php echo !empty($f['is_featured']) ? 'checked' : ''; ?>>
                        Đánh dấu bài nổi bật
                    </label>
                </div>
            </aside>
        </div>

        <div class="blog-editor-actions">
            <a class="btn-blog-cancel" href="index.php?view=blog">Hủy / Quay lại</a>
            <button type="submit" name="save_blog_post" value="1" class="btn-blog-publish">Đăng bài viết</button>
        </div>
    </form>
</section>
