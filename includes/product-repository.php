<?php
require_once __DIR__ . '/../config/database.php';

function productFormatPrice(float $amount): string
{
    return number_format($amount, 0, ',', '.') . 'đ';
}

function productGetFilterCategories(mysqli $conn): array
{
    $result = $conn->query("SELECT id, name, slug FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
    if (!$result) {
        return [];
    }

    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    return $categories;
}

function productGetFilterBrands(mysqli $conn): array
{
    $result = $conn->query("SELECT id, name, slug FROM brands WHERE is_active = 1 ORDER BY name ASC");
    if (!$result) {
        return [];
    }

    $brands = [];
    while ($row = $result->fetch_assoc()) {
        $brands[] = $row;
    }
    return $brands;
}

function productGetFilterMaterialOptions(mysqli $conn): array
{
    $result = $conn->query("SELECT DISTINCT material
                            FROM products
                            WHERE is_active = 1
                              AND material IS NOT NULL
                              AND material <> ''
                            ORDER BY material ASC");
    if (!$result) {
        return [];
    }

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row['material'];
    }
    return $items;
}

function productGetFilterColorOptions(mysqli $conn): array
{
    $result = $conn->query("SELECT DISTINCT color
                            FROM products
                            WHERE is_active = 1
                              AND color IS NOT NULL
                              AND color <> ''
                            ORDER BY color ASC");
    if (!$result) {
        return [];
    }

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row['color'];
    }
    return $items;
}

function productGetAvailablePriceRange(mysqli $conn): array
{
    $result = $conn->query("SELECT MIN(base_price) AS min_price, MAX(base_price) AS max_price FROM products WHERE is_active = 1");
    $row = $result ? $result->fetch_assoc() : null;
    $min = isset($row['min_price']) ? (float) $row['min_price'] : 0;
    $max = isset($row['max_price']) ? (float) $row['max_price'] : 0;
    return ['min' => $min, 'max' => $max];
}

function productBuildFiltersFromRequest(): array
{
    return [
        'q' => trim((string) ($_GET['q'] ?? '')),
        'category' => trim((string) ($_GET['category'] ?? '')),
        'brand' => trim((string) ($_GET['brand'] ?? '')),
        'material' => trim((string) ($_GET['material'] ?? '')),
        'color' => trim((string) ($_GET['color'] ?? '')),
        'stock' => trim((string) ($_GET['stock'] ?? '')),
        'min_price' => (float) ($_GET['min_price'] ?? 0),
        'max_price' => (float) ($_GET['max_price'] ?? 0),
        'sort' => trim((string) ($_GET['sort'] ?? 'featured')),
    ];
}

function productMapListRow(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'name' => $row['name'],
        'slug' => $row['slug'],
        'sku' => $row['sku'],
        'short_description' => $row['short_description'] ?? '',
        'base_price' => (float) $row['base_price'],
        'price_label' => productFormatPrice((float) $row['base_price']),
        'stock_status' => $row['stock_status'],
        'category_name' => $row['category_name'] ?? 'Chưa phân loại',
        'category_slug' => $row['category_slug'] ?? '',
        'brand_name' => $row['brand_name'] ?? 'Winsum Home',
        'brand_slug' => $row['brand_slug'] ?? '',
        'image' => $row['image_url'] ?: 'assets/images/blog_1.png'
    ];
}

function productBuildSearchConditions(array $filters): array
{
    $where = [
        "p.is_active = 1",
        "(p.published_at IS NULL OR p.published_at <= NOW())",
    ];
    $types = '';
    $params = [];

    if ($filters['q'] !== '') {
        $where[] = "(p.name LIKE CONCAT('%', ?, '%') OR p.short_description LIKE CONCAT('%', ?, '%'))";
        $types .= 'ss';
        $params[] = $filters['q'];
        $params[] = $filters['q'];
    }

    if ($filters['category'] !== '') {
        $where[] = "c.slug = ?";
        $types .= 's';
        $params[] = $filters['category'];
    }

    if ($filters['stock'] !== '' && in_array($filters['stock'], ['in_stock', 'out_of_stock', 'preorder'], true)) {
        $where[] = "p.stock_status = ?";
        $types .= 's';
        $params[] = $filters['stock'];
    }

    if ($filters['min_price'] > 0) {
        $where[] = "p.base_price >= ?";
        $types .= 'd';
        $params[] = $filters['min_price'];
    }

    if ($filters['max_price'] > 0) {
        $where[] = "p.base_price <= ?";
        $types .= 'd';
        $params[] = $filters['max_price'];
    }

    if ($filters['brand'] !== '') {
        $where[] = "b.slug = ?";
        $types .= 's';
        $params[] = $filters['brand'];
    }

    if ($filters['material'] !== '') {
        $where[] = "p.material = ?";
        $types .= 's';
        $params[] = $filters['material'];
    }

    if ($filters['color'] !== '') {
        $where[] = "p.color = ?";
        $types .= 's';
        $params[] = $filters['color'];
    }

    return [
        'where_sql' => implode(' AND ', $where),
        'types' => $types,
        'params' => $params,
    ];
}

function productResolveSortSql(string $sort): string
{
    if ($sort === 'price_asc') {
        return "p.base_price ASC, p.id DESC";
    }
    if ($sort === 'price_desc') {
        return "p.base_price DESC, p.id DESC";
    }
    if ($sort === 'name_asc') {
        return "p.name ASC, p.id DESC";
    }
    if ($sort === 'latest') {
        return "p.published_at DESC, p.id DESC";
    }
    return "p.is_featured DESC, p.published_at DESC, p.id DESC";
}

