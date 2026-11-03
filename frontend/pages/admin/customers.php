<?php
ob_start();
require_once '../../../backend/config/database.php';
require_once '../../../backend/src/helpers/Session.php';

$database = new Database();
$db = $database->getConnection();

// Xử lý Khóa / Mở khóa nhanh
if (isset($_GET['toggle_status'])) {
    $toggleId = (int)$_GET['toggle_status'];
    $stmtUser = $db->prepare("SELECT status FROM users WHERE id = :id AND role = 'customer'");
    $stmtUser->execute([':id' => $toggleId]);
    $u = $stmtUser->fetch();
    if ($u) {
        $newStatus = $u['status'] ? 0 : 1;
        $upd = $db->prepare("UPDATE users SET status = :status WHERE id = :id");
        $upd->execute([':status' => $newStatus, ':id' => $toggleId]);
        
        $msg = $newStatus ? 'Mở khóa tài khoản khách hàng thành công!' : 'Đã khóa tài khoản khách hàng thành công!';
        Session::setFlash('success', $msg);
    }
    ob_end_clean();
    header('Location: customers.php');
    exit();
}

$pageTitle = 'Quản lý khách hàng';
require_once 'includes/header.php';

// Search
$search = trim($_GET['search'] ?? '');

// ✅ XÂY DỰNG QUERY ĐỘNG
if (!empty($search)) {
    $queryUsers = "SELECT * FROM users 
                   WHERE role = 'customer' 
                   AND (name LIKE ? OR email LIKE ? OR phone LIKE ?) 
                   ORDER BY created_at DESC";
    $stmtUsers = $db->prepare($queryUsers);
    $searchParam = "%$search%";
    $stmtUsers->execute([$searchParam, $searchParam, $searchParam]);
} else {
    $queryUsers = "SELECT * FROM users WHERE role = 'customer' ORDER BY created_at DESC";
    $stmtUsers = $db->prepare($queryUsers);
    $stmtUsers->execute();
}

$users = $stmtUsers->fetchAll();

// ✅ Lấy thống kê đơn hàng cho từng user
$customers = [];
foreach ($users as $user) {
    // Lấy số đơn hàng và tổng chi tiêu
    $statsQuery = "SELECT 
                    COUNT(*) as total_orders,
                    COALESCE(SUM(total_amount), 0) as total_spent
                   FROM orders 
                   WHERE user_id = ?";
    $statsStmt = $db->prepare($statsQuery);
    $statsStmt->execute([$user['id']]);
    $stats = $statsStmt->fetch();
    
    // Gộp thông tin
    $customers[] = array_merge($user, [
        'total_orders' => $stats['total_orders'] ?? 0,
        'total_spent' => $stats['total_spent'] ?? 0
    ]);
}

// Thống kê tổng quan
$statsGeneral = [
    'total_customers' => $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn(),
    'active_customers' => $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer' AND status = 1")->fetchColumn(),
    'customers_with_orders' => $db->query("SELECT COUNT(DISTINCT user_id) FROM orders WHERE user_id IS NOT NULL")->fetchColumn()
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Quản lý khách hàng</h2>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Tổng khách hàng</h6>
                        <h3 class="mb-0"><?= $statsGeneral['total_customers'] ?></h3>
                    </div>
                    <div class="text-primary fs-1">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Đang hoạt động</h6>
                        <h3 class="mb-0"><?= $statsGeneral['active_customers'] ?></h3>
                    </div>
                    <div class="text-success fs-1">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Đã mua hàng</h6>
                        <h3 class="mb-0"><?= $statsGeneral['customers_with_orders'] ?></h3>
                    </div>
                    <div class="text-info fs-1">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-10">
                <input type="text" name="search" class="form-control" 
                       placeholder="Tìm theo tên, email, số điện thoại..." 
                       value="<?= e($search) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i>Tìm kiếm
                </button>
            </div>
            <?php if (!empty($search)): ?>
            <div class="col-12">
                <a href="customers.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times me-2"></i>Xóa tìm kiếm
                </a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Customers Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($customers)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <?php if (!empty($search)): ?>
                    Không tìm thấy khách hàng nào với từ khóa "<strong><?= e($search) ?></strong>".
                <?php else: ?>
                    Chưa có khách hàng nào.
                <?php endif; ?>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>Điện thoại</th>
                        <th>Số đơn hàng</th>
                        <th>Tổng chi tiêu</th>
                        <th>Ngày đăng ký</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?= $customer['id'] ?></td>
                        <td>
                            <strong><?= e($customer['name']) ?></strong>
                        </td>
                        <td><?= e($customer['email']) ?></td>
                        <td><?= e($customer['phone'] ?: '-') ?></td>
                        <td>
                            <?php if ($customer['total_orders'] > 0): ?>
                                <span class="badge bg-info"><?= $customer['total_orders'] ?> đơn</span>
                            <?php else: ?>
                                <span class="text-muted">Chưa mua</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($customer['total_spent'] > 0): ?>
                                <strong><?= formatCurrency($customer['total_spent']) ?></strong>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($customer['created_at'])) ?></td>
                        <td>
                            <?php if ($customer['status']): ?>
                                <span class="badge bg-success">Hoạt động</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Khóa</span>
                            <?php endif; ?>
                        </td>
                        <td class="table-actions">
                            <a href="customer-detail.php?id=<?= $customer['id'] ?>" class="btn btn-sm btn-primary" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if ($customer['status']): ?>
                                <a href="customers.php?toggle_status=<?= $customer['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger ms-1" 
                                   title="Khóa tài khoản"
                                   onclick="return confirm('Bạn có chắc chắn muốn KHÓA tài khoản <?= e($customer['name']) ?>?');">
                                    <i class="fas fa-ban"></i>
                                </a>
                            <?php else: ?>
                                <a href="customers.php?toggle_status=<?= $customer['id'] ?>" 
                                   class="btn btn-sm btn-outline-success ms-1" 
                                   title="Mở khóa tài khoản"
                                   onclick="return confirm('Bạn có chắc chắn muốn MỞ KHÓA tài khoản <?= e($customer['name']) ?>?');">
                                    <i class="fas fa-check-circle"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>