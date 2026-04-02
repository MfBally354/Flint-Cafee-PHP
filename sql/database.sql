-- ============================================
-- FLINT CAFE - Database Schema
-- ============================================

CREATE DATABASE IF NOT EXISTS flintcafe CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE flintcafe;

-- Tables
CREATE TABLE IF NOT EXISTS tables_cafe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_number INT NOT NULL UNIQUE,
    table_name VARCHAR(50) NOT NULL,
    capacity INT DEFAULT 4,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(50) DEFAULT '☕',
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    is_available TINYINT(1) DEFAULT 1,
    is_featured TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(20) NOT NULL UNIQUE,
    table_id INT NOT NULL,
    customer_name VARCHAR(100) DEFAULT 'Guest',
    status ENUM('pending','confirmed','preparing','ready','completed','cancelled') DEFAULT 'pending',
    payment_status ENUM('unpaid','paid') DEFAULT 'unpaid',
    payment_method ENUM('cash','qris','transfer') DEFAULT 'cash',
    subtotal DECIMAL(10,2) DEFAULT 0,
    tax DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (table_id) REFERENCES tables_cafe(id)
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    notes VARCHAR(255),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
);

-- ============================================
-- SEED DATA
-- ============================================

-- Tables
INSERT INTO tables_cafe (table_number, table_name, capacity) VALUES
(1, 'Meja 1', 2), (2, 'Meja 2', 2), (3, 'Meja 3', 4),
(4, 'Meja 4', 4), (5, 'Meja 5', 6), (6, 'Meja 6', 6),
(7, 'Meja 7', 4), (8, 'Meja 8', 4), (9, 'Meja 9', 2),
(10, 'Meja 10', 8);

-- Categories
INSERT INTO categories (name, slug, icon, sort_order) VALUES
('Kopi', 'kopi', '☕', 1),
('Non-Kopi', 'non-kopi', '🍵', 2),
('Minuman Segar', 'minuman-segar', '🥤', 3),
('Makanan Berat', 'makanan-berat', '🍽️', 4),
('Cemilan', 'cemilan', '🍰', 5),
('Paket Hemat', 'paket-hemat', '🎁', 6);

-- Menu Items - Kopi
INSERT INTO menu_items (category_id, name, description, price, is_featured, sort_order) VALUES
(1, 'Espresso Single', 'Shot espresso pekat dari biji kopi pilihan Flint', 18000, 0, 1),
(1, 'Espresso Double', 'Dua shot espresso untuk yang butuh energi ekstra', 28000, 0, 2),
(1, 'Americano', 'Espresso dengan air panas, clean dan bold', 22000, 0, 3),
(1, 'Cappuccino', 'Espresso dengan steamed milk dan foam lembut', 28000, 1, 4),
(1, 'Café Latte', 'Espresso dengan banyak steamed milk, creamy & smooth', 30000, 1, 5),
(1, 'Flat White', 'Ristretto shot dengan microfoam susu, intens namun lembut', 32000, 0, 6),
(1, 'Caramel Macchiato', 'Vanilla latte dengan drizzle karamel manis', 35000, 1, 7),
(1, 'Hazelnut Latte', 'Latte dengan sirup hazelnut, aroma kacang yang khas', 35000, 0, 8),
(1, 'Mocha', 'Espresso dengan cokelat dan susu, dessert in a cup', 35000, 1, 9),
(1, 'V60 Pour Over', 'Manual brew dengan metode V60, nuansa rasa yang kompleks', 38000, 0, 10),
(1, 'Cold Brew', 'Kopi cold brew 12 jam, smooth dan less acidic', 32000, 0, 11),
(1, 'Es Kopi Susu', 'Kopi susu segar dengan brown sugar, signature Flint', 28000, 1, 12);

