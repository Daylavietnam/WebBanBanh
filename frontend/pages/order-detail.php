<?php
require_once '../../backend/config/config.php';
require_once '../../backend/config/database.php';
require_once '../../backend/src/helpers/Session.php';

$pageTitle = 'Chi tiết đơn hàng';

// Yêu cầu đăng nhập
if (!Session::isLoggedIn()) {
    Session::setFlash('error', 'Vui lòng đăng nhập để xem đơn hàng');
    redirect('frontend/pages/auth/login.php');
}

$orderId = (int)($_GET['id'] ?? 0);
$userId = Session::getUserId();

if ($orderId <= 0) {
    Session::setFlash('error', 'Đơn hàng không tồn tại');
    redirect('frontend/pages/account.php');
}

$database = new Database();
$db = $database->getConnection();

// ✅ XỬ LÝ HỦY ĐƠN HÀNG + HOÀN LẠI TỒN KHO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    // Lấy thông tin đơn hàng để kiểm tra trạng thái
    $stmt = $db->prepare("SELECT status FROM orders WHERE id = :id AND user_id = :user_id");
    $stmt->execute([':id' => $orderId, ':user_id' => $userId]);
    $currentOrder = $stmt->fetch();
    
    if ($currentOrder) {
        // Chỉ cho phép hủy đơn có trạng thái pending hoặc confirmed
        if (in_array($currentOrder['status'], ['pending', 'confirmed'])) {
            try {
                // Bắt đầu transaction
                $db->beginTransaction();
                
                // 1. Cập nhật trạng thái đơn hàng thành cancelled
                $updateStmt = $db->prepare("UPDATE orders SET status = 'cancelled' WHERE id = :id AND user_id = :user_id");
                $updateStmt->execute([':id' => $orderId, ':user_id' => $userId]);
                
                // 2. ✅ HOÀN LẠI TỒN KHO
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
                
                // Commit transaction
                $db->commit();
                
                Session::setFlash('success', 'Hủy đơn hàng thành công');
            } catch (Exception $e) {
                // Rollback nếu có lỗi
                $db->rollBack();
                Session::setFlash('error', 'Có lỗi xảy ra, vui lòng thử lại!');
            }
        } else {
            Session::setFlash('error', 'Không thể hủy đơn hàng đã được xử lý!');
        }
    }
    
    header('Location: order-detail.php?id=' . $orderId);
    exit();
}

// Lấy thông tin đơn hàng - CHỈ CỦA USER HIỆN TẠI
$stmt = $db->prepare("SELECT * FROM orders WHERE id = :id AND user_id = :user_id");
$stmt->execute([':id' => $orderId, ':user_id' => $userId]);
$order = $stmt->fetch();

if (!$order) {
    Session::setFlash('error', 'Đơn hàng không tồn tại hoặc bạn không có quyền xem');
    redirect('frontend/pages/account.php');
}

// Lấy chi tiết sản phẩm
$stmtItems = $db->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
$stmtItems->execute([':order_id' => $orderId]);
$items = $stmtItems->fetchAll();

// Kiểm tra xem có thể hủy đơn không
$canCancel = in_array($order['status'], ['pending', 'confirmed']);

require_once '../components/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('frontend/pages/index.php') ?>">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?= url('frontend/pages/account.php') ?>">Tài khoản</a></li>
            <li class="breadcrumb-item active">Đơn hàng #<?= e($order['order_number']) ?></li>
        </ol>
    </div>
</nav>

