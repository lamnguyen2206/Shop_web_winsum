USE winsumweb;

-- Vai trò tài khoản: khách hàng hoặc quản trị (đăng nhập chung một form)
ALTER TABLE customers
    ADD COLUMN role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer' AFTER status;

-- Tài khoản admin mặc định: admin / admin123
-- Đăng nhập bằng: admin | admin@winsumhome.vn | 0901000000
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
