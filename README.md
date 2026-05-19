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

Nếu DB đã tạo trước đó, chạy thêm:

```bash
mysql -u root -p winsumweb < database/schema/13_inventory_alerts.sql
mysql -u root -p winsumweb < database/schema/14_seed_inventory.sql
mysql -u root -p winsumweb < database/schema/15_customer_role_admin.sql
```

3. Cấu hình `config/database.php` (mẫu: `config/database.example.php`), database `winsumweb`.
4. Mở: `http://localhost/webfinal/index.php` (lần đầu tự tạo tài khoản admin trong DB nếu chưa có).

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
- Đăng ký / đăng nhập, xem đơn hàng
- Blog tin tức

### Đăng nhập / đăng ký (trang chủ)
- Hai form tách riêng ngay dưới banner: **Đăng nhập** | **Đăng kí tài khoản** (theme tối như mẫu)
- Đăng nhập bằng SĐT, email hoặc tên đăng nhập · Mật khẩu tối thiểu 6 ký tự

### Quản trị (sau khi đăng nhập admin)
- **Tổng quan** — `?view=admin-dashboard` (thống kê, đơn mới, liên kết nhanh)
- **Đơn hàng** — `?view=admin-orders`
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
| `admin-login` | Chuyển về trang chủ (đăng nhập chung) |
| `admin-dashboard` | Bảng điều khiển |
| `admin-orders` | Quản lý đơn |
| `admin-products` | CRUD sản phẩm |
| `admin-reviews` | Duyệt đánh giá |
| `blog-editor` | Soạn blog |

## Cấu trúc thư mục

```
config/                 Cấu hình DB & admin
database/schema/        Script SQL
includes/               PHP (repository, views)
  product-detail.php    Trang chi tiết SP
  admin-products.php    CRUD sản phẩm
  admin-reviews.php     Quản lý đánh giá
assets/css/
  product-detail.css    Giao diện chi tiết SP
  admin.css             Giao diện quản trị
assets/js/product-detail.js  Gallery & tabs
index.php               Front controller
```

## Demo chi tiết sản phẩm

Sau khi import DB, thử:
- `index.php?view=product&slug=den-treo-tran-axis`
- Gửi đánh giá → admin duyệt tại `?view=admin-reviews`
