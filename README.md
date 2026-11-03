# Website Bán Bánh Trực Tuyến (Bakery Shop)

Dự án website bán bánh ngọt, bánh kem trực tuyến xây dựng bằng PHP và MySQL.

---

## 🛠️ Công nghệ sử dụng
- **Backend:** PHP (PHP 7.4+ / 8.x)
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap
- **Database:** MySQL / MariaDB (PDO)
- **Thanh toán:** Tích hợp cổng thanh toán VNPay & COD

---

## 🚀 Hướng dẫn cài đặt

1. **Cấu hình Web Server:**
   - Đặt thư mục dự án vào thư mục web server (ví dụ: `htdocs` của XAMPP hoặc `www` của Laragon).

2. **Cấu hình Cơ sở dữ liệu:**
   - Tạo database MySQL tên: `bakery_shop` (hoặc tên theo cấu hình của bạn).
   - Mở file `backend/config/database.php` và điều chỉnh thông tin kết nối nếu cần:
     ```php
     private $host = "localhost";
     private $db_name = "bakery_shop";
     private $username = "root";
     private $password = "";
     ```
   - Chạy script import database tự động bằng cách truy cập trình duyệt:
     `http://localhost/WebBanBanh/import_db.php`
     hoặc import trực tiếp file `database/bakery_shop.sql` vào phpMyAdmin.

3. **Khởi chạy ứng dụng:**
   - Truy cập vào: `http://localhost/WebBanBanh/`