USE winsumweb;

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
