<?php
$pageTitle = 'Quản lý liên hệ';
require_once 'includes/header.php';
require_once '../../../backend/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Filter by status
$statusFilter = $_GET['status'] ?? '';
$where = '1=1';

if ($statusFilter && in_array($statusFilter, ['new', 'processing', 'completed'])) {
    $where .= " AND status = '$statusFilter'";
}

// Get contacts
$query = "SELECT * FROM contacts WHERE $where ORDER BY created_at DESC";
$stmt = $db->query($query);
$contacts = $stmt->fetchAll();

// Statistics
$stats = [
    'total' => $db->query("SELECT COUNT(*) FROM contacts")->fetchColumn(),
    'new' => $db->query("SELECT COUNT(*) FROM contacts WHERE status = 'new'")->fetchColumn(),
    'processing' => $db->query("SELECT COUNT(*) FROM contacts WHERE status = 'processing'")->fetchColumn(),
    'completed' => $db->query("SELECT COUNT(*) FROM contacts WHERE status = 'completed'")->fetchColumn()
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Quản lý liên hệ</h2>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Tổng số</h6>
                        <h3 class="mb-0"><?= $stats['total'] ?></h3>
                    </div>
                    <div class="text-primary fs-1">
                        <i class="fas fa-envelope"></i>
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
                        <h6 class="text-muted mb-1">Mới</h6>
                        <h3 class="mb-0"><?= $stats['new'] ?></h3>
                    </div>
                    <div class="text-warning fs-1">
                        <i class="fas fa-envelope-open"></i>
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
                        <h6 class="text-muted mb-1">Đang xử lý</h6>
                        <h3 class="mb-0"><?= $stats['processing'] ?></h3>
                    </div>
                    <div class="text-info fs-1">
                        <i class="fas fa-spinner"></i>
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
                        <h6 class="text-muted mb-1">Hoàn thành</h6>
                        <h3 class="mb-0"><?= $stats['completed'] ?></h3>
                    </div>
                    <div class="text-success fs-1">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <div class="btn-group" role="group">
            <a href="contacts.php" class="btn btn-outline-primary <?= empty($statusFilter) ? 'active' : '' ?>">
                Tất cả (<?= $stats['total'] ?>)
            </a>
            <a href="contacts.php?status=new" class="btn btn-outline-warning <?= $statusFilter === 'new' ? 'active' : '' ?>">
                Mới (<?= $stats['new'] ?>)
            </a>
            <a href="contacts.php?status=processing" class="btn btn-outline-info <?= $statusFilter === 'processing' ? 'active' : '' ?>">
                Đang xử lý (<?= $stats['processing'] ?>)
            </a>
            <a href="contacts.php?status=completed" class="btn btn-outline-success <?= $statusFilter === 'completed' ? 'active' : '' ?>">
                Hoàn thành (<?= $stats['completed'] ?>)
            </a>
        </div>
    </div>
</div>

<!-- Contacts Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($contacts)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>Chưa có tin nhắn liên hệ nào.
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>Điện thoại</th>
                        <th>Tiêu đề</th>
                        <th>Ngày gửi</th>
                        <th>Trạng thái</th>
                        <th width="100">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contacts as $contact): ?>
                    <tr>
                        <td><?= $contact['id'] ?></td>
                        <td>
                            <strong><?= e($contact['name']) ?></strong>
                        </td>
                        <td><?= e($contact['email']) ?></td>
                        <td><?= e($contact['phone'] ?: '-') ?></td>
                        <td><?= e($contact['subject'] ?: '-') ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($contact['created_at'])) ?></td>
                        <td>
                            <?php
                            $statusColors = [
                                'new' => 'warning',
                                'processing' => 'info',
                                'completed' => 'success'
                            ];
                            $statusTexts = [
                                'new' => 'Mới',
                                'processing' => 'Đang xử lý',
                                'completed' => 'Hoàn thành'
                            ];
                            ?>
                            <span class="badge bg-<?= $statusColors[$contact['status']] ?>">
                                <?= $statusTexts[$contact['status']] ?>
                            </span>
                        </td>
                        <td class="table-actions">
                            <a href="contact-detail.php?id=<?= $contact['id'] ?>" class="btn btn-sm btn-primary" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="contact-delete.php?id=<?= $contact['id'] ?>" 
                               class="btn btn-sm btn-danger" 
                               title="Xóa"
                               onclick="return confirm('Bạn có chắc muốn xóa?')">
                                <i class="fas fa-trash"></i>
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

<?php require_once 'includes/footer.php'; ?>