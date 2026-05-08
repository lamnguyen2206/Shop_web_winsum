<?php
require_once __DIR__ . '/blog-repository.php';

$editorMessage = '';
$editorSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_blog_post'])) {
    $title = trim((string) ($_POST['title'] ?? ''));
    $slug = trim((string) ($_POST['slug'] ?? ''));
    $excerpt = trim((string) ($_POST['excerpt'] ?? ''));
    $contentHtml = trim((string) ($_POST['content_html'] ?? ''));
    $image = trim((string) ($_POST['image'] ?? ''));
    $category = trim((string) ($_POST['category'] ?? 'Tin tức'));
    $readTime = trim((string) ($_POST['read_time'] ?? '3 phút đọc'));
    $publishedAt = trim((string) ($_POST['published_at'] ?? date('Y-m-d')));
    $isFeatured = (int) ($_POST['is_featured'] ?? 0) === 1;

    if ($title === '' || $slug === '' || $excerpt === '' || $contentHtml === '') {
        $editorMessage = 'Vui lòng nhập đầy đủ tiêu đề, slug, mô tả ngắn và nội dung.';
    } else {
        $ok = blogCreatePost($conn, [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content' => $contentHtml,
            'category' => $category !== '' ? $category : 'Tin tức',
            'image' => $image !== '' ? $image : 'assets/images/blog_1.png',
            'read_time' => $readTime !== '' ? $readTime : '3 phút đọc',
            'published_at' => $publishedAt !== '' ? $publishedAt : date('Y-m-d'),
            'is_featured' => $isFeatured,
        ]);
        if ($ok) {
            $editorSuccess = true;
            $editorMessage = 'Đã lưu bài viết vào database.';
        } else {
            $editorMessage = 'Không thể lưu bài viết. Kiểm tra slug có thể đã tồn tại.';
        }
    }
}
?>

<section class="container blog-editor-page">
    <p class="breadcrumb"><a href="index.php?view=home">Trang chủ</a> / <a href="index.php?view=blog">Blog</a> / <span>Soạn bài</span></p>
    <h1>Khung soạn blog kiểu Word</h1>
    <p class="editor-intro">Khung này để bạn nhập nội dung tự nhiên: xuống dòng, chèn ảnh, in đậm/in nghiêng, heading, danh sách. Dữ liệu sẽ lưu trực tiếp vào bảng <code>blog_posts</code>.</p>

    <?php if ($editorMessage !== ''): ?>
        <p class="checkout-notice <?php echo $editorSuccess ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($editorMessage); ?></p>
    <?php endif; ?>

    <form method="post" action="index.php?view=blog-editor" class="blog-editor-form" id="blogEditorForm">
        <div class="editor-grid">
            <label>Tiêu đề
                <input type="text" name="title" required value="<?php echo htmlspecialchars((string) ($_POST['title'] ?? '')); ?>">
            </label>
            <label>Slug (duy nhất)
                <input type="text" name="slug" required value="<?php echo htmlspecialchars((string) ($_POST['slug'] ?? '')); ?>">
            </label>
            <label>Ảnh đại diện
                <input type="text" name="image" placeholder="assets/images/your-image.png" value="<?php echo htmlspecialchars((string) ($_POST['image'] ?? '')); ?>">
            </label>
            <label>Danh mục
                <input type="text" name="category" value="<?php echo htmlspecialchars((string) ($_POST['category'] ?? 'Tin tức')); ?>">
            </label>
            <label>Thời gian đọc
                <input type="text" name="read_time" value="<?php echo htmlspecialchars((string) ($_POST['read_time'] ?? '3 phút đọc')); ?>">
            </label>
            <label>Ngày đăng
                <input type="date" name="published_at" value="<?php echo htmlspecialchars((string) ($_POST['published_at'] ?? date('Y-m-d'))); ?>">
            </label>
        </div>

        <label>Mô tả ngắn
            <textarea name="excerpt" rows="3" required><?php echo htmlspecialchars((string) ($_POST['excerpt'] ?? '')); ?></textarea>
        </label>

        <div class="editor-toolbar">
            <button type="button" data-cmd="bold">B</button>
            <button type="button" data-cmd="italic"><i>I</i></button>
            <button type="button" data-cmd="formatBlock" data-val="h2">H2</button>
            <button type="button" data-cmd="formatBlock" data-val="h3">H3</button>
            <button type="button" data-cmd="insertUnorderedList">• List</button>
            <button type="button" id="insertImageBtn">Chèn ảnh</button>
            <button type="button" id="insertLinkBtn">Chèn link</button>
        </div>

        <div id="editorSurface" class="editor-surface" contenteditable="true"><?php echo (string) ($_POST['content_html'] ?? '<p>Nhập nội dung bài viết tại đây...</p>'); ?></div>
        <textarea name="content_html" id="contentHtml" rows="12" class="editor-html-input" required></textarea>

        <label class="editor-check">
            <input type="checkbox" name="is_featured" value="1" <?php echo !empty($_POST['is_featured']) ? 'checked' : ''; ?>>
            Đánh dấu bài nổi bật
        </label>

        <button type="submit" name="save_blog_post" value="1" class="update-cart-btn">Lưu bài viết</button>
    </form>
</section>

<script>
(function () {
    const form = document.getElementById('blogEditorForm');
    const editor = document.getElementById('editorSurface');
    const hidden = document.getElementById('contentHtml');

    function syncEditor() {
        hidden.value = editor.innerHTML.trim();
    }

    document.querySelectorAll('.editor-toolbar button[data-cmd]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const cmd = btn.getAttribute('data-cmd');
            const val = btn.getAttribute('data-val') || null;
            document.execCommand(cmd, false, val);
            syncEditor();
            editor.focus();
        });
    });

    document.getElementById('insertImageBtn').addEventListener('click', () => {
        const url = window.prompt('Nhập URL ảnh hoặc đường dẫn ảnh:');
        if (!url) return;
        document.execCommand('insertImage', false, url);
        syncEditor();
    });

    document.getElementById('insertLinkBtn').addEventListener('click', () => {
        const url = window.prompt('Nhập URL liên kết:');
        if (!url) return;
        document.execCommand('createLink', false, url);
        syncEditor();
    });

    editor.addEventListener('input', syncEditor);
    form.addEventListener('submit', syncEditor);
    syncEditor();
})();
</script>
