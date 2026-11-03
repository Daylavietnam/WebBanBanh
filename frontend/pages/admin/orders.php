<?php
$pageTitle = 'Quản lý đơn hàng';
require_once 'includes/header.php';
require_once '../../../backend/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Filter
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$where = "1=1";
$params = [];

if ($status) {
    $where .= " AND status = :status";
    $params[':status'] = $status;
}

if ($search) {
    $where .= " AND (order_number LIKE :search OR customer_name LIKE :search OR customer_phone LIKE :search)";
    $params[':search'] = "%$search%";
}

$query = "SELECT * FROM orders WHERE $where ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Thống kê số lượng theo trạng thái
$statuses = $db->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Quản lý đơn hàng</h2>
</div>

<!-- Status Filter Tabs -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?= empty($status) ? 'active' : '' ?>" href="orders.php">
            Tất cả <span class="badge bg-secondary"><?= array_sum($statuses) ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $status === 'pending' ? 'active' : '' ?>" href="?status=pending">
            Chờ xác nhận <span class="badge bg-warning"><?= $statuses['pending'] ?? 0 ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $status === 'confirmed' ? 'active' : '' ?>" href="?status=confirmed">
            Đã xác nhận <span class="badge bg-info"><?= $statuses['confirmed'] ?? 0 ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $status === 'shipping' ? 'active' : '' ?>" href="?status=shipping">
            Đang giao <span class="badge bg-primary"><?= $statuses['shipping'] ?? 0 ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $status === 'completed' ? 'active' : '' ?>" href="?status=completed">
            Hoàn thành <span class="badge bg-success"><?= $statuses['completed'] ?? 0 ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $status === 'cancelled' ? 'active' : '' ?>" href="?status=cancelled">
            Đã hủy <span class="badge bg-danger"><?= $statuses['cancelled'] ?? 0 ?></span>
        </a>
    </li>
</ul>

<!-- Search Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <?php if ($status): ?>
                <input type="hidden" name="status" value="<?= e($status) ?>">
            <?php endif; ?>
            <div class="col-md-10">
                <input type="text" name="search" class="form-control" 
                       placeholder="Tìm theo mã đơn, tên khách hàng, số điện thoại..." 
                       value="<?= e($search) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
            </div>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Điện thoại</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Ngày đặt</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong><?= e($order['order_number']) ?></strong></td>
                        <td><?= e($order['customer_name']) ?></td>
                        <td><?= e($order['customer_phone']) ?></td>
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
                            $statusTexts = [
                                'pending' => 'Chờ xác nhận',
                                'confirmed' => 'Đã xác nhận',
                                'processing' => 'Đang xử lý',
                                'shipping' => 'Đang giao',
                                'completed' => 'Hoàn thành',
                                'cancelled' => 'Đã hủy'
                            ];
                            ?>
                            <span class="badge bg-<?= $statusColors[$order['status']] ?>">
                                <?= $statusTexts[$order['status']] ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                        <td class="table-actions">
                            <a href="order-detail.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> Chi tiết
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
