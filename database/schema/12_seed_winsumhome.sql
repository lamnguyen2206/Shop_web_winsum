USE winsumweb;

INSERT INTO brands (name, slug, description, is_active)
VALUES ('Winsum Home', 'winsum-home', 'Nội thất và chiếu sáng cao cấp', 1)
ON DUPLICATE KEY UPDATE description = VALUES(description), is_active = VALUES(is_active);

INSERT INTO categories (name, slug, sort_order, is_active)
VALUES
('ĐÈN THẢ TRẦN', 'den-tha-tran', 1, 1),
('ĐÈN TƯỜNG', 'den-tuong', 2, 1),
('ĐÈN BÀN', 'den-ban', 3, 1),
('ĐÈN SÀN', 'den-san', 4, 1),
('ĐÈN CHÙM', 'den-chum', 5, 1),
('KỆ TRANG TRÍ', 'ke-trang-tri', 6, 1)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
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
