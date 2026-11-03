<?php
require_once 'backend/config/database.php';

echo "<h2>Kiểm tra kết nối Database</h2>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<p style='color: green;'>✓ Kết nối database thành công!</p>";
    
    // Test query
    $query = "SELECT COUNT(*) as total FROM categories";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch();
    
    echo "<p>Số danh mục trong database: " . $result['total'] . "</p>";
    
    // Show all tables
    $query = "SHOW TABLES";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Các bảng trong database:</h3>";
    echo "<ul>";
    foreach($tables as $table) {
        echo "<li>" . $table . "</li>";
    }
    echo "</ul>";
    
} catch(Exception $e) {
    echo "<p style='color: red;'>✗ Lỗi kết nối: " . $e->getMessage() . "</p>";
}
?>
