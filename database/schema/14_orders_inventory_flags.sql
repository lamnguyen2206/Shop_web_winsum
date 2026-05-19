USE winsumweb;

ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS inventory_deducted TINYINT(1) NOT NULL DEFAULT 0 AFTER payment_status,
    ADD COLUMN IF NOT EXISTS inventory_restocked TINYINT(1) NOT NULL DEFAULT 0 AFTER inventory_deducted;

ALTER TABLE order_items
    ADD COLUMN IF NOT EXISTS stock_deducted TINYINT(1) NOT NULL DEFAULT 0 AFTER line_total;
