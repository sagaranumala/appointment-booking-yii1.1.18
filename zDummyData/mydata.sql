/* ============================================================
   USERS
   ============================================================ */
CREATE TABLE users (
    id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    userId VARCHAR(26) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(255) DEFAULT NULL,
    role VARCHAR(255) DEFAULT NULL,
    status TINYINT DEFAULT 1,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP NULL DEFAULT NULL
);

CREATE INDEX idx_users_userId ON users (userId);
CREATE INDEX idx_users_role ON users (role);
CREATE INDEX idx_users_status ON users (status);

/* ============================================================
   CATEGORIES
   ============================================================ */
CREATE TABLE categories (
    id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    categoryId VARCHAR(26) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    parentCategoryId VARCHAR(26) DEFAULT NULL,
    status TINYINT DEFAULT 1,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_categories_categoryId ON categories (categoryId);
CREATE INDEX idx_categories_parentCategoryId ON categories (parentCategoryId);
CREATE INDEX idx_categories_status ON categories (status);

/* ============================================================
   PRODUCTS
   ============================================================ */
CREATE TABLE products (
    id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    productId VARCHAR(26) NOT NULL UNIQUE,
    sku VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    categoryId VARCHAR(26) NOT NULL,
    unitPrice DECIMAL(10,2) NOT NULL,
    costPrice DECIMAL(10,2) NOT NULL,
    reorderLevel INT DEFAULT 5,
    expiryDate DATE DEFAULT NULL,
    isActive TINYINT DEFAULT 1,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP NULL DEFAULT NULL
);

CREATE INDEX idx_products_productId ON products (productId);
CREATE INDEX idx_products_categoryId ON products (categoryId);
CREATE INDEX idx_products_isActive ON products (isActive);
CREATE INDEX idx_products_reorderLevel ON products (reorderLevel);
CREATE INDEX idx_products_name ON products (name);


/* ============================================================
   WAREHOUSES
   ============================================================ */
CREATE TABLE warehouses (
    id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    warehouseId VARCHAR(26) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255),
    status TINYINT DEFAULT 1,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_warehouses_warehouseId ON warehouses (warehouseId);
CREATE INDEX idx_warehouses_status ON warehouses (status);


/* ============================================================
   WAREHOUSE STOCK
   ============================================================ */
CREATE TABLE warehouseStock (
    id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    stockId VARCHAR(26) NOT NULL UNIQUE,
    warehouseId VARCHAR(26) NOT NULL,
    productId VARCHAR(26) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_stock_stockId ON warehouseStock (stockId);
CREATE INDEX idx_stock_productId ON warehouseStock (productId);
CREATE INDEX idx_stock_warehouseId ON warehouseStock (warehouseId);

CREATE INDEX idx_stock_product_warehouse 
ON warehouseStock (productId, warehouseId);


/* ============================================================
   SUPPLIERS
   ============================================================ */
CREATE TABLE suppliers (
    id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    supplierId VARCHAR(26) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    address TEXT,
    status TINYINT DEFAULT 1,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_suppliers_supplierId ON suppliers (supplierId);
CREATE INDEX idx_suppliers_status ON suppliers (status);

/* ============================================================
   PURCHASES
   ============================================================ */
CREATE TABLE purchases (
    id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    purchaseId VARCHAR(26) NOT NULL UNIQUE,
    supplierId VARCHAR(26) NOT NULL,
    warehouseId VARCHAR(26) NOT NULL,
    totalAmount DECIMAL(12,2),
    status VARCHAR(50),
    createdBy VARCHAR(26),
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_purchases_purchaseId ON purchases (purchaseId);
CREATE INDEX idx_purchases_supplierId ON purchases (supplierId);
CREATE INDEX idx_purchases_warehouseId ON purchases (warehouseId);
CREATE INDEX idx_purchases_createdBy ON purchases (createdBy);
CREATE INDEX idx_purchases_createdAt ON purchases (createdAt);

/* ============================================================
   PURCHASE ITEMS
   ============================================================ */
CREATE TABLE purchaseItems (
    id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    purchaseItemId VARCHAR(26) NOT NULL UNIQUE,
    purchaseId VARCHAR(26) NOT NULL,
    productId VARCHAR(26) NOT NULL,
    quantity INT NOT NULL,
    unitCost DECIMAL(10,2) NOT NULL
);

CREATE INDEX idx_purchaseItems_purchaseId ON purchaseItems (purchaseId);
CREATE INDEX idx_purchaseItems_productId ON purchaseItems (productId);

/* ============================================================
   SALES
   ============================================================ */
CREATE TABLE sales (
    id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    saleId VARCHAR(26) NOT NULL UNIQUE,
    customerName VARCHAR(255),
    totalAmount DECIMAL(12,2),
    status VARCHAR(50),
    createdBy VARCHAR(26),
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_sales_saleId ON sales (saleId);
CREATE INDEX idx_sales_createdBy ON sales (createdBy);
CREATE INDEX idx_sales_createdAt ON sales (createdAt);

/* ============================================================
   SALE ITEMS
   ============================================================ */
CREATE TABLE saleItems (
    id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    saleItemId VARCHAR(26) NOT NULL UNIQUE,
    saleId VARCHAR(26) NOT NULL,
    productId VARCHAR(26) NOT NULL,
    quantity INT NOT NULL,
    unitPrice DECIMAL(10,2) NOT NULL
);

CREATE INDEX idx_saleItems_saleId ON saleItems (saleId);
CREATE INDEX idx_saleItems_productId ON saleItems (productId);

/* ============================================================
   STOCK MOVEMENTS (AUDIT CORE)
   ============================================================ */
CREATE TABLE stockMovements (
    id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    movementId VARCHAR(26) NOT NULL UNIQUE,
    productId VARCHAR(26) NOT NULL,
    warehouseId VARCHAR(26) NOT NULL,
    quantity INT NOT NULL,
    movementType ENUM('IN','OUT','ADJUST') NOT NULL,
    referenceType VARCHAR(50),
    referenceId VARCHAR(26),
    createdBy VARCHAR(26),
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_stockMovements_movementId ON stockMovements (movementId);
CREATE INDEX idx_stockMovements_productId ON stockMovements (productId);
CREATE INDEX idx_stockMovements_warehouseId ON stockMovements (warehouseId);
CREATE INDEX idx_stockMovements_createdBy ON stockMovements (createdBy);
CREATE INDEX idx_stockMovements_createdAt ON stockMovements (createdAt);

CREATE INDEX idx_stockMovements_product_warehouse_date 
ON stockMovements (productId, warehouseId, createdAt);

/* ============================================================
   NOTIFICATIONS
   ============================================================ */
CREATE TABLE notifications (
    id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    notificationId VARCHAR(26) NOT NULL UNIQUE,
    userId VARCHAR(26) NOT NULL,
    type VARCHAR(50),
    message TEXT,
    isRead TINYINT DEFAULT 0,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_notifications_notificationId ON notifications (notificationId);
CREATE INDEX idx_notifications_userId ON notifications (userId);
CREATE INDEX idx_notifications_isRead ON notifications (isRead);
CREATE INDEX idx_notifications_createdAt ON notifications (createdAt);

/* ============================================================
   AUDIT LOGS
   ============================================================ */
CREATE TABLE auditLogs (
    id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    auditId VARCHAR(26) NOT NULL UNIQUE,
    userId VARCHAR(26),
    action VARCHAR(100),
    tableName VARCHAR(100),
    recordId VARCHAR(26),
    oldData JSON,
    newData JSON,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_auditLogs_auditId ON auditLogs (auditId);
CREATE INDEX idx_auditLogs_userId ON auditLogs (userId);
CREATE INDEX idx_auditLogs_tableName ON auditLogs (tableName);
CREATE INDEX idx_auditLogs_createdAt ON auditLogs (createdAt);

