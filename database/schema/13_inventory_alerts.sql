USE winsumweb;

CREATE TABLE IF NOT EXISTS inventory_alerts (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT NOT NULL,
    order_id BIGINT DEFAULT NULL,
    message TEXT NOT NULL,
    alert_type VARCHAR(30) NOT NULL DEFAULT 'stock_depleted',
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_inventory_alerts_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_inventory_alerts_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    INDEX idx_inventory_alerts_unread (is_read, created_at)
);
