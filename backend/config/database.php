<?php
/**
 * Database Configuration
 * Cấu hình kết nối database MySQL
 */

class Database {
    private $host = "127.0.0.1";
    private $db_name = "bakery_shop";
    private $username = "root";
    private $password = "1234";  // Nếu bạn đặt password cho MySQL thì điền vào đây
    private $charset = "utf8mb4";
    public $conn;

    /**
     * Kết nối database
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
            die();
        }

        return $this->conn;
    }

    /**
     * Đóng kết nối
     */
    public function closeConnection() {
        $this->conn = null;
    }
}
?>