<!-- Order Detail -->
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Chi tiết đơn hàng #<?= e($order['order_number']) ?></h2>
        <a href="<?= url('frontend/pages/account.php') ?>" class="btn btn-outline-brown">
            <i class="fas fa-arrow-left me-2"></i>Quay lại
        </a>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <!-- Order Items -->
            <div class="card mb-4">
                <div class="card-header bg-brown text-white">
                    <h5 class="mb-0"><i class="fas fa-shopping-bag me-2"></i>Sản phẩm đã đặt</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
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
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($item['product_image'])): ?>
                                                <img src="<?= url('storage/uploads/products/' . $item['product_image']) ?>" 
                                                     alt="<?= e($item['product_name']) ?>" 
                                                     style="width:60px;height:60px;object-fit:cover;" 
                                                     class="me-3 rounded"
                                                     onerror="this.src='<?= url('frontend/assets/images/no-image.png') ?>'">
                                            <?php endif; ?>
                                            <div>
                                                <strong><?= e($item['product_name']) ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= formatCurrency($item['price']) ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?= $item['quantity'] ?></span>
                                    </td>
                                    <td><strong><?= formatCurrency($item['subtotal']) ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="3" class="text-end"><strong>Tổng cộng:</strong></td>
                                    <td><strong class="text-danger fs-5"><?= formatCurrency($order['total_amount']) ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Order Status -->
            <div class="card mb-4">
                <div class="card-header bg-brown text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Trạng thái đơn hàng</h5>
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
                    <div class="text-center mb-4">
                        <span class="badge bg-<?= $statusColors[$order['status']] ?> fs-6 px-4 py-3">
                            <i class="fas fa-check-circle me-2"></i><?= $statusTexts[$order['status']] ?>
                        </span>
                    </div>
                    
                    <!-- Nút hủy đơn hàng -->
                    <?php if ($canCancel): ?>
                        <div class="alert alert-info small mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Bạn có thể hủy đơn hàng này
                        </div>
                        <form method="POST" onsubmit="return confirm('Bạn có chắc muốn hủy đơn hàng này?');" class="mb-3">
                            <button type="submit" name="cancel_order" class="btn btn-danger w-100">
                                <i class="fas fa-times-circle me-2"></i>Hủy đơn hàng
                            </button>
                        </form>
                    <?php elseif ($order['status'] === 'cancelled'): ?>
                        <div class="alert alert-danger small mb-3">
                            <i class="fas fa-ban me-2"></i>
                            Đơn hàng đã bị hủy
                        </div>
                    <?php else: ?>
                        <div class="alert alert-secondary small mb-3">
                            <i class="fas fa-lock me-2"></i>
                            Đơn hàng đang được xử lý, không thể hủy
                        </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <th width="120"><i class="fas fa-barcode me-2"></i>Mã đơn:</th>
                            <td><strong><?= e($order['order_number']) ?></strong></td>
                        </tr>
                        <tr>
                            <th><i class="far fa-calendar me-2"></i>Ngày đặt:</th>
                            <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-credit-card me-2"></i>Thanh toán:</th>
                            <td>
                                <?php if ($order['payment_method'] === 'cod'): ?>
                                    <span class="badge bg-success">COD</span>
                                <?php else: ?>
                                    <span class="badge bg-primary">Chuyển khoản</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-money-bill-wave me-2"></i>Tổng tiền:</th>
                            <td><strong class="text-danger"><?= formatCurrency($order['total_amount']) ?></strong></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Delivery Info -->
            <div class="card">
                <div class="card-header bg-brown text-white">
                    <h5 class="mb-0"><i class="fas fa-truck me-2"></i>Thông tin giao hàng</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <th width="100"><i class="fas fa-user me-2"></i>Họ tên:</th>
                            <td><?= e($order['customer_name']) ?></td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-phone me-2"></i>Điện thoại:</th>
                            <td><a href="tel:<?= e($order['customer_phone']) ?>"><?= e($order['customer_phone']) ?></a></td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-envelope me-2"></i>Email:</th>
                            <td><?= e($order['customer_email']) ?></td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-map-marker-alt me-2"></i>Địa chỉ:</th>
                            <td><?= e($order['customer_address']) ?></td>
                        </tr>
                        <?php if (!empty($order['notes'])): ?>
                        <tr>
                            <th><i class="fas fa-comment me-2"></i>Ghi chú:</th>
                            <td><?= e($order['notes']) ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-brown {
    background-color: #6B4423;
}
.text-brown {
    color: #6B4423;
}
</style>

<?php require_once '../components/footer.php'; ?>