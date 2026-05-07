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
    return [
        'id' => (int) $row['id'],
        'slug' => $row['slug'],
        'title' => $row['title'],
        'excerpt' => $row['excerpt'],
        'content' => preg_split("/\r\n|\n|\r/", (string) $row['content']),
        'category' => $row['category'],
        'image' => $row['image'],
        'read_time' => $row['read_time'],
        'date' => $row['published_at'],
        'date_label' => blogFormatVietnameseDateLabel($row['published_at']),
        'is_featured' => (int) $row['is_featured'] === 1
    ];
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
