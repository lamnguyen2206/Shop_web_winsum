# Winsum Home

Website bán **nội thất & chiếu sáng** — PHP thuần + MySQL, chạy trên XAMPP.

## Yêu cầu

- PHP 8.0+
- MySQL / MariaDB
- Apache (XAMPP)

## Cài đặt nhanh

1. Đặt project vào `htdocs/webfinal`.
2. Import database (từ thư mục gốc project):

```bash
mysql -u root -p < database/winsumhome_schema.sql
```

3. Sao chép và chỉnh `config/database.php` (mẫu: `config/database.example.php`), database mặc định: `winsumweb`.
4. Mở trình duyệt: `http://localhost/webfinal/index.php`

### Nâng cấp database đã có sẵn

Chạy từng file schema bổ sung khi thiếu bảng:

```bash
mysql -u root -p winsumweb < database/schema/13_inventory_alerts.sql
mysql -u root -p winsumweb < database/schema/14_blog_comments.sql
```

> Lần tải trang đầu sau khi cập nhật code, hệ thống cũng tự tạo bảng `blog_comments` nếu chưa có.

## Tài khoản demo

| Loại | Cách đăng nhập |
|------|----------------|
| **Admin** | Form **Đăng nhập** trên header: `admin` / `admin123` (hoặc `admin@winsumhome.vn`, `0901000000`) → chuyển bảng quản trị |
| **Mã giảm giá** | `WINSUMXINCHAO` — giảm 40.000đ |
| **Khách hàng** | Đăng ký qua popup **Đăng ký** trên trang chủ |

## Chức năng chính

### Khách hàng (storefront)

- Trang chủ, danh mục sản phẩm (lọc, tìm kiếm, phân trang)
- Chi tiết sản phẩm: gallery, thông số, tab mô tả / đánh giá, gửi đánh giá (chờ duyệt)
- Giỏ hàng, thanh toán, mã giảm giá
- Tồn kho: trừ kho khi đặt; hết kho → đặt trước + cảnh báo admin
- Đăng ký / đăng nhập (popup), tài khoản, đơn hàng, hủy đơn khi còn chờ xử lý
- **Blog**: danh sách tin, chi tiết bài, **bình luận** (gửi → chờ admin duyệt mới hiển thị)

### Blog & bình luận

| Thao tác | URL / vị trí |
|----------|----------------|
| Danh sách tin | `?view=blog` |
| Chi tiết + form bình luận | `?view=post&slug=...` (mục **Bình luận** cuối bài) |
| Soạn bài (admin) | `?view=blog-editor` |
| Quản lý bài | `?view=admin-blog` |
| Duyệt bình luận | `?view=admin-blog-comments` |

Luồng bình luận: khách gửi → trạng thái `pending` → admin **Duyệt** / **Từ chối** / **Xóa** → chỉ bình luận `approved` hiện trên trang bài viết.

### Đăng nhập / đăng ký

Không có `login.php` riêng — dùng popup header (`data-open-auth`) hoặc tham số URL:

- `index.php?view=orders&auth=login`
- `index.php?view=account&auth=login`
- `index.php?view=order-detail&code=...&auth=login`

Helper: `auth_login_url()`, `auth_register_url()` trong `includes/helpers.php`.

### Quản trị (sau khi đăng nhập admin)

| Trang | View |
|-------|------|
| Tổng quan | `admin-dashboard` |
| Đơn hàng | `admin-orders` |
| Khách hàng | `admin-customers` |
| Sản phẩm (CRUD) | `admin-products` |
| Đánh giá SP | `admin-reviews` |
| Quản lý blog | `admin-blog` |
| Bình luận blog | `admin-blog-comments` |
| Soạn blog | `blog-editor` |

### Bảo mật

- CSRF token trên form POST
- Prepared statements, `password_hash`
- Trang `admin-*` và `blog-editor` yêu cầu phiên admin

## URL tham chiếu

| View | Mô tả |
|------|--------|
| `home` | Trang chủ |
| `catalog` | Sản phẩm |
| `product&slug=...` | Chi tiết SP |
| `cart` / `checkout` | Giỏ / Thanh toán |
| `account` / `orders` | Tài khoản / Đơn hàng |
| `blog` / `post` | Tin tức / Chi tiết + bình luận |
| `admin-dashboard` | Bảng điều khiển |
| `admin-orders` | Quản lý đơn |
| `admin-customers` | Khách hàng |
| `admin-products` | CRUD sản phẩm |
| `admin-reviews` | Duyệt đánh giá SP |
| `admin-blog` | Quản lý bài blog |
| `admin-blog-comments` | Duyệt bình luận blog |
| `blog-editor` | Soạn bài |

## Cấu trúc thư mục

```
bootstrap/app.php          Session, DB, POST handlers, auth
index.php                  Front controller
config/                    Cấu hình database
database/schema/           SQL theo module (01–14)
includes/
  routes.php               Whitelist view & assets
  helpers.php, flash.php, csrf.php
  *-repository.php         Truy vấn MySQL (blog, comment, review, order…)
  *-post.php               Xử lý form (storefront, admin)
  layout/                  head.php, foot.php
assets/css|js/
```

**Luồng request:** `index.php` → `bootstrap/app.php` (POST trước HTML) → layout → `includes/{view}.php`

## Ghi chú nghiệp vụ

- **Biến thể SP** (`product_variants`): có trong schema; storefront hiện một SKU/giá mỗi sản phẩm.
- **Doanh thu admin**: “Đã thu” = đơn không hủy/trả và `payment_status = paid`.
- **Hủy/trả đơn**: hoàn tồn kho một lần (`inventory_restocked`).

## Thử nhanh

```text
index.php?view=product&slug=den-treo-tran-axis
index.php?view=post&slug=<slug-bai-blog>
index.php?view=admin-blog-comments&status=pending
```

Sau khi gửi bình luận trên bài viết, vào **Bình luận blog** trong menu admin để duyệt.
