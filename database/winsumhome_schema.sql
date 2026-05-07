-- Winsum Home full schema runner
-- Run with MySQL CLI from project root:
-- mysql -u root -p < database/winsumhome_schema.sql

SOURCE database/schema/00_create_database.sql;
SOURCE database/schema/01_customers.sql;
SOURCE database/schema/02_catalog.sql;
SOURCE database/schema/03_variants_inventory.sql;
SOURCE database/schema/04_product_content.sql;
SOURCE database/schema/05_promotions.sql;
SOURCE database/schema/06_cart.sql;
SOURCE database/schema/07_orders.sql;
SOURCE database/schema/08_payments_shipments.sql;
SOURCE database/schema/09_engagement.sql;
SOURCE database/schema/10_blog.sql;
SOURCE database/schema/11_site_content.sql;
SOURCE database/schema/12_seed_winsumhome.sql;
