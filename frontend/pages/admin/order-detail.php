<?php
// ✅ BẬT OUTPUT BUFFERING NGAY ĐẦU FILE
ob_start();

$pageTitle = 'Chi tiết đơn hàng';
require_once 'includes/header.php';
require_once '../../../backend/config/database.php';

$database = new Database();
$db = $database->getConnection();

$orderId = (int)($_GET['id'] ?? 0);
if ($orderId <= 0) {
    Session::setFlash('error', 'Đơn hàng không tồn tại');
    ob_end_clean(); // ✅ Xóa buffer trước khi redirect
    header('Location: orders.php');
    exit();
}

// Lấy thông tin đơn hàng
$stmt = $db->prepare("SELECT * FROM orders WHERE id = :id");
$stmt->execute([':id' => $orderId]);
$order = $stmt->fetch();

if (!$order) {
    Session::setFlash('error', 'Đơn hàng không tồn tại');
    ob_end_clean(); // ✅ Xóa buffer trước khi redirect
    header('Location: orders.php');
    exit();
}

// Lấy chi tiết sản phẩm
$stmtItems = $db->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
$stmtItems->execute([':order_id' => $orderId]);
$items = $stmtItems->fetchAll();

// ✅ ĐỊNH NGHĨA CÁC TRẠNG THÁI HỢP LỆ CÓ THỂ CHUYỂN
$allowedTransitions = [
    'pending' => ['confirmed', 'cancelled'],
    'confirmed' => ['processing', 'cancelled'],
    'processing' => ['shipping', 'cancelled'],
    'shipping' => ['completed', 'cancelled'],
    'completed' => [], // Không được thay đổi
    'cancelled' => []  // Không được thay đổi
];

// Cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = trim($_POST['status']);
    $currentStatus = $order['status'];
    
    // ✅ KIỂM TRA XEM TRẠNG THÁI MỚI CÓ HỢP LỆ KHÔNG
    if (!in_array($newStatus, $allowedTransitions[$currentStatus])) {
        Session::setFlash('error', 'Không thể chuyển từ trạng thái "' . $currentStatus . '" sang "' . $newStatus . '"!');
    } else {
        try {
            $db->beginTransaction();
            
            // ✅ NẾU HỦY ĐƠN → HOÀN LẠI TỒN KHO
            if ($newStatus === 'cancelled') {
                $itemsStmt = $db->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = :order_id");
                $itemsStmt->execute([':order_id' => $orderId]);
                $orderItems = $itemsStmt->fetchAll();
                
                foreach ($orderItems as $item) {
                    $restoreStockStmt = $db->prepare("UPDATE products SET stock = stock + :quantity WHERE id = :product_id");
                    $restoreStockStmt->execute([
                        ':quantity' => $item['quantity'],
                        ':product_id' => $item['product_id']
                    ]);
                }
            }
            
            // Cập nhật trạng thái đơn hàng
            $stmt = $db->prepare("UPDATE orders SET status = :status WHERE id = :id");
            $stmt->execute([':status' => $newStatus, ':id' => $orderId]);
            
            $db->commit();
            
            Session::setFlash('success', 'Cập nhật trạng thái đơn hàng thành công!');
        } catch (Exception $e) {
            $db->rollBack();
            Session::setFlash('error', 'Có lỗi xảy ra, vui lòng thử lại!');
        }
    }
    
    ob_end_clean(); // ✅ Xóa buffer trước khi redirect
    header('Location: order-detail.php?id=' . $orderId);
    exit();
}

// Lấy danh sách trạng thái có thể chuyển
$availableStatuses = $allowedTransitions[$order['status']];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Đơn hàng #<?= e($order['order_number']) ?></h2>
    <a href="orders.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Order Items -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Sản phẩm đã đặt</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Đơn giá</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= e($item['product_name']) ?></td>
                                <td><?= formatCurrency($item['price']) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td><?= formatCurrency($item['subtotal']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Tổng cộng:</strong></td>
                                <td><strong><?= formatCurrency($order['total_amount']) ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Customer Info -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Thông tin khách hàng</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="150">Họ tên:</th>
                        <td><?= e($order['customer_name']) ?></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><?= e($order['customer_email']) ?></td>
                    </tr>
                    <tr>
                        <th>Điện thoại:</th>
                        <td><?= e($order['customer_phone']) ?></td>
                    </tr>
                    <tr>
                        <th>Địa chỉ:</th>
                        <td><?= e($order['customer_address']) ?></td>
                    </tr>
                    <tr>
                        <th>Ghi chú:</th>
                        <td><?= e($order['notes']) ?: '(Không có)' ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Order Status -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Trạng thái đơn hàng</h5>
            </div>
            <div class="card-body">
                <?php
                $statusColors = [
                    'pending' => 'warning',
                    'confirmed' => 'info',
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
                
                <div class="text-center mb-3">
                    <span class="badge bg-<?= $statusColors[$order['status']] ?> fs-6 px-4 py-2">
                        <?= $statusTexts[$order['status']] ?>
                    </span>
                </div>
                
                <?php if (empty($availableStatuses)): ?>
                    <!-- Không thể thay đổi trạng thái -->
                    <div class="alert alert-secondary">
                        <i class="fas fa-lock me-2"></i>
                        <?php if ($order['status'] === 'completed'): ?>
                            Đơn hàng đã hoàn thành, không thể thay đổi trạng thái.
                        <?php elseif ($order['status'] === 'cancelled'): ?>
                            Đơn hàng đã bị hủy, không thể thay đổi trạng thái.
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Form cập nhật trạng thái -->
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Cập nhật trạng thái:</label>
                            <select name="status" class="form-select" required>
                                <option value="">-- Chọn trạng thái --</option>
                                <?php foreach ($availableStatuses as $status): ?>
                                    <option value="<?= $status ?>"><?= $statusTexts[$status] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="update_status" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Cập nhật
                        </button>
                    </form>
                    
                    <!-- Hướng dẫn chuyển trạng thái -->
                    <div class="alert alert-info mt-3 small">
                        <strong>Có thể chuyển sang:</strong><br>
                        <?php foreach ($availableStatuses as $status): ?>
                            <span class="badge bg-<?= $statusColors[$status] ?> me-1"><?= $statusTexts[$status] ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Order Info -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Thông tin đơn hàng</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <th>Mã đơn:</th>
                        <td><?= e($order['order_number']) ?></td>
                    </tr>
                    <tr>
                        <th>Ngày đặt:</th>
                        <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                    </tr>
                    <tr>
                        <th>Thanh toán:</th>
                        <td><?= $order['payment_method'] === 'cod' ? 'COD' : 'Chuyển khoản' ?></td>
                    </tr>
                    <tr>
                        <th>Tổng tiền:</th>
                        <td><strong class="text-danger"><?= formatCurrency($order['total_amount']) ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>