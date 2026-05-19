<?php
require_once __DIR__ . '/../config/database.php';

function blogFormatVietnameseDateLabel(string $ymdDate): string
{
    $timestamp = strtotime($ymdDate);
    $dayMap = [
        'Monday' => 'Thứ Hai',
        'Tuesday' => 'Thứ Ba',
        'Wednesday' => 'Thứ Tư',
        'Thursday' => 'Thứ Năm',
        'Friday' => 'Thứ Sáu',
        'Saturday' => 'Thứ Bảy',
        'Sunday' => 'Chủ Nhật'
    ];

    $englishDay = date('l', $timestamp);
    $dayLabel = $dayMap[$englishDay] ?? $englishDay;
    return $dayLabel . ', ' . date('d/m/Y', $timestamp);
}

function blogMapPostRow(array $row): array
{
    $rawContent = (string) $row['content'];
    $contentBlocks = preg_split("/\r\n|\n|\r/", $rawContent);
    $hasHtml = preg_match('/<\s*(p|h1|h2|h3|h4|img|ul|ol|li|blockquote|strong|em|a|br)\b/i', $rawContent) === 1;

    return [
        'id' => (int) $row['id'],
        'slug' => $row['slug'],
        'title' => $row['title'],
        'excerpt' => $row['excerpt'],
        'content' => $contentBlocks,
        'content_html' => $hasHtml ? $rawContent : '',
        'category' => $row['category'],
        'image' => $row['image'],
        'read_time' => $row['read_time'],
        'date' => $row['published_at'],
        'date_label' => blogFormatVietnameseDateLabel($row['published_at']),
        'is_featured' => (int) $row['is_featured'] === 1
    ];
}

function blogSanitizeHtml(string $html): string
{
    $allowed = '<p><br><strong><em><u><h2><h3><h4><ul><ol><li><blockquote><img><a>';
    $clean = strip_tags($html, $allowed);

    $clean = preg_replace_callback('/<a\s+([^>]+)>/i', static function (array $matches): string {
        $attrs = $matches[1];
        $href = '';
        if (preg_match('/href\s*=\s*"([^"]*)"/i', $attrs, $hrefMatch)) {
            $href = $hrefMatch[1];
        } elseif (preg_match("/href\s*=\s*'([^']*)'/i", $attrs, $hrefMatch)) {
            $href = $hrefMatch[1];
        }
        $href = htmlspecialchars($href, ENT_QUOTES, 'UTF-8');
        return '<a href="' . $href . '" target="_blank" rel="noopener noreferrer">';
    }, $clean) ?? $clean;

    $clean = preg_replace_callback('/<img\s+([^>]+)>/i', static function (array $matches): string {
        $attrs = $matches[1];
        $src = '';
        $alt = '';
        if (preg_match('/src\s*=\s*"([^"]*)"/i', $attrs, $srcMatch)) {
            $src = $srcMatch[1];
        } elseif (preg_match("/src\s*=\s*'([^']*)'/i", $attrs, $srcMatch)) {
            $src = $srcMatch[1];
        }
        if (preg_match('/alt\s*=\s*"([^"]*)"/i', $attrs, $altMatch)) {
            $alt = $altMatch[1];
        } elseif (preg_match("/alt\s*=\s*'([^']*)'/i", $attrs, $altMatch)) {
            $alt = $altMatch[1];
        }
        if ($src === '') {
            return '';
        }
        return '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '">';
    }, $clean) ?? $clean;

    return $clean;
}

function blogGetCategoryOptions(mysqli $conn): array
{
    $categories = [];
    $result = $conn->query('SELECT name FROM blog_categories WHERE is_active = 1 ORDER BY name ASC');
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = (string) $row['name'];
        }
    }
    if ($categories === []) {
        $categories = ['Tin tức', 'Xu hướng', 'Hướng dẫn', 'Không gian sống'];
    }
    return $categories;
}

function blogEstimateReadTime(string $html): string
{
    $text = trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    if ($text === '') {
        return '1 phút đọc';
    }
    $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
    $wordCount = is_array($words) ? count($words) : 0;
    $minutes = max(1, (int) ceil($wordCount / 200));

    return $minutes . ' phút đọc';
}

function blogCreatePost(mysqli $conn, array $payload): bool
{
    $stmt = $conn->prepare("INSERT INTO blog_posts
        (slug, title, excerpt, content, category, image, read_time, published_at, is_featured, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'published')");
    if (!$stmt) {
        return false;
    }

    $slug = $payload['slug'];
    $title = $payload['title'];
    $excerpt = $payload['excerpt'];
    $content = $payload['content'];
    $category = $payload['category'];
    $image = $payload['image'];
    $readTime = $payload['read_time'];
    $publishedAt = $payload['published_at'];
    $isFeatured = !empty($payload['is_featured']) ? 1 : 0;

    $stmt->bind_param('ssssssssi', $slug, $title, $excerpt, $content, $category, $image, $readTime, $publishedAt, $isFeatured);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function blogGetAllPosts(mysqli $conn): array
{
    $sql = "SELECT id, slug, title, excerpt, content, category, image, read_time, published_at, is_featured
            FROM blog_posts
            ORDER BY published_at DESC, id DESC";
    $result = $conn->query($sql);
    if (!$result) {
        return [];
    }

    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = blogMapPostRow($row);
    }
    return $posts;
}

function blogGetFeaturedPosts(mysqli $conn, int $limit = 3): array
{
    $stmt = $conn->prepare("SELECT id, slug, title, excerpt, content, category, image, read_time, published_at, is_featured
                            FROM blog_posts
                            WHERE is_featured = 1
                            ORDER BY published_at DESC, id DESC
                            LIMIT ?");
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = blogMapPostRow($row);
    }
    $stmt->close();
    return $posts;
}

function blogGetPostBySlug(mysqli $conn, string $slug): ?array
{
    $stmt = $conn->prepare("SELECT id, slug, title, excerpt, content, category, image, read_time, published_at, is_featured
                            FROM blog_posts
                            WHERE slug = ?
                            LIMIT 1");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return $row ? blogMapPostRow($row) : null;
}

function blogGetRelatedPosts(mysqli $conn, string $category, string $excludedSlug, int $limit = 3): array
{
    $stmt = $conn->prepare("SELECT id, slug, title, excerpt, content, category, image, read_time, published_at, is_featured
                            FROM blog_posts
                            WHERE category = ? AND slug <> ?
                            ORDER BY published_at DESC, id DESC
                            LIMIT ?");
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('ssi', $category, $excludedSlug, $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = blogMapPostRow($row);
    }
    $stmt->close();
    return $posts;
}
