<?php
/**
 * Công cụ tự động chạy/import file database/bakery_shop.sql vào MySQL
 */
require_once __DIR__ . '/backend/config/database.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chạy file bakery_shop.sql - WebBanBanh</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f8fafc; padding: 40px; color: #1e293b; }
        .container { max-width: 650px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        h1 { margin-top: 0; color: #0f172a; font-size: 22px; }
        .success { background: #dcfce7; color: #166534; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
        .error { background: #fee2e2; color: #991b1b; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; }
        .info { background: #f1f5f9; padding: 16px; border-radius: 8px; font-size: 14px; line-height: 1.6; margin-bottom: 20px; }
        .btn { display: inline-block; background: #d97706; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: 600; margin-top: 10px; margin-right: 8px; }
        .btn:hover { background: #b45309; }
        .btn-blue { background: #2563eb; }
        .btn-blue:hover { background: #1d4ed8; }
        ul { margin: 8px 0; padding-left: 20px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Chạy file database/bakery_shop.sql</h1>
<?php
$sqlFile = __DIR__ . '/database/bakery_shop.sql';

if (!file_exists($sqlFile)) {
    echo "<div class='error'>Không tìm thấy file <code>database/bakery_shop.sql</code>!</div>";
} else {
    try {
        $database = new Database();
        $db = $database->getConnection();

        // Tắt ràng buộc khóa ngoại để drop và tạo mới sạch sẽ
        $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $tables = ['settings', 'contacts', 'news', 'cart', 'order_items', 'orders', 'users', 'products', 'categories'];
        foreach ($tables as $t) {
            $db->exec("DROP TABLE IF EXISTS `$t`");
        }
        $db->exec("SET FOREIGN_KEY_CHECKS = 1;");

        // Đọc nội dung file SQL
        $sqlContent = file_get_contents($sqlFile);
        
        // Thực thi toàn bộ lệnh SQL trong file bakery_shop.sql
        $db->exec($sqlContent);

        // Lấy thống kê
        $allTables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $productCount = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $categoryCount = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        $userCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();

        echo "<div class='success'>✓ Đã thực thi thành công file <code>bakery_shop.sql</code> vào MySQL!</div>";
        echo "<div class='info'>";
        echo "<p><strong>Kết quả sau khi chạy file:</strong></p>";
        echo "<ul>";
        echo "<li>Tổng số bảng đã tạo: <strong>" . count($allTables) . " bảng</strong> (" . implode(', ', $allTables) . ")</li>";
        echo "<li>Số danh mục bánh: <strong>$categoryCount</strong></li>";
        echo "<li>Số sản phẩm mẫu: <strong>$productCount</strong></li>";
        echo "<li>Tài khoản người dùng: <strong>$userCount</strong> (Tài khoản admin: <code>admin@bakery.com</code> / Mật khẩu: <code>admin123</code>)</li>";
        echo "</ul>";
        echo "</div>";

        echo "<a href='http://localhost/WebBanBanh/frontend/pages/products/list.php' class='btn'>Xem Trang Sản Phẩm &rarr;</a>";
        echo "<a href='http://localhost/WebBanBanh/' class='btn btn-blue'>Về Trang Chủ &rarr;</a>";

    } catch (Exception $e) {
        echo "<div class='error'>Lỗi khi thực thi SQL: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>
</div>
</body>
</html>
