-- Создание базы данных
CREATE DATABASE IF NOT EXISTS exr_studio
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE exr_studio;

-- Таблица товаров
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(500),
    category VARCHAR(100),
    description TEXT,
    stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица заказов
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(50) NOT NULL,
    customer_email VARCHAR(255),
    customer_address TEXT,
    total DECIMAL(10, 2) NOT NULL,
    status ENUM('new', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Таблица товаров в заказе
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Таблица пользователей (для админки)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager') DEFAULT 'manager',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Вставка тестовых товаров
INSERT INTO products (name, price, image, category, description, stock) VALUES
('Вечернее платье New Year', 15000, '/Pic/dress1.jpg', 'Вечерние платья', 'Элегантное вечернее платье с мерцающими деталями', 10),
('Сценический костюм Concert', 12000, '/Pic/dress2.jpg', 'Сценические костюмы', 'Яркий костюм для выступлений на сцене', 15),
('Повседневное платье', 8000, '/Pic/dress3.jpg', 'Повседневная одежда', 'Стильное платье для повседневной носки', 20),
('Бизнес-костюм', 18000, '/Pic/suit1.jpg', 'Офисный стиль', 'Классический костюм для офиса и деловых встреч', 5),
('Свадебное платье', 35000, '/Pic/wedding1.jpg', 'Свадебная коллекция', 'Нежное свадебное платье с кружевом', 3);

-- Вставка администратора (пароль: admin123)
INSERT INTO users (username, password, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');