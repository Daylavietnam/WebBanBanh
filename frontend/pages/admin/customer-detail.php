<?php
// ✅ Bật output buffering ngay đầu file để hỗ trợ redirect không bị lỗi headers already sent
ob_start();

require_once '../../../backend/config/database.php';
require_once '../../../backend/src/helpers/Session.php';

$database = new Database();
$db = $database->getConnection();

$customerId = (int)($_GET['id'] ?? 0);
if ($customerId <= 0) {
    Session::setFlash('error', 'Khách hàng không tồn tại');
    ob_end_clean();
    header('Location: customers.php');
    exit();
}

// Lấy thông tin khách hàng
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id AND role = 'customer'");
$stmt->execute([':id' => $customerId]);
$customer = $stmt->fetch();

if (!$customer) {
    Session::setFlash('error', 'Khách hàng không tồn tại');
    ob_end_clean();
    header('Location: customers.php');
    exit();
}

// ✅ Xử lý cập nhật trạng thái (Khóa / Mở khóa)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $status = (int)$_POST['status'];
    $stmt = $db->prepare("UPDATE users SET status = :status WHERE id = :id");
    $stmt->execute([':status' => $status, ':id' => $customerId]);
    
    $msg = ($status === 1) ? 'Mở khóa tài khoản khách hàng thành công!' : 'Đã khóa tài khoản khách hàng thành công!';
    Session::setFlash('success', $msg);
    
    ob_end_clean(); // Xóa sạch buffer trước khi gửi header redirect
    header('Location: customer-detail.php?id=' . $customerId);
    exit();
}

// Lấy thống kê đơn hàng
$statsStmt = $db->prepare("
    SELECT 
        COUNT(*) as total_orders,
        SUM(total_amount) as total_spent,
        MAX(created_at) as last_order_date
    FROM orders 
    WHERE user_id = :user_id
");
$statsStmt->execute([':user_id' => $customerId]);
$stats = $statsStmt->fetch();

// Lấy danh sách đơn hàng
$ordersStmt = $db->prepare("
    SELECT * FROM orders 
    WHERE user_id = :user_id 
    ORDER BY created_at DESC 
    LIMIT 20
");
$ordersStmt->execute([':user_id' => $customerId]);
$orders = $ordersStmt->fetchAll();

$pageTitle = 'Chi tiết khách hàng - ' . $customer['name'];
require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Chi tiết khách hàng</h2>
    <a href="customers.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Quay lại
    </a>
</div>

<div class="row">
    <div class="col-md-4">
        <!-- Customer Info Card -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0 fw-bold"><i class="fas fa-user me-2"></i>Thông tin khách hàng</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="avatar-circle bg-primary text-white d-inline-flex align-items-center justify-content-center shadow-sm" 
                         style="width: 80px; height: 80px; border-radius: 50%; font-size: 2rem;">
                        <?= strtoupper(substr($customer['name'], 0, 1)) ?>
                    </div>
                    <h5 class="mt-2 mb-1 fw-bold"><?= e($customer['name']) ?></h5>
                    <div class="mt-1">
                        <?php if ($customer['status'] == 1): ?>
                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Đang hoạt động</span>
                        <?php else: ?>
                            <span class="badge bg-danger"><i class="fas fa-ban me-1"></i>Đã bị khóa</span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($customer['status'] == 0): ?>
                    <div class="alert alert-danger py-2 small mb-3">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <strong>Tài khoản đang bị khóa:</strong> Khách hàng không thể đăng nhập hoặc đặt hàng trên hệ thống.
                    </div>
                <?php endif; ?>
                
                <table class="table table-borderless table-sm">
                    <tr>
                        <th style="width: 35%;">Họ tên:</th>
                        <td><?= e($customer['name']) ?></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><?= e($customer['email']) ?></td>
                    </tr>
                    <tr>
                        <th>Điện thoại:</th>
                        <td><?= e($customer['phone'] ?: '-') ?></td>
                    </tr>
                    <tr>
                        <th>Địa chỉ:</th>
                        <td><?= e($customer['address'] ?: '-') ?></td>
                    </tr>
                    <tr>
                        <th>Ngày đăng ký:</th>
                        <td><?= date('d/m/Y H:i', strtotime($customer['created_at'])) ?></td>
                    </tr>
                </table>
                
                <hr>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Trạng thái tài khoản:</label>
                        <select name="status" class="form-select <?= $customer['status'] ? 'border-success' : 'border-danger' ?>">
                            <option value="1" <?= $customer['status'] ? 'selected' : '' ?>>Hoạt động (Cho phép đăng nhập)</option>
                            <option value="0" <?= !$customer['status'] ? 'selected' : '' ?>>Khóa (Chặn đăng nhập)</option>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn <?= $customer['status'] ? 'btn-danger' : 'btn-success' ?> w-100" onclick="return confirm('Bạn có chắc chắn muốn thay đổi trạng thái tài khoản này không?');">
                        <i class="fas fa-save me-1"></i> <?= $customer['status'] ? 'Cập nhật trạng thái' : 'Mở khóa tài khoản' ?>
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Statistics Card -->
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0 fw-bold"><i class="fas fa-chart-line me-2"></i>Thống kê</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Tổng số đơn hàng</small>
                    <h4 class="mb-0 fw-bold"><?= $stats['total_orders'] ?? 0 ?> đơn</h4>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Tổng chi tiêu</small>
                    <h4 class="mb-0 text-success fw-bold"><?= formatCurrency($stats['total_spent'] ?? 0) ?></h4>
                </div>
                <?php if ($stats['last_order_date']): ?>
                <div>
                    <small class="text-muted d-block">Đơn hàng gần nhất</small>
                    <h6 class="mb-0"><?= date('d/m/Y H:i', strtotime($stats['last_order_date'])) ?></h6>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <!-- Orders History -->
        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i>Lịch sử đơn hàng</h5>
                <span class="badge bg-secondary"><?= count($orders) ?> đơn</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($orders)): ?>
                    <p class="text-muted text-center py-4 mb-0">Khách hàng chưa có đơn hàng nào.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Mã đơn</th>
                                    <th>Ngày đặt</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th class="text-center pe-3">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td class="ps-3"><strong>#<?= e($order['order_number']) ?></strong></td>
                                    <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                    <td class="text-danger fw-bold"><?= formatCurrency($order['total_amount']) ?></td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'pending' => 'warning text-dark',
                                            'confirmed' => 'info text-dark',
                                            'processing' => 'primary',
                                            'shipping' => 'secondary',
                                            'completed' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                        $statusTexts = [
                                            'pending' => 'Chờ xác nhận',
                                            'confirmed' => 'Đã xác nhận',
                                            'processing' => 'Đang xử lý',
                                            'shipping' => 'Đang giao',
                                            'completed' => 'Hoàn thành',
                                            'cancelled' => 'Đã hủy'
                                        ];
                                        ?>
                                        <span class="badge bg-<?= $statusColors[$order['status']] ?? 'secondary' ?>">
                                            <?= $statusTexts[$order['status']] ?? ucfirst($order['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center pe-3">
                                        <a href="order-detail.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary" title="Xem chi tiết đơn hàng">
                                            <i class="fas fa-eye me-1"></i>Xem
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
