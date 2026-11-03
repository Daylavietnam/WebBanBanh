<?php
$pageTitle = 'Chi tiết liên hệ';
require_once 'includes/header.php';
require_once '../../../backend/config/database.php';

$database = new Database();
$db = $database->getConnection();

$contactId = (int)($_GET['id'] ?? 0);

if ($contactId <= 0) {
    Session::setFlash('error', 'Liên hệ không tồn tại');
    header('Location: contacts.php');
    exit();
}

// Get contact
$stmt = $db->prepare("SELECT * FROM contacts WHERE id = ?");
$stmt->execute([$contactId]);
$contact = $stmt->fetch();

if (!$contact) {
    Session::setFlash('error', 'Liên hệ không tồn tại');
    header('Location: contacts.php');
    exit();
}

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = $_POST['status'];
    
    if (in_array($newStatus, ['new', 'processing', 'completed'])) {
        $updateStmt = $db->prepare("UPDATE contacts SET status = ? WHERE id = ?");
        $updateStmt->execute([$newStatus, $contactId]);
        
        Session::setFlash('success', 'Cập nhật trạng thái thành công!');
        header('Location: contact-detail.php?id=' . $contactId);
        exit();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Chi tiết liên hệ #<?= $contact['id'] ?></h2>
    <a href="contacts.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Contact Message -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Nội dung tin nhắn</h5>
            </div>
            <div class="card-body">
                <?php if ($contact['subject']): ?>
                    <div class="mb-3">
                        <strong>Tiêu đề:</strong>
                        <p class="mb-0"><?= e($contact['subject']) ?></p>
                    </div>
                    <hr>
                <?php endif; ?>
                
                <div>
                    <strong>Nội dung:</strong>
                    <p class="mb-0 mt-2" style="white-space: pre-wrap;"><?= e($contact['message']) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Customer Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Thông tin khách hàng</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <th width="100"><i class="fas fa-user me-2"></i>Họ tên:</th>
                        <td><?= e($contact['name']) ?></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-envelope me-2"></i>Email:</th>
                        <td><a href="mailto:<?= e($contact['email']) ?>"><?= e($contact['email']) ?></a></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-phone me-2"></i>Điện thoại:</th>
                        <td>
                            <?php if ($contact['phone']): ?>
                                <a href="tel:<?= e($contact['phone']) ?>"><?= e($contact['phone']) ?></a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th><i class="far fa-calendar me-2"></i>Ngày gửi:</th>
                        <td><?= date('d/m/Y H:i', strtotime($contact['created_at'])) ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Status -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Trạng thái</h5>
            </div>
            <div class="card-body">
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
                
                <div class="text-center mb-3">
                    <span class="badge bg-<?= $statusColors[$contact['status']] ?> fs-6 px-4 py-2">
                        <?= $statusTexts[$contact['status']] ?>
                    </span>
                </div>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Cập nhật trạng thái:</label>
                        <select name="status" class="form-select" required>
                            <option value="new" <?= $contact['status'] === 'new' ? 'selected' : '' ?>>Mới</option>
                            <option value="processing" <?= $contact['status'] === 'processing' ? 'selected' : '' ?>>Đang xử lý</option>
                            <option value="completed" <?= $contact['status'] === 'completed' ? 'selected' : '' ?>>Hoàn thành</option>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> Cập nhật
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
