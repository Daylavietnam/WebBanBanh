<?php
$pageTitle = 'Dashboard';
require_once 'includes/header.php';
require_once '../../../backend/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Thống kê tổng quan
$stats = [
    'total_products' => $db->query("SELECT COUNT(*) FROM products WHERE status = 1")->fetchColumn(),
    'total_orders' => $db->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'total_customers' => $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn(),
    'pending_orders' => $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
    'total_revenue' => $db->query("SELECT SUM(total_amount) FROM orders WHERE status IN ('completed', 'shipping')")->fetchColumn()
];

// Đơn hàng gần nhất
$queryRecentOrders = "SELECT * FROM orders ORDER BY created_at DESC LIMIT 10";
$stmtOrders = $db->prepare($queryRecentOrders);
$stmtOrders->execute();
$recentOrders = $stmtOrders->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Dashboard</h2>
    <div class="text-muted">Xin chào, <?= e(Session::get('user_name')) ?>!</div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Sản phẩm</h6>
                        <h3 class="mb-0"><?= $stats['total_products'] ?></h3>
                    </div>
                    <div class="text-primary fs-1">
                        <i class="fas fa-box"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Đơn hàng</h6>
                        <h3 class="mb-0"><?= $stats['total_orders'] ?></h3>
                    </div>
                    <div class="text-success fs-1">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Khách hàng</h6>
                        <h3 class="mb-0"><?= $stats['total_customers'] ?></h3>
                    </div>
                    <div class="text-info fs-1">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Doanh thu</h6>
                        <h3 class="mb-0"><?= formatCurrency($stats['total_revenue'] ?? 0) ?></h3>
                    </div>
                    <div class="text-warning fs-1">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Orders Alert -->
<?php if ($stats['pending_orders'] > 0): ?>
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    Có <strong><?= $stats['pending_orders'] ?></strong> đơn hàng đang chờ xác nhận!
    <a href="orders.php" class="alert-link">Xem ngay</a>
</div>
<?php endif; ?>

<!-- Recent Orders -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Đơn hàng gần đây</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Ngày đặt</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td><?= e($order['order_number']) ?></td>
                        <td><?= e($order['customer_name']) ?></td>
                        <td><?= formatCurrency($order['total_amount']) ?></td>
                        <td>
                            <?php
                            $statusColors = [
                                'pending' => 'warning',
                                'confirmed' => 'info',
                                'processing' => 'primary',
                                'shipping' => 'secondary',
                                'completed' => 'success',
                                'cancelled' => 'danger'
                            ];
                            ?>
                            <span class="badge bg-<?= $statusColors[$order['status']] ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                        <td>
                            <a href="order-detail.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
