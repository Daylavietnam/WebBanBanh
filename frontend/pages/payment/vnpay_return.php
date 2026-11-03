<?php
session_start();

// Load file Database class
require_once __DIR__ . '/../../../backend/config/database.php';

// Load VNPAY config
$vnpayConfig = require __DIR__ . '/../../../backend/config/vnpay_config.php';

// Kết nối database
$database = new Database();
$db = $database->getConnection();

$vnp_HashSecret = $vnpayConfig['vnp_HashSecret'];

$inputData = [];
foreach ($_GET as $key => $value) {
    // Lấy tất cả tham số bắt đầu bằng vnp_ (trừ vnp_SecureHash)
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

$vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
unset($inputData['vnp_SecureHash']);

// Sắp xếp lại
ksort($inputData);

$i = 0;
$hashData = "";
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
}

//  QUAN TRỌNG: Dùng sha512 để kiểm tra chữ ký
$secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

if ($secureHash === $vnp_SecureHash) {
    if ($_GET['vnp_ResponseCode'] == '00') {
        // Thanh toán thành công
        $orderNumber = $_GET['vnp_TxnRef'];
        
        // Cập nhật trạng thái đơn hàng
        $stmt = $db->prepare("UPDATE orders SET payment_status = 'completed', status = 'processing' WHERE order_number = :order_number");
        $stmt->execute([':order_number' => $orderNumber]);
        
        $_SESSION['flash_success'] = 'Thanh toán thành công!';
        
        // Chuyển hướng về trang lịch sử đơn hàng hoặc trang cảm ơn
        header('Location: http://localhost/WebBanBanh/frontend/pages/thankyou.php');
    } else {
        // Giao dịch không thành công (Do khách hủy hoặc lỗi ngân hàng)
        $_SESSION['flash_error'] = 'Giao dịch thanh toán không thành công!';
        header('Location: http://localhost/WebBanBanh/frontend/pages/checkout.php');
    }
} else {
    // Sai chữ ký (Có thể do dữ liệu bị thay đổi trong quá trình truyền)
    $_SESSION['flash_error'] = 'Chữ ký không hợp lệ!';
    header('Location: http://localhost/WebBanBanh/frontend/pages/cart.php');
}
exit();
?>