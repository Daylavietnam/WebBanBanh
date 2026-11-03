<?php
$pageTitle = 'Đặt hàng thành công';
require_once '../components/header.php';
require_once __DIR__ . '/../../backend/config/database.php';

$orderId = $_GET['order_id'] ?? null;
$order = null;

if ($orderId) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $orderId]);
        $order = $stmt->fetch();
    } catch (Exception $e) {
        // Fallback im lặng nếu lỗi DB
    }
}
?>
<div class="container py-5 text-center">
    <div class="card shadow-sm border-0 mx-auto p-4" style="max-width: 600px; border-radius: 16px;">
        <div class="mb-3">
            <i class="fas fa-check-circle text-success" style="font-size: 64px;"></i>
        </div>
        <h2 class="fw-bold text-success mb-2">Đặt hàng thành công!</h2>
        
        <?php if ($order): ?>
            <div class="alert alert-light border my-3 text-start">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Mã đơn hàng:</span>
                    <strong>#<?= e($order['order_number']) ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Phương thức thanh toán:</span>
                    <span>
                        <?php if ($order['payment_method'] === 'vnpay'): ?>
                            <span class="badge bg-primary">VNPAY-QR (Demo)</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">COD (Tiền mặt khi nhận)</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Trạng thái thanh toán:</span>
                    <span>
                        <?php if ($order['payment_status'] === 'paid'): ?>
                            <span class="badge bg-success">Đã thanh toán</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Chờ thanh toán</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="d-flex justify-content-between mt-2 pt-2 border-top">
                    <span class="fw-bold">Tổng thanh toán:</span>
                    <strong class="text-danger fs-5"><?= formatCurrency($order['total_amount']) ?></strong>
                </div>
            </div>
        <?php endif; ?>

        <p class="text-muted">Nguyễn Sơn Bakery sẽ liên hệ xác nhận và giao hàng trong thời gian sớm nhất.</p>
        
        <div class="d-flex justify-content-center gap-2 mt-3">
            <a href="<?= url('frontend/pages/index.php') ?>" class="btn btn-brown px-4">Quay về trang chủ</a>
            <a href="<?= url('frontend/pages/products/list.php') ?>" class="btn btn-outline-secondary px-4">Tiếp tục mua hàng</a>
        </div>
    </div>
</div>
<?php require_once '../components/footer.php'; ?>
