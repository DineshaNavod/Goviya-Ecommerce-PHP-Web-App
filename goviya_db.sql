
CREATE DATABASE IF NOT EXISTS goviya_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE goviya_db;


CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)        NOT NULL,
    email       VARCHAR(150)        NOT NULL UNIQUE,
    password    VARCHAR(255)        NOT NULL,          
    phone       VARCHAR(20),
    address     TEXT,
    role        ENUM('customer','admin') DEFAULT 'customer',
    reset_token VARCHAR(64)         DEFAULT NULL,
    reset_expires DATETIME          DEFAULT NULL,
    created_at  TIMESTAMP           DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP           DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


CREATE TABLE categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)  NOT NULL,
    slug        VARCHAR(120)  NOT NULL UNIQUE,
    description TEXT,
    image       VARCHAR(255),
    is_active   TINYINT(1)    DEFAULT 1,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    category_id   INT            NOT NULL,
    name          VARCHAR(200)   NOT NULL,
    slug          VARCHAR(220)   NOT NULL UNIQUE,
    description   TEXT,
    price         DECIMAL(10,2)  NOT NULL,
    sale_price    DECIMAL(10,2)  DEFAULT NULL,
    unit          VARCHAR(30)    DEFAULT 'kg',   
    stock         INT            DEFAULT 0,
    image         VARCHAR(255),
    is_featured   TINYINT(1)     DEFAULT 0,
    is_active     TINYINT(1)     DEFAULT 1,
    created_at    TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);


CREATE TABLE orders (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT             NOT NULL,
    order_number    VARCHAR(20)     NOT NULL UNIQUE,
    total_amount    DECIMAL(10,2)   NOT NULL,
    status          ENUM('pending','confirmed','processing','shipped','delivered','cancelled')
                    DEFAULT 'pending',
    payment_method  ENUM('card','cod','bank_transfer') DEFAULT 'cod',
    payment_status  ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
    shipping_name   VARCHAR(100),
    shipping_phone  VARCHAR(20),
    shipping_address TEXT,
    shipping_city   VARCHAR(80),
    notes           TEXT,
    created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


CREATE TABLE order_items (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    order_id    INT             NOT NULL,
    product_id  INT             NOT NULL,
    name        VARCHAR(200)    NOT NULL,
    price       DECIMAL(10,2)   NOT NULL,
    quantity    INT             NOT NULL DEFAULT 1,
    subtotal    DECIMAL(10,2)   NOT NULL,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);


CREATE TABLE cart (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    product_id  INT NOT NULL,
    quantity    INT NOT NULL DEFAULT 1,
    added_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_product (user_id, product_id),
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);




INSERT INTO users (name, email, password, role) VALUES
('Admin Goviya', 'admin@goviya.lk',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.usd2pCx2y', 'admin');


INSERT INTO categories (name, slug, description, image) VALUES
('Vegetables',   'vegetables',   'Fresh farm vegetables',          'vegetables.jpg'),
('Fruits',       'fruits',       'Seasonal & exotic fruits',       'fruits.jpg'),
('Rice & Grains','rice-grains',  'Rice, dhal & pulses',            'grains.jpg'),
('Dairy & Eggs', 'dairy-eggs',   'Fresh dairy products and eggs',  'dairy.jpg'),
('Herbs & Spices','herbs-spices','Aromatic herbs and spices',      'herbs.jpg');


INSERT INTO products 
(category_id, name, slug, description, price, sale_price, unit, stock, image, is_featured, is_active)
VALUES
(1, 'Tomatoes', 'tomatoes', 'Ripe red tomatoes, locally grown', 180.00, 150.00, 'kg', 47, 'prod_1778402216_47cedec6.jpg', 1, 1),
(1, 'Brinjal', 'brinjal', 'Fresh purple brinjal', 120.00, NULL, 'kg', 30, 'prod_1778402377_d00c133c.jpg', 0, 1),
(1, 'Carrot', 'carrot', 'Crunchy orange carrots', 200.00, 180.00, 'kg', 37, 'prod_1778402446_503d9ef7.jpg', 1, 1),
(1, 'Leeks', 'leeks', 'Fresh green leeks', 90.00, NULL, 'bunch', 20, 'prod_1778402499_f45abb95.jpg', 0, 1),
(2, 'Banana', 'banana', 'Sweet Ambul banana bunch', 250.00, NULL, 'bunch', 57, 'prod_1778402822_e73f14e0.jpg', 1, 1),
(2, 'Papaya', 'papaya', 'Ripe yellow papaya', 150.00, 120.00, 'piece', 25, 'prod_1778402835_d1efe4be.jpg', 0, 1),
(2, 'Mango', 'mango', 'Sweet Karuthakolomban mango', 400.00, NULL, 'kg', 15, 'prod_1778402849_a43b3add.jpg', 1, 1),
(3, 'White Rice', 'white-rice', 'Premium Samba rice 5kg bag', 1200.00, NULL, '5kg', 100, 'prod_1778403117_ea640f3c.jpg', 0, 1),
(3, 'Red Dhal', 'red-dhal', 'Organic red lentils', 320.00, 280.00, 'kg', 80, 'prod_1778403148_fb58cf18.jpg', 0, 1),
(4, 'Eggs (Tray)', 'eggs-tray', '30-piece farm fresh eggs', 900.00, NULL, 'tray', 50, 'prod_1778403338_4f40a2d0.jpg', 1, 1),
(4, 'Fresh Milk', 'fresh-milk', 'Farm fresh cow milk 1L', 180.00, NULL, 'litre', 40, 'prod_1778403356_efc338b9.jpg', 0, 1),
(5, 'Curry Leaves', 'curry-leaves', 'Fresh curry leaves bundle', 50.00, NULL, 'bunch', 30, 'prod_1778403367_ce4b33ad.jpg', 0, 1),
(5, 'Green Chilli', 'green-chilli', 'Hot fresh green chillies', 160.00, 140.00, 'kg', 25, 'prod_1778403381_2d393082.jpg', 0, 1);