function productCountSearchProducts(mysqli $conn, array $filters): int
{
    $conditions = productBuildSearchConditions($filters);
    $sql = "SELECT COUNT(*) AS total
            FROM products p
            JOIN categories c ON c.id = p.category_id
            LEFT JOIN brands b ON b.id = p.brand_id
            WHERE {$conditions['where_sql']}";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return 0;
    }
    if ($conditions['types'] !== '') {
        $stmt->bind_param($conditions['types'], ...$conditions['params']);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return (int) ($row['total'] ?? 0);
}

function productSearchProducts(mysqli $conn, array $filters, int $limit = 12, int $offset = 0): array
{
    $conditions = productBuildSearchConditions($filters);
    $sortSql = productResolveSortSql($filters['sort']);
    $sql = "SELECT p.id, p.name, p.slug, p.sku, p.short_description, p.base_price, p.stock_status,
                   c.name AS category_name, c.slug AS category_slug,
                   b.name AS brand_name, b.slug AS brand_slug,
                   pi.image_url
            FROM products p
            JOIN categories c ON c.id = p.category_id
            LEFT JOIN brands b ON b.id = p.brand_id
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
            WHERE {$conditions['where_sql']}
            ORDER BY {$sortSql}
            LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [];
    }

    $types = $conditions['types'] . 'ii';
    $params = $conditions['params'];
    $params[] = $limit;
    $params[] = max(0, $offset);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = productMapListRow($row);
    }
    $stmt->close();

    return $items;
}

function productGetBySlug(mysqli $conn, string $slug): ?array
{
    $sql = "SELECT p.id, p.category_id, p.name, p.slug, p.sku, p.short_description, p.description, p.base_price, p.compare_at_price,
                   p.stock_status, p.material, p.color, p.warranty_months,
                   c.name AS category_name, c.slug AS category_slug
            FROM products p
            JOIN categories c ON c.id = p.category_id
            WHERE p.slug = ?
              AND p.is_active = 1
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        return null;
    }

    $product = [
        'id' => (int) $row['id'],
        'category_id' => (int) $row['category_id'],
        'name' => $row['name'],
        'slug' => $row['slug'],
        'sku' => $row['sku'],
        'short_description' => $row['short_description'] ?? '',
        'description' => $row['description'] ?? '',
        'base_price' => (float) $row['base_price'],
        'compare_at_price' => $row['compare_at_price'] !== null ? (float) $row['compare_at_price'] : null,
        'price_label' => productFormatPrice((float) $row['base_price']),
        'compare_price_label' => $row['compare_at_price'] !== null ? productFormatPrice((float) $row['compare_at_price']) : null,
        'stock_status' => $row['stock_status'],
        'material' => $row['material'] ?? '',
        'color' => $row['color'] ?? '',
        'warranty_months' => $row['warranty_months'] !== null ? (int) $row['warranty_months'] : null,
        'category_name' => $row['category_name'],
        'category_slug' => $row['category_slug'],
        'images' => [],
    ];

    $stmtImages = $conn->prepare("SELECT image_url, alt_text, sort_order, is_primary
                                  FROM product_images
                                  WHERE product_id = ?
                                  ORDER BY is_primary DESC, sort_order ASC, id ASC");
    if ($stmtImages) {
        $productId = $product['id'];
        $stmtImages->bind_param('i', $productId);
        $stmtImages->execute();
        $imagesRes = $stmtImages->get_result();
        while ($img = $imagesRes->fetch_assoc()) {
            $product['images'][] = [
                'url' => $img['image_url'],
                'alt' => $img['alt_text'] ?: $product['name'],
            ];
        }
        $stmtImages->close();
    }

    if (empty($product['images'])) {
        $product['images'][] = ['url' => 'assets/images/blog_1.png', 'alt' => $product['name']];
    }

    return $product;
}

function productGetRelatedByCategory(mysqli $conn, int $categoryId, int $excludeId, int $limit = 4): array
{
    $stmt = $conn->prepare("SELECT p.id, p.name, p.slug, p.sku, p.short_description, p.base_price, p.stock_status,
                                   c.name AS category_name, c.slug AS category_slug,
                                   pi.image_url
                            FROM products p
                            JOIN categories c ON c.id = p.category_id
                            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
                            WHERE p.category_id = ?
                              AND p.id <> ?
                              AND p.is_active = 1
                            ORDER BY p.is_featured DESC, p.published_at DESC, p.id DESC
                            LIMIT ?");
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('iii', $categoryId, $excludeId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = productMapListRow($row);
    }
    $stmt->close();
    return $items;
}

function productGetById(mysqli $conn, int $id): ?array
{
    $stmt = $conn->prepare("SELECT p.id, p.name, p.slug, p.sku, p.base_price, p.stock_status,
                                   c.name AS category_name,
                                   pi.image_url
                            FROM products p
                            JOIN categories c ON c.id = p.category_id
                            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
                            WHERE p.id = ?
                              AND p.is_active = 1
                            LIMIT 1");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    if (!$row) {
        return null;
    }

    return [
        'id' => (int) $row['id'],
        'name' => $row['name'],
        'slug' => $row['slug'],
        'sku' => $row['sku'],
        'price' => (int) round((float) $row['base_price']),
        'image' => $row['image_url'] ?: 'assets/images/blog_1.png',
        'category' => $row['category_name'],
        'stock_status' => $row['stock_status'],
    ];
}