-- Menu Items - Non-Kopi
INSERT INTO menu_items (category_id, name, description, price, is_featured, sort_order) VALUES
(2, 'Matcha Latte', 'Matcha Jepang premium dengan susu full cream', 32000, 1, 1),
(2, 'Taro Latte', 'Latte rasa taro ubi ungu yang creamy dan manis', 30000, 0, 2),
(2, 'Chocolate Hazelnut', 'Minuman cokelat dengan sentuhan hazelnut mewah', 30000, 1, 3),
(2, 'Vanilla Steamlk', 'Susu steamed dengan vanilla, lembut dan hangat', 22000, 0, 4),
(2, 'Chai Tea Latte', 'Teh chai dengan rempah khas dan susu hangat', 28000, 0, 5),
(2, 'Hojicha Latte', 'Teh hojicha panggang Jepang dengan susu, earthy & warm', 32000, 0, 6);

-- Menu Items - Minuman Segar
INSERT INTO menu_items (category_id, name, description, price, is_featured, sort_order) VALUES
(3, 'Lemon Mint Squash', 'Soda lemon segar dengan daun mint, menyegarkan', 25000, 1, 1),
(3, 'Strawberry Yakult', 'Stroberi segar dengan yakult, manis dan probiotik', 28000, 0, 2),
(3, 'Blue Lemonade', 'Lemonade butterfly pea yang cantik dan segar', 28000, 1, 3),
(3, 'Mojito Mocktail', 'Mocktail mojito segar dengan lime dan mint', 30000, 0, 4),
(3, 'Sparkling Grape', 'Soda anggur dingin, manis dan fruity', 25000, 0, 5),
(3, 'Jus Alpukat', 'Alpukat creamy dengan susu dan cokelat', 28000, 0, 6);

-- Menu Items - Makanan Berat
INSERT INTO menu_items (category_id, name, description, price, is_featured, sort_order) VALUES
(4, 'Nasi Goreng Flint', 'Nasi goreng spesial dengan telur mata sapi dan ayam', 35000, 1, 1),
(4, 'Pasta Aglio Olio', 'Pasta dengan bawang putih, olive oil, dan chili flakes', 38000, 1, 2),
(4, 'Sandwich Chicken Melt', 'Sandwich ayam panggang dengan keju melt dan sayuran segar', 35000, 0, 3),
(4, 'Salad Bowl Quinoa', 'Quinoa dengan sayuran organik, alpukat, dan dressing lemon', 40000, 0, 4),
(4, 'Overnight Oats', 'Oat dengan buah segar dan granola, sehat dan mengenyangkan', 30000, 0, 5),
(4, 'Croissant Sandwich', 'Croissant butter dengan smoked beef dan telur', 40000, 1, 6);

-- Menu Items - Cemilan
INSERT INTO menu_items (category_id, name, description, price, is_featured, sort_order) VALUES
(5, 'Croissant Butter', 'Croissant french buttery dan flaky, freshly baked', 22000, 0, 1),
(5, 'Brownies Fudge', 'Brownies cokelat fudgy, moist dan rich', 25000, 1, 2),
(5, 'Cheesecake Slice', 'New york cheesecake dengan graham cracker crust', 30000, 1, 3),
(5, 'Banana Bread', 'Banana bread homemade dengan walnut, moist dan harum', 22000, 0, 4),
(5, 'French Fries', 'Kentang goreng crispy dengan saus pilihan', 22000, 0, 5),
(5, 'Donat Glaze', '2 pcs donat glaze vanilla/cokelat, fluffy dan manis', 20000, 0, 6);

-- Menu Items - Paket Hemat
INSERT INTO menu_items (category_id, name, description, price, is_featured, sort_order) VALUES
(6, 'Paket Pagi ☀️', 'Croissant + Café Latte/Matcha Latte (pilih 1)', 42000, 1, 1),
(6, 'Paket Makan Siang 🍽️', 'Nasi Goreng Flint + Es Kopi Susu', 55000, 1, 2),
(6, 'Paket Santai ☕', 'Brownies Fudge + Minuman Segar (pilih 1)', 45000, 0, 3),
(6, 'Paket Kerja 💻', 'Pasta + Cold Brew + Cheesecake Slice', 90000, 1, 4);
