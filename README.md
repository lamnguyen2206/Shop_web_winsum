# Winsum Home — Website bán nội thất & chiếu sáng

Đồ án web thuần **PHP + MySQL**, chạy trên XAMPP.

## Yêu cầu

- PHP 8.0+
- MySQL / MariaDB
- Apache (XAMPP)

## Cài đặt

1. Copy project vào `htdocs/webfinal`.
2. Import database:

```bash
mysql -u root -p < database/winsumhome_schema.sql
```

Nếu DB đã tạo trước đó (thiếu bảng cảnh báo tồn kho):

```bash
mysql -u root -p winsumweb < database/schema/13_inventory_alerts.sql
```

3. Cấu hình `config/database.php` (mẫu: `config/database.example.php`), database `winsumweb`.
4. Mở: `http://localhost/webfinal/index.php` (admin + tồn kho mẫu nằm trong `12_seed_winsumhome.sql`; DB cũ có thể tự bổ sung admin khi tải trang).

## Tài khoản demo

| Loại | Thông tin |
|------|-----------|
| Admin | Đăng nhập **chung** form khách: `admin` / `admin123` (hoặc `admin@winsumhome.vn`, `0901000000`) → chuyển tới bảng quản trị |
| Mã giảm giá | `WINSUMXINCHAO` (giảm 40.000đ) |
| Khách hàng | Đăng ký qua popup **Đăng ký** trên trang chủ |

## Chức năng

### Khách hàng
- Trang chủ, danh mục (lọc, tìm kiếm, phân trang)
- **Chi tiết sản phẩm**: gallery ảnh, thông số, tab mô tả/đánh giá, gửi đánh giá
- Giỏ hàng, thanh toán, mã giảm giá từ database
- **Tồn kho**: trừ kho khi đặt hàng; hết kho → tự chuyển **Đặt trước** + cảnh báo admin
- Đăng ký / đăng nhập, xem đơn hàng, hủy đơn khi còn chờ xử lý (tài khoản đã đăng nhập)
- Blog tin tức

### Đăng nhập / đăng ký
- Không có `login.php` riêng — dùng popup trên header (`data-open-auth`) hoặc tham số URL:
  - `index.php?view=orders&auth=login` — mở form đăng nhập trên trang đơn hàng
  - `index.php?view=account&auth=login` — trang tài khoản
  - `index.php?view=order-detail&code=...&auth=login` — chi tiết đơn (sau khi đăng nhập)
- Helper PHP: `auth_login_url($view, $params)`, `auth_register_url($view, $params)` trong `includes/helpers.php`
- Toast khi đăng nhập thành công; SĐT, email hoặc tên · mật khẩu tối thiểu 6 ký tự

### Quản trị (sau khi đăng nhập admin)
- **Tổng quan** — `?view=admin-dashboard` (thống kê, đơn mới, liên kết nhanh)
- **Đơn hàng** — `?view=admin-orders`
- **Khách hàng** — `?view=admin-customers` (tìm kiếm, trạng thái, đơn hàng)
- **Sản phẩm (CRUD)** — `?view=admin-products` (số lượng tồn, cảnh báo hết hàng)
- **Đánh giá** — `?view=admin-reviews`
- **Soạn blog** — `?view=blog-editor`

### Bảo mật
- CSRF token, prepared statements, `password_hash`, khóa trang admin/blog-editor

## URL trang

| View | Mô tả |
|------|--------|
| `home` | Trang chủ |
| `catalog` | Sản phẩm |
| `product&slug=...` | Chi tiết SP |
| `cart` / `checkout` | Giỏ hàng / Thanh toán |
| `account` / `orders` | Tài khoản / Đơn hàng |
| `blog` / `post` | Tin tức |
| `admin-dashboard` | Bảng điều khiển |
| `admin-orders` | Quản lý đơn (tất cả đơn) |
| `admin-customers` | Quản lý khách hàng |
| `admin-products` | CRUD sản phẩm |
| `admin-reviews` | Duyệt đánh giá |
| `blog-editor` | Soạn blog |

## Cấu trúc thư mục

```
bootstrap/app.php       Khởi động: session, DB, xử lý POST, auth
index.php               Front controller (layout + view)
config/                 Cấu hình database
database/schema/        Script SQL
includes/
  routes.php            Whitelist view & assets
  helpers.php           e(), redirect(), app_url()
  flash.php             Thông báo PRG theo trang
  *-post.php            Xử lý form trước khi in HTML
  *-repository.php      Truy vấn MySQL
  layout/               head.php, foot.php
  errors/404.php
assets/css|js/
```

**Luồng request:** `index.php` → `bootstrap/app.php` (POST handlers) → layout → `includes/{view}.php`

### Ghi chú nghiệp vụ

- **Biến thể sản phẩm** (`product_variants`): có trong schema nhưng storefront hiện dùng một SKU/ giá mỗi sản phẩm.
- **Doanh thu admin**: “Đã thu” = đơn không hủy/trả và `payment_status = paid`; “Giá trị đơn” = tổng đơn chưa hủy/trả (ước tính, kể cả COD chưa thu).
- **Hủy/trả đơn**: hoàn tồn kho một lần (cờ `inventory_restocked`); khách hủy từ web khi đơn `pending`/`processing`.

## Demo chi tiết sản phẩm

Sau khi import DB, thử:
- `index.php?view=product&slug=den-treo-tran-axis`
- Gửi đánh giá → admin duyệt tại `?view=admin-reviews`
