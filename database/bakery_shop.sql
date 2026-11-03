-- ============================================
-- DATABASE: bakery_shop
-- Description: Database schema cho website bán bánh
-- ============================================

CREATE DATABASE IF NOT EXISTS bakery_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bakery_shop;

-- Bảng categories (Danh mục sản phẩm)
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    description TEXT,
    image VARCHAR(255),
    display_order INT DEFAULT 0,
    status TINYINT DEFAULT 1 COMMENT '1=active, 0=inactive',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng products (Sản phẩm)
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    description TEXT,
    ingredients TEXT COMMENT 'Thành phần',
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) DEFAULT NULL,
    image VARCHAR(255),
    gallery TEXT COMMENT 'JSON array of images',
    stock INT DEFAULT 0,
    is_featured TINYINT DEFAULT 0 COMMENT 'Sản phẩm nổi bật',
    is_new TINYINT DEFAULT 0 COMMENT 'Sản phẩm mới',
    is_bestseller TINYINT DEFAULT 0 COMMENT 'Sản phẩm bán chạy',
    views INT DEFAULT 0,
    status TINYINT DEFAULT 1 COMMENT '1=active, 0=inactive',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_status (status),
    INDEX idx_featured (is_featured),
    INDEX idx_new (is_new)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng users (Người dùng)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    avatar VARCHAR(255),
    role ENUM('customer', 'admin') DEFAULT 'customer',
    status TINYINT DEFAULT 1 COMMENT '1=active, 0=inactive',
    email_verified TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng orders (Đơn hàng)
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT DEFAULT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_address TEXT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cod', 'bank_transfer', 'momo', 'vnpay') DEFAULT 'cod',
    payment_status ENUM('unpaid', 'paid') DEFAULT 'unpaid',
    transaction_id VARCHAR(100) NULL COMMENT 'Mã giao dịch VNPAY',  -- ✅ THÊM DÒNG NÀY
    paid_at DATETIME NULL COMMENT 'Thời gian thanh toán',            -- ✅ THÊM DÒNG NÀY
    status ENUM('pending', 'confirmed', 'processing', 'shipping', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order_number (order_number),
    INDEX idx_status (status),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng order_items (Chi tiết đơn hàng)
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(255) NOT NULL,
    product_image VARCHAR(255),
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    INDEX idx_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng news (Tin tức/Khuyến mãi)
CREATE TABLE news (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    summary TEXT,
    content TEXT NOT NULL,
    image VARCHAR(255),
    author VARCHAR(100),
    views INT DEFAULT 0,
    status TINYINT DEFAULT 1 COMMENT '1=published, 0=draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng contacts (Liên hệ)
CREATE TABLE contacts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    status ENUM('new', 'processing', 'completed') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng settings (Cài đặt website)
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng cart (Giỏ hàng - cho user đã đăng nhập)
CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DATA SAMPLES
-- ============================================

-- Insert default admin user (password: admin123)
INSERT INTO users (name, email, password, role, status) VALUES
('Administrator', 'admin@bakery.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

-- Insert sample categories
INSERT INTO categories (name, slug, description, display_order, status) VALUES
('Bánh Ngọt', 'banh-ngot', 'Các loại bánh ngọt đa dạng', 1, 1),
('Bánh Mỳ', 'banh-my', 'Bánh mỳ tươi ngon mỗi ngày', 2, 1),
('Bánh Sinh Nhật', 'banh-sinh-nhat', 'Bánh sinh nhật theo yêu cầu', 3, 1),
('Bánh Trung Thu', 'banh-trung-thu', 'Bánh trung thu truyền thống', 4, 1);

-- Insert sample products
INSERT INTO products (category_id, name, slug, description, price, image, is_featured, is_new, status) VALUES
(1, 'Bánh Ốc Quế Kem', 'banh-oc-que-kem', 'Bánh ốc quế giòn tan với kem mát lạnh', 15000, 'banh-oc-que-kem.jpg', 1, 1, 1),
(1, 'Tiramisu Cake Piece', 'tiramisu-cake-piece', 'Tiramisu thơm ngon vị cà phê đặc trưng', 37000, 'tiramisu.jpg', 1, 0, 1),
(1, 'Coconut Mochi', 'coconut-mochi', 'Bánh mochi dừa mềm mịn', 16000, 'coconut-mochi.jpg', 0, 1, 1),
(2, 'Bánh Croissant', 'banh-croissant', 'Bánh sừng bò giòn xốp kiểu Pháp', 17000, 'croissant.jpg', 1, 0, 1),
(2, 'Floss Pork Bread', 'floss-pork-bread', 'Bánh mỳ ruốc heo thơm ngon', 13000, 'floss-pork-bread.jpg', 0, 0, 1),
(3, 'White Cheese and Caramel Cake', 'white-cheese-caramel-cake', 'Bánh kem phô mai caramel', 335000, 'white-cheese-cake.jpg', 1, 1, 1),
(3, 'Banana Cheese Cake', 'banana-cheese-cake', 'Bánh kem chuối phô mai', 370000, 'banana-cheese-cake.jpg', 1, 0, 1);

-- Insert sample settings
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'Nguyễn Sơn Bakery'),
('site_email', 'info@nguyenson.vn'),
('site_phone', '02438222228'),
('site_address', 'Số 15, hẻm 75 ngách 51, ngõ Linh Quang, phường Văn Miếu - Quốc Tử Giám, Hà Nội'),
('facebook_url', 'https://www.facebook.com/NguyenSonBakery'),
('zalo_url', '02438222228'),
('logo', 'logo.png');

-- Insert sample news
INSERT INTO news (title, slug, summary, content, author, status) VALUES
('Khai Trương Chi Nhánh Mới', 'khai-truong-chi-nhanh-moi', 'Nguyễn Sơn Bakery vừa khai trương chi nhánh mới tại Hà Nội', 
'<p>Chúng tôi rất vui mừng thông báo về việc khai trương chi nhánh mới...</p>', 'Admin', 1),
('Khuyến Mãi Tháng 10', 'khuyen-mai-thang-10', 'Giảm giá 20% cho tất cả các loại bánh sinh nhật', 
'<p>Nhân dịp khai trương, Nguyễn Sơn Bakery giảm giá 20% cho tất cả bánh sinh nhật...</p>', 'Admin', 1);
