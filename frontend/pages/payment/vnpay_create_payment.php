<?php
session_start();

// Load cấu hình và kết nối database
require_once __DIR__ . '/../../../backend/config/config.php';
require_once __DIR__ . '/../../../backend/config/database.php';
require_once __DIR__ . '/../../../backend/src/helpers/Session.php';

// Bắt buộc đăng nhập để thanh toán
if (!Session::isLoggedIn()) {
    Session::setFlash('warning', 'Vui lòng đăng nhập để tiếp tục thanh toán.');
    redirect('frontend/pages/auth/login.php');
}

$database = new Database();
$db = $database->getConnection();

$orderId = $_GET['order_id'] ?? $_POST['order_id'] ?? null;
if (!$orderId) {
    Session::setFlash('error', 'Không tìm thấy đơn hàng cần thanh toán');
    redirect('frontend/pages/cart.php');
}

$userId = Session::getUserId();
$stmt = $db->prepare("SELECT * FROM orders WHERE id = :id AND user_id = :user_id LIMIT 1");
$stmt->execute([':id' => $orderId, ':user_id' => $userId]);
$order = $stmt->fetch();

if (!$order) {
    Session::setFlash('error', 'Đơn hàng không tồn tại hoặc bạn không có quyền thanh toán đơn hàng này');
    redirect('frontend/pages/cart.php');
}

// Xử lý POST mô phỏng thanh toán
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'confirm_payment') {
        // Tạo mã giao dịch giả lập
        $fakeTxn = 'VNP' . date('YmdHis') . rand(100, 999);
        
        $updateStmt = $db->prepare("UPDATE orders SET payment_status = 'paid', status = 'processing', transaction_id = :txn, paid_at = NOW() WHERE id = :id");
        $updateStmt->execute([
            ':txn' => $fakeTxn,
            ':id' => $orderId
        ]);

        // Xóa giỏ hàng
        Session::clearCart();
        Session::setFlash('success', 'Thanh toán thành công đơn hàng #' . $order['order_number'] . ' qua VNPAY-QR!');
        
        header('Location: ' . url('frontend/pages/thankyou.php?order_id=' . $orderId));
        exit();
    }
    
    if ($action === 'cancel_payment') {
        Session::setFlash('warning', 'Bạn đã hủy quá trình thanh toán qua VNPAY.');
        header('Location: ' . url('frontend/pages/checkout.php'));
        exit();
    }
}

// Lấy danh sách sản phẩm trong đơn
$itemsStmt = $db->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
$itemsStmt->execute([':order_id' => $orderId]);
$orderItems = $itemsStmt->fetchAll();

