USE winsumweb;

INSERT INTO brands (name, slug, description, is_active)
VALUES ('Winsum Home', 'winsum-home', 'Nội thất và chiếu sáng cao cấp', 1)
ON DUPLICATE KEY UPDATE description = VALUES(description), is_active = VALUES(is_active);

INSERT INTO categories (name, slug, description, sort_order, is_active)
VALUES
('ĐÈN THẢ TRẦN', 'den-tha-tran', 'Đèn treo trần, phù hợp phòng khách, phòng ăn và không gian rộng.', 1, 1),
('ĐÈN TƯỜNG', 'den-tuong', 'Đèn tường trang trí, tạo điểm nhấn hành lang và khu vực sinh hoạt.', 2, 1),
('ĐÈN BÀN', 'den-ban', 'Đèn bàn làm việc, đọc sách và trang trí bàn trà, bàn làm việc.', 3, 1),
('ĐÈN SÀN', 'den-san', 'Đèn sàn đứng, chiếu sáng góc sofa hoặc khu tiếp khách.', 4, 1),
('ĐÈN CHÙM', 'den-chum', 'Đèn chùm cao cấp, làm điểm nhấn cho phòng khách sang trọng.', 5, 1),
('KỆ TRANG TRÍ', 'ke-trang-tri', 'Kệ và phụ kiện trang trí bổ sung cho không gian nội thất.', 6, 1)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    sort_order = VALUES(sort_order),
    is_active = VALUES(is_active);

INSERT INTO coupons (code, name, description, discount_type, discount_value, min_order_amount, per_customer_limit, is_active)
VALUES ('WINSUMXINCHAO', 'Xin chào Winsum', 'Giảm 40.000đ cho toàn bộ đơn hàng', 'fixed', 40000, 0, 1, 1)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    discount_type = VALUES(discount_type),
    discount_value = VALUES(discount_value),
    per_customer_limit = VALUES(per_customer_limit),
    is_active = VALUES(is_active);

INSERT INTO payment_methods (code, name, description, is_active)
VALUES
('cod', 'Thanh toán khi nhận hàng', 'Khách hàng thanh toán tiền mặt khi nhận hàng', 1),
('bank_transfer', 'Chuyển khoản ngân hàng', 'Chuyển khoản trước khi giao hàng', 1)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    is_active = VALUES(is_active);

INSERT INTO shipping_methods (code, name, fee, eta_label, is_active)
VALUES
('express_24h', 'Giao hàng hỏa tốc', 30000, 'Nhận hàng trong vòng 24h', 1),
('standard', 'Giao hàng tiêu chuẩn', 20000, 'Nhận hàng 2-4 ngày', 1)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    fee = VALUES(fee),
    eta_label = VALUES(eta_label),
    is_active = VALUES(is_active);

INSERT INTO blog_categories (name, slug, description, is_active)
VALUES ('Tin tức', 'tin-tuc', 'Bài viết xu hướng nội thất và chiếu sáng', 1)
ON DUPLICATE KEY UPDATE description = VALUES(description), is_active = VALUES(is_active);

INSERT INTO blog_posts (category_id, slug, title, excerpt, content, category, image, read_time, published_at, is_featured, status)
SELECT bc.id, x.slug, x.title, x.excerpt, x.content, 'Tin tức', x.image, x.read_time, x.published_at, x.is_featured, 'published'
FROM (
    SELECT 'den-treo-tran-axis-thong-minh' AS slug,
           'Đèn Treo Trần AXIS Thông Minh: Giải Pháp Tối Ưu Cho Cuộc Sống Hiện Đại' AS title,
           'Thường được biết đến là một giải pháp tiện ích cho gia đình, chiếc đèn treo trần tích hợp giàn phơi đồ tự động lại mang đến nhiều tiện lợi trong không gian sống hiện đại...' AS excerpt,
           'Đèn treo trần AXIS là lựa chọn được nhiều gia chủ yêu thích nhờ khả năng cân bằng giữa công năng và thẩm mỹ.
Điểm nổi bật của AXIS nằm ở khả năng điều chỉnh cường độ sáng theo từng thời điểm trong ngày.
Ngoài ra, đèn hỗ trợ điều khiển qua ứng dụng trên điện thoại để bật/tắt và hẹn giờ linh hoạt.' AS content,
           'assets/images/blog_1.png' AS image,
           '2 phút đọc' AS read_time,
           DATE('2025-11-02') AS published_at,
           1 AS is_featured
    UNION ALL
    SELECT 've-dep-den-bauhaus',
           'Điểm Nhấn Hoài Cổ: Vẻ Đẹp Của Đèn Treo BAUHAUS',
           'Trong thế giới thiết kế nội thất, ánh sáng không chỉ đơn thuần là nguồn chiếu rọi mà còn là một tác phẩm nghệ thuật, một tuyên ngôn cá tính trong không gian sống...',
           'BAUHAUS đại diện cho triết lý form follows function và tạo nên ngôn ngữ thiết kế cân bằng.
Khi đặt trong phòng khách, đèn BAUHAUS trở thành điểm nhấn tinh tế mà không phô trương.
Tông màu trung tính và vật liệu mờ giúp không gian vẫn ấm cúng nhưng hiện đại.',
           'assets/images/blog_2.png',
           '3 phút đọc',
           DATE('2025-10-29'),
           1
    UNION ALL
    SELECT 'ph5-pendant-lamp-tuyet-tac-anh-sang',
           'PH5 PENDANT LAMP: Tuyệt Tác Ánh Sáng Và Triết Lý Sống Bắc Âu',
           'Trong lịch sử thiết kế chiếu sáng, có rất ít sản phẩm đạt đến tầm vóc và sự ảnh hưởng của Đèn Treo PH5 với khả năng chống chói và phân bổ ánh sáng hoàn hảo...',
           'PH5 là biểu tượng của thiết kế Bắc Âu với cấu trúc nhiều lớp tán sáng.
Thiết kế này giúp ánh sáng dịu mắt, phân bổ đều và nâng chất lượng sinh hoạt thường ngày.
Triết lý bền vững và trường tồn là lý do PH5 luôn được ưa chuộng.',
           'assets/images/blog_3.png',
           '4 phút đọc',
           DATE('2025-10-27'),
           1
) x
JOIN blog_categories bc ON bc.slug = 'tin-tuc'
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    excerpt = VALUES(excerpt),
    content = VALUES(content),
    image = VALUES(image),
    read_time = VALUES(read_time),
    published_at = VALUES(published_at),
    is_featured = VALUES(is_featured),
    status = VALUES(status);

-- ------------------------------------------------------------
-- HOME PAGE FRAMEWORK DATA
-- Khung dữ liệu mẫu cho trang chủ; có thể chỉnh sửa/đổ thêm sau.
-- ------------------------------------------------------------

INSERT INTO banners (title, subtitle, image_url, link_url, position, sort_order, is_active, starts_at, ends_at)
SELECT
    'Nội thất và chiếu sáng cao cấp cho không gian sống đẳng cấp',
    'Khám phá bộ sưu tập đèn trang trí, nội thất nhập khẩu và giải pháp thiết kế đồng bộ theo chuẩn châu Âu.',
    'assets/images/blog_3.png',
    '#',
    'home_hero',
    1,
    1,
    NULL,
    NULL
WHERE NOT EXISTS (
    SELECT 1
    FROM banners
    WHERE position = 'home_hero'
      AND is_active = 1
);

INSERT INTO products (
    brand_id, category_id, name, slug, short_description, description, sku, product_type,
    base_price, compare_at_price, stock_status, material, color, warranty_months,
    is_featured, is_active, published_at
)
SELECT
    b.id,
    c.id,
    x.name,
    x.slug,
    x.short_description,
    x.description,
    x.sku,
    'simple',
    x.base_price,
    x.compare_at_price,
    'in_stock',
    x.material,
    x.color,
    x.warranty_months,
    x.is_featured,
    1,
    NOW()
FROM (
    SELECT
        'den-treo-tran-axis' AS slug,
        'Đèn treo trần AXIS' AS name,
        'Giải pháp chiếu sáng tinh gọn cho phòng khách hiện đại.' AS short_description,
        'Mẫu đèn treo trần AXIS mang phong cách hiện đại, ánh sáng chống chói và phù hợp nhiều không gian sống.' AS description,
        'WS-AXIS-01' AS sku,
        12800000.00 AS base_price,
        14500000.00 AS compare_at_price,
        'Hợp kim + Acrylic' AS material,
        'Đen nhám' AS color,
        24 AS warranty_months,
        1 AS is_featured,
        'den-tha-tran' AS category_slug
    UNION ALL
    SELECT
        'den-treo-bauhaus',
        'Đèn treo BAUHAUS',
        'Điểm nhấn hoài cổ cho không gian nội thất cao cấp.',
        'Thiết kế lấy cảm hứng từ triết lý Bauhaus, cân bằng giữa thẩm mỹ và công năng sử dụng.',
        'WS-BAU-02',
        9650000.00,
        10900000.00,
        'Kim loại sơn tĩnh điện' ,
        'Vàng đồng',
        24,
        1,
        'den-tha-tran'
    UNION ALL
    SELECT
        'ph5-pendant-lamp',
        'PH5 Pendant Lamp',
        'Thiết kế Bắc Âu biểu tượng với ánh sáng phân bổ đồng đều.',
        'Mẫu đèn PH5 mang lại ánh sáng dịu mắt, phù hợp khu vực bàn ăn và phòng khách sang trọng.',
        'WS-PH5-03',
        15200000.00,
        16900000.00,
        'Nhôm phủ sơn',
        'Cam đất',
        36,
        1,
        'den-tha-tran'
) x
JOIN brands b ON b.slug = 'winsum-home'
JOIN categories c ON c.slug = x.category_slug
ON DUPLICATE KEY UPDATE
    category_id = VALUES(category_id),
    name = VALUES(name),
    short_description = VALUES(short_description),
    description = VALUES(description),
    base_price = VALUES(base_price),
    compare_at_price = VALUES(compare_at_price),
    stock_status = VALUES(stock_status),
    material = VALUES(material),
    color = VALUES(color),
    warranty_months = VALUES(warranty_months),
    is_featured = VALUES(is_featured),
    is_active = VALUES(is_active),
    published_at = VALUES(published_at);

DELETE pi
FROM product_images pi
JOIN products p ON p.id = pi.product_id
WHERE p.slug IN ('den-treo-tran-axis', 'den-treo-bauhaus', 'ph5-pendant-lamp');

INSERT INTO product_images (product_id, image_url, alt_text, sort_order, is_primary)
SELECT p.id, x.image_url, x.alt_text, x.sort_order, x.is_primary
FROM (
    SELECT 'den-treo-tran-axis' AS product_slug, 'assets/images/blog_1.png' AS image_url, 'Đèn treo trần AXIS' AS alt_text, 1 AS sort_order, 1 AS is_primary
    UNION ALL
    SELECT 'den-treo-bauhaus', 'assets/images/blog_2.png', 'Đèn treo BAUHAUS', 1, 1
    UNION ALL
    SELECT 'ph5-pendant-lamp', 'assets/images/blog_3.png', 'PH5 Pendant Lamp', 1, 1
) x
JOIN products p ON p.slug = x.product_slug;

INSERT INTO product_reviews (product_id, reviewer_name, rating, title, content, status)
SELECT p.id, 'Minh Anh', 5, 'Đèn đẹp, đúng mô tả', 'Lắp nhanh, ánh sáng dịu mắt, phù hợp phòng khách.', 'approved'
FROM products p WHERE p.slug = 'den-treo-tran-axis'
AND NOT EXISTS (SELECT 1 FROM product_reviews r WHERE r.product_id = p.id AND r.reviewer_name = 'Minh Anh');

INSERT INTO product_reviews (product_id, reviewer_name, rating, title, content, status)
SELECT p.id, 'Tuấn Kiệt', 4, 'Hài lòng', 'Thiết kế sang, giá hơi cao nhưng xứng đáng.', 'approved'
FROM products p WHERE p.slug = 'ph5-pendant-lamp'
AND NOT EXISTS (SELECT 1 FROM product_reviews r WHERE r.product_id = p.id AND r.reviewer_name = 'Tuấn Kiệt');

UPDATE products p
SET rating_average = COALESCE((
        SELECT ROUND(AVG(r.rating), 2) FROM product_reviews r
        WHERE r.product_id = p.id AND r.status = 'approved'
    ), 0),
    rating_count = COALESCE((
        SELECT COUNT(*) FROM product_reviews r
        WHERE r.product_id = p.id AND r.status = 'approved'
    ), 0)
WHERE p.slug IN ('den-treo-tran-axis', 'den-treo-bauhaus', 'ph5-pendant-lamp');

INSERT INTO warehouses (name, code, address, is_default)
VALUES ('Kho Winsum chính', 'MAIN', 'Kho trung tâm Winsum Home', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), is_default = VALUES(is_default);

INSERT INTO inventory_items (product_id, variant_id, warehouse_id, quantity_on_hand, quantity_reserved, reorder_level)
SELECT p.id, NULL, w.id, 50, 0, 5
FROM products p
CROSS JOIN warehouses w
WHERE w.code = 'MAIN'
  AND NOT EXISTS (
      SELECT 1 FROM inventory_items ii
      WHERE ii.product_id = p.id
        AND ii.warehouse_id = w.id
        AND ii.variant_id IS NULL
  );

INSERT INTO customers (customer_code, full_name, phone, email, password_hash, status, role)
VALUES (
    'ADM00001',
    'admin',
    '0901000000',
    'admin@winsumhome.vn',
    '$2y$10$LiwDdPmNl40o4nDcOw8H9O4UyStaVDFyclvHztxZjxfEP6T4Fna3K',
    'active',
    'admin'
)
ON DUPLICATE KEY UPDATE
    password_hash = VALUES(password_hash),
    status = 'active',
    role = 'admin';
