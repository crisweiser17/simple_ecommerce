CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    concentration TEXT NOT NULL,
    sku TEXT,
    default_cost REAL DEFAULT 0.0,
    default_batch_cost REAL DEFAULT 0.0,
    default_batch_quantity INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS inventory (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    location TEXT NOT NULL CHECK(location IN ('USA', 'BRA')),
    quantity INTEGER DEFAULT 0,
    avg_unit_cost REAL DEFAULT 0.0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    UNIQUE(product_id, location)
);

CREATE TABLE IF NOT EXISTS movements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    type TEXT NOT NULL CHECK(type IN ('PURCHASE_CHINA', 'TRANSFER_BRAZIL', 'SALE_USA', 'SALE_BRAZIL')),
    description TEXT,
    total_freight REAL DEFAULT 0.0,
    total_other_costs REAL DEFAULT 0.0,
    exchange_rate REAL DEFAULT 1.0,
    total_extra_costs REAL DEFAULT 0.0,
    currency TEXT,
    supplier_id INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
);

CREATE TABLE IF NOT EXISTS movement_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    movement_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL,
    unit_price_or_cost REAL NOT NULL, -- Cost for Purchase/Transfer, Price for Sale
    final_unit_cost REAL, -- Calculated cost after freight allocation
    FOREIGN KEY (movement_id) REFERENCES movements(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);