// Chuỗi nội dung QR mẫu: là văn bản thuần túy không chứa thông tin tài khoản ngân hàng
// Khi dùng bất kỳ ứng dụng ngân hàng nào (Vietcombank, MB, Techcombank, MoMo...) quét mã này
// Ứng dụng sẽ thông báo "Mã QR không hợp lệ", TUYỆT ĐỐI KHÔNG THỂ CHUYỂN KHOẢN ĐƯỢC
$qrPayload = "VNPAY_DEMO_KHONG_THE_CHUYEN_TIEN_DON_HANG_#" . $order['order_number'] . "_SO_TIEN_" . ((int)$order['total_amount']) . "VND";
$qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=260x260&margin=8&data=" . urlencode($qrPayload);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán VNPAY-QR (Bản Thử Nghiệm) - <?= e($order['order_number']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --vnpay-blue: #005baa;
            --vnpay-red: #ed1c24;
            --bg-light: #f4f6f9;
        }
        body {
            background-color: var(--bg-light);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #333;
        }
        .vnpay-header {
            background: linear-gradient(135deg, var(--vnpay-blue) 0%, #003c73 100%);
            color: white;
            padding: 16px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        }
        .vnpay-badge-demo {
            background: #ffc107;
            color: #000;
            font-weight: 700;
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .payment-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        .qr-wrapper {
            position: relative;
            display: inline-block;
            padding: 12px;
            background: #ffffff;
            border: 2px dashed #005baa;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,91,170,0.1);
        }
        .qr-wrapper img {
            display: block;
            width: 240px;
            height: 240px;
            border-radius: 8px;
        }
        .scan-line {
            position: absolute;
            top: 12px;
            left: 12px;
            right: 12px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--vnpay-red), transparent);
            box-shadow: 0 0 8px var(--vnpay-red);
            animation: scan 2.5s ease-in-out infinite;
        }
        @keyframes scan {
            0% { top: 12px; opacity: 0; }
            30% { opacity: 1; }
            70% { opacity: 1; }
            100% { top: 240px; opacity: 0; }
        }
        .timer-box {
            font-size: 20px;
            font-weight: 700;
            color: var(--vnpay-red);
            background: #fff1f0;
            border: 1px solid #ffccc7;
            padding: 6px 16px;
            border-radius: 8px;
            display: inline-block;
        }
        .safe-alert {
            background: #fffbe6;
            border: 1px solid #ffe58f;
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 13px;
            color: #873800;
            line-height: 1.5;
        }
        .bank-icons {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            justify-content: center;
            opacity: 0.85;
        }
        .bank-pill {
            font-size: 11px;
            background: #f1f5f9;
            color: #475569;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <!-- Header VNPAY -->
    <header class="vnpay-header mb-4">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-white px-2 py-1 rounded">
                    <span style="font-weight: 900; color: var(--vnpay-blue); font-size: 20px;">VN</span><span style="font-weight: 900; color: var(--vnpay-red); font-size: 20px;">PAY</span>
                    <span style="font-weight: 700; color: var(--vnpay-blue); font-size: 14px;"><sup>QR</sup></span>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold">Cổng Thanh Toán VNPAY-QR</h5>
                    <small class="text-white-50">Đơn vị chấp nhận: <strong>Nguyễn Sơn Bakery</strong></small>
                </div>
            </div>
            <div>
                <span class="vnpay-badge-demo">
                    <i class="fas fa-flask me-1"></i> Môi trường Test / Demo
                </span>
            </div>
        </div>
    </header>

    <div class="container py-2 pb-5">
        <div class="row g-4 justify-content-center">
            
            <!-- Cột trái: Thông tin đơn hàng -->
            <div class="col-lg-5 col-md-6">
                <div class="payment-card p-4 h-100">
                    <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary">
                        <i class="fas fa-receipt me-2"></i>Thông tin đơn hàng
                    </h5>
                    
                    <div class="mb-3">
                        <div class="text-muted small">Mã đơn hàng:</div>
                        <div class="fw-bold text-dark fs-5">#<?= e($order['order_number']) ?></div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="text-muted small">Khách hàng nhận:</div>
                        <div class="fw-semibold"><?= e($order['customer_name']) ?> - <?= e($order['customer_phone']) ?></div>
                        <div class="small text-muted"><?= e($order['customer_address']) ?></div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small mb-2">Chi tiết sản phẩm:</div>
                        <div class="list-group list-group-flush border-top border-bottom" style="max-height: 220px; overflow-y: auto;">
                            <?php foreach ($orderItems as $item): ?>
                                <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($item['product_image'])): ?>
                                            <img src="<?= url('storage/uploads/products/' . $item['product_image']) ?>" 
                                                 class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;" alt="">
                                        <?php endif; ?>
                                        <div>
                                            <div class="small fw-bold"><?= e($item['product_name']) ?></div>
                                            <div class="text-muted" style="font-size: 12px;">Số lượng: x<?= $item['quantity'] ?></div>
                                        </div>
                                    </div>
                                    <div class="small fw-semibold"><?= formatCurrency($item['subtotal']) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded mt-3">
                        <span class="fw-bold">Tổng thanh toán:</span>
                        <span class="fs-4 fw-bold text-danger"><?= formatCurrency($order['total_amount']) ?></span>
                    </div>

                    <div class="mt-4 pt-2 border-top">
                        <small class="text-muted d-block mb-1"><i class="fas fa-shield-alt text-success me-1"></i> Giao dịch được bảo vệ và mã hóa SSL</small>
                        <small class="text-muted d-block"><i class="fas fa-headset text-info me-1"></i> Hỗ trợ: 02438222228 (Nguyễn Sơn Bakery)</small>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Quét mã QR thanh toán -->
            <div class="col-lg-6 col-md-6">
                <div class="payment-card p-4 text-center">
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted small">Thời gian còn lại:</span>
                        <div class="timer-box" id="timer">15:00</div>
                    </div>

                    <h5 class="fw-bold mb-1">Quét mã VNPAY-QR để thanh toán</h5>
                    <p class="text-muted small mb-3">Mở ứng dụng ngân hàng hoặc ví VNPAY để quét mã bên dưới</p>

                    <!-- Khung mã QR mẫu -->
                    <div class="qr-wrapper mb-3">
                        <div class="scan-line"></div>
                        <img src="<?= $qrApiUrl ?>" 
                             alt="QR Code VNPAY Demo" 
                             id="qrImage"
                             onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'240\' height=\'240\' viewBox=\'0 0 240 240\'><rect width=\'240\' height=\'240\' fill=\'%23f8fafc\'/><rect x=\'20\' y=\'20\' width=\'60\' height=\'60\' fill=\'%23005baa\'/><rect x=\'160\' y=\'20\' width=\'60\' height=\'60\' fill=\'%23005baa\'/><rect x=\'20\' y=\'160\' width=\'60\' height=\'60\' fill=\'%23005baa\'/><text x=\'120\' y=\'110\' font-size=\'14\' font-weight=\'bold\' fill=\'%23ed1c24\' text-anchor=\'middle\'>DEMO QR CODE</text><text x=\'120\' y=\'135\' font-size=\'11\' fill=\'%2364748b\' text-anchor=\'middle\'>KHÔNG CHUYỂN TIỀN</text></svg>';">
                    </div>

                    <!-- Cảnh báo mã QR mẫu an toàn -->
                    <div class="safe-alert text-start mb-3">
                        <div class="fw-bold mb-1">
                            <i class="fas fa-exclamation-triangle text-warning me-1"></i> 
                            MÃ QR MẪU THỬ NGHIỆM — KHÔNG THỂ CHUYỂN KHOẢN:
                        </div>
                        Mã QR này được sinh cho mục đích đồ án / kiểm thử. Khi quét bằng bất kỳ App ngân hàng nào (Vietcombank, MB, Techcombank, MoMo...), ứng dụng sẽ thông báo <em>"Mã QR không hợp lệ"</em>. <strong>Tuyệt đối an toàn, không bị trừ tiền thật!</strong>
                    </div>

                    <!-- Danh sách ngân hàng hỗ trợ mô phỏng -->
                    <div class="mb-4">
                        <small class="text-muted d-block mb-2" style="font-size: 11px;">Hỗ trợ thanh toán qua 40+ ngân hàng & ví điện tử:</small>
                        <div class="bank-icons">
                            <span class="bank-pill">Vietcombank</span>
                            <span class="bank-pill">MB Bank</span>
                            <span class="bank-pill">Techcombank</span>
                            <span class="bank-pill">BIDV</span>
                            <span class="bank-pill">VietinBank</span>
                            <span class="bank-pill">Agribank</span>
                            <span class="bank-pill">VPBank</span>
                            <span class="bank-pill">Ví VNPAY</span>
                        </div>
                    </div>

                    <!-- Form mô phỏng xác nhận / hủy thanh toán -->
                    <form method="POST" action="">
                        <input type="hidden" name="order_id" value="<?= $orderId ?>">
                        
                        <div class="d-grid gap-2">
                            <button type="submit" name="action" value="confirm_payment" class="btn btn-success py-2 fw-bold fs-6">
                                <i class="fas fa-check-circle me-2"></i> Xác nhận đã thanh toán (Mô phỏng)
                            </button>
                            
                            <button type="submit" name="action" value="cancel_payment" class="btn btn-outline-secondary py-2" onclick="return confirm('Bạn có chắc muốn hủy thanh toán đơn hàng này?')">
                                <i class="fas fa-times me-1"></i> Hủy giao dịch & Quay lại
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>

    <!-- Script đếm ngược thời gian -->
    <script>
        let duration = 15 * 60; // 15 phút
        const timerDisplay = document.getElementById('timer');
        
        const countdown = setInterval(function () {
            let minutes = parseInt(duration / 60, 10);
            let seconds = parseInt(duration % 60, 10);

            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;

            timerDisplay.textContent = minutes + ":" + seconds;

            if (--duration < 0) {
                clearInterval(countdown);
                alert("Mã QR đã hết hạn hiệu lực. Bạn sẽ được chuyển về giỏ hàng!");
                window.location.href = "<?= url('frontend/pages/cart.php') ?>";
            }
        }, 1000);
    </script>

</body>
</html>