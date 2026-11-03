<?php
// =========================
//  Account Page (handle-first, no output before render)
// =========================

// 0) Bootstrapping (KHÔNG xuất HTML ở trên)
require_once '../../backend/config/config.php';
require_once '../../backend/config/database.php';
require_once '../../backend/src/helpers/Session.php';
require_once '../../backend/src/helpers/Validator.php';

$pageTitle = 'Tài khoản của tôi';

// 1) Yêu cầu đăng nhập
if (!Session::isLoggedIn()) {
    redirect('frontend/pages/auth/login.php?redirect=' . urlencode('frontend/pages/account.php'));
}

// 2) Lấy user từ session
$user = Session::user();
$userId = $user['id'] ?? null;
if (!$userId) {
    Session::clearUser();
    redirect('frontend/pages/auth/login.php');
}

// 3) CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$passwordErrors = [];

// 4) Kết nối DB
$database = new Database();
$db = $database->getConnection();

// 5) Xử lý cập nhật thông tin cá nhân
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $postedToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $postedToken)) {
        $errors['general'] = 'Phiên làm việc không hợp lệ. Vui lòng thử lại.';
    } else {
        $name    = trim($_POST['name'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        // Validate cơ bản
        $validator = new Validator($_POST);
        $validator->required('name', 'Họ và tên là bắt buộc')
                  ->max('name', 255, 'Họ và tên quá dài')
                  ->max('phone', 20, 'Số điện thoại quá dài')
                  ->max('address', 2000, 'Địa chỉ quá dài');

        if ($phone !== '' && !preg_match('/^(0|\+84)[0-9]{9}$/', $phone)) {
            $errors['phone'] = 'Số điện thoại không hợp lệ (gồm 10 số, bắt đầu bằng 0 hoặc +84)';
        }

        if ($validator->fails()) {
            $errors = array_merge($errors, $validator->errors());
        }

        if (empty($errors)) {
            try {
                $upd = $db->prepare(
                    "UPDATE users
                     SET name = :name, phone = :phone, address = :address, updated_at = NOW()
                     WHERE id = :id LIMIT 1"
                );
                $ok = $upd->execute([
                    ':name'    => $name,
                    ':phone'   => $phone !== '' ? $phone : null,
                    ':address' => $address !== '' ? $address : null,
                    ':id'      => $userId
                ]);

                if ($ok) {
                    // Cập nhật lại session user (để header đổi ngay)
                    $user['name']    = $name;
                    $user['phone']   = $phone;
                    $user['address'] = $address;
                    Session::setUser($user);

                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    Session::setFlash('success', 'Cập nhật thông tin tài khoản thành công!');
                    redirect('frontend/pages/account.php');
                } else {
                    $errors['general'] = 'Không thể cập nhật thông tin. Vui lòng thử lại.';
                }
            } catch (Throwable $e) {
                $errors['general'] = 'Lỗi hệ thống: ' . e($e->getMessage());
            }
        }
    }
}

// 6) Xử lý đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $postedToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $postedToken)) {
        $passwordErrors['general'] = 'Phiên làm việc không hợp lệ. Vui lòng thử lại.';
    } else {
        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newPassword     = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        $pwdValidator = new Validator($_POST);
        $pwdValidator->required('current_password', 'Mật khẩu hiện tại là bắt buộc')
                     ->required('new_password', 'Mật khẩu mới là bắt buộc')
                     ->min('new_password', 6, 'Mật khẩu mới phải có ít nhất 6 ký tự')
                     ->match('confirm_password', 'new_password', 'Xác nhận mật khẩu mới không khớp');

        if ($pwdValidator->fails()) {
            $passwordErrors = $pwdValidator->errors();
        } else {
            try {
                $chkStmt = $db->prepare("SELECT password FROM users WHERE id = :id LIMIT 1");
                $chkStmt->execute([':id' => $userId]);
                $userPwd = $chkStmt->fetchColumn();

                if (!$userPwd || !password_verify($currentPassword, $userPwd)) {
                    $passwordErrors['current_password'] = 'Mật khẩu hiện tại không chính xác.';
                } else {
                    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $updPwd = $db->prepare("UPDATE users SET password = :pwd, updated_at = NOW() WHERE id = :id LIMIT 1");
                    $updPwd->execute([':pwd' => $newHash, ':id' => $userId]);

                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    Session::setFlash('success', 'Đổi mật khẩu thành công!');
                    redirect('frontend/pages/account.php');
                }
            } catch (Throwable $e) {
                $passwordErrors['general'] = 'Lỗi hệ thống: ' . e($e->getMessage());
            }
        }
    }
}

// 7) Lấy thông tin user mới nhất từ DB
try {
    $stmt = $db->prepare(
        "SELECT id, name, email, phone, address, avatar, role, status, created_at, updated_at
         FROM users WHERE id = :id LIMIT 1"
    );
    $stmt->execute([':id' => $userId]);
    $me = $stmt->fetch() ?: $user;
} catch (Throwable $e) {
    $me = $user;
    $errors['general'] = 'Không thể tải thông tin tài khoản.';
}

// 8) Lấy danh sách đơn hàng và thống kê
$orders = [];
$totalSpent = 0;
try {
    $q = $db->prepare(
        "SELECT id, order_number, total_amount, payment_method, status, created_at
         FROM orders
         WHERE user_id = :uid
         ORDER BY created_at DESC"
    );
    $q->execute([':uid' => $userId]);
    $orders = $q->fetchAll() ?: [];
    foreach ($orders as $ord) {
        if ($ord['status'] !== 'cancelled') {
            $totalSpent += (float)$ord['total_amount'];
        }
    }
} catch (Throwable $e) {
    $orders = [];
}

// 9) Render giao diện
require_once '../components/header.php';
?>
<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3 border-bottom">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('frontend/pages/index.php') ?>" class="text-decoration-none">Trang chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tài khoản của tôi</li>
        </ol>
    </div>
</nav>

<div class="container py-5">
    <!-- Flash Messages -->
    <?php if ($msg = Session::getFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= e($msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($msg = Session::getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= e($msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Cột trái: Hồ sơ & Thao tác -->
        <div class="col-lg-4">
            <!-- Card tóm tắt tài khoản -->
            <div class="card shadow-sm border-0 mb-4 text-center">
                <div class="card-body p-4">
                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 80px; height: 80px; border: 2px solid #8B4513;">
                        <i class="fas fa-user-circle fa-4x text-brown" style="color: #8B4513;"></i>
                    </div>
                    <h5 class="card-title mb-1 fw-bold"><?= e($me['name'] ?? 'Khách hàng') ?></h5>
                    <p class="text-muted small mb-2"><i class="fas fa-envelope me-1"></i><?= e($me['email'] ?? '') ?></p>
                    
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <span class="badge bg-<?= ($me['role'] ?? 'customer') === 'admin' ? 'danger' : 'primary' ?>">
                            <?= ($me['role'] ?? 'customer') === 'admin' ? '<i class="fas fa-shield-alt me-1"></i>Quản trị viên' : '<i class="fas fa-user me-1"></i>Khách hàng' ?>
                        </span>
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle me-1"></i>Đang hoạt động
                        </span>
                    </div>

                    <?php if (!empty($me['created_at'])): ?>
                        <small class="text-muted d-block border-top pt-2">
                            <i class="fas fa-calendar-alt me-1"></i>Thành viên từ: <?= date('d/m/Y', strtotime($me['created_at'])) ?>
                        </small>
                    <?php endif; ?>

                    <?php if (($me['role'] ?? 'customer') === 'admin'): ?>
                        <div class="mt-3">
                            <a href="<?= url('frontend/pages/admin/dashboard.php') ?>" class="btn btn-sm btn-outline-danger w-100">
                                <i class="fas fa-cog me-1"></i>Truy cập Trang Quản Trị
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Card Cập nhật thông tin -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-bold" style="color: #8B4513;">
                        <i class="fas fa-user-edit me-2"></i>Cập nhật thông tin
                    </h5>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger py-2 small"><?= e($errors['general']) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>"
                                   name="name" value="<?= e($me['name'] ?? '') ?>" required>
                            <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback"><?= e($errors['name']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control bg-light" value="<?= e($me['email'] ?? '') ?>" disabled readonly>
                            <small class="text-muted">Email đăng nhập không thể thay đổi</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Số điện thoại</label>
                            <input type="tel"
                                   class="form-control<?= isset($errors['phone']) ? ' is-invalid' : '' ?>"
                                   name="phone" placeholder="0901234567"
                                   value="<?= e($me['phone'] ?? '') ?>">
                            <?php if (isset($errors['phone'])): ?>
                                <div class="invalid-feedback"><?= e($errors['phone']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Địa chỉ giao hàng mặc định</label>
                            <textarea class="form-control<?= isset($errors['address']) ? ' is-invalid' : '' ?>"
                                      name="address" rows="3"
                                      placeholder="Số nhà, tên đường, phường/xã, quận/huyện..."><?= e($me['address'] ?? '') ?></textarea>
                            <?php if (isset($errors['address'])): ?>
                                <div class="invalid-feedback"><?= e($errors['address']) ?></div>
                            <?php endif; ?>
                        </div>

                        <button type="submit" name="update_profile" class="btn btn-brown w-100">
                            <i class="fas fa-save me-1"></i>Lưu thay đổi
                        </button>
                    </form>
                </div>
            </div>

            <!-- Card Đổi mật khẩu -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-bold" style="color: #8B4513;">
                        <i class="fas fa-key me-2"></i>Đổi mật khẩu
                    </h5>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($passwordErrors['general'])): ?>
                        <div class="alert alert-danger py-2 small"><?= e($passwordErrors['general']) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                            <input type="password"
                                   class="form-control<?= isset($passwordErrors['current_password']) ? ' is-invalid' : '' ?>"
                                   name="current_password" required>
                            <?php if (isset($passwordErrors['current_password'])): ?>
                                <div class="invalid-feedback"><?= e($passwordErrors['current_password']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mật khẩu mới <span class="text-danger">*</span></label>
                            <input type="password"
                                   class="form-control<?= isset($passwordErrors['new_password']) ? ' is-invalid' : '' ?>"
                                   name="new_password" required minlength="6">
                            <?php if (isset($passwordErrors['new_password'])): ?>
                                <div class="invalid-feedback"><?= e($passwordErrors['new_password']) ?></div>
                            <?php else: ?>
                                <small class="text-muted">Tối thiểu 6 ký tự</small>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                            <input type="password"
                                   class="form-control<?= isset($passwordErrors['confirm_password']) ? ' is-invalid' : '' ?>"
                                   name="confirm_password" required>
                            <?php if (isset($passwordErrors['confirm_password'])): ?>
                                <div class="invalid-feedback"><?= e($passwordErrors['confirm_password']) ?></div>
                            <?php endif; ?>
                        </div>

                        <button type="submit" name="change_password" class="btn btn-outline-brown w-100">
                            <i class="fas fa-lock me-1"></i>Cập nhật mật khẩu
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Cột phải: Thống kê & Lịch sử đơn hàng -->
        <div class="col-lg-8">
            <!-- Thống kê nhanh đơn hàng -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6">
                    <div class="card border-0 shadow-sm p-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle p-3 bg-light text-brown me-3" style="color: #8B4513;">
                                <i class="fas fa-shopping-bag fa-2x"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Tổng số đơn hàng</small>
                                <span class="h4 fw-bold mb-0"><?= count($orders) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="card border-0 shadow-sm p-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle p-3 bg-light text-success me-3">
                                <i class="fas fa-wallet fa-2x"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Tổng tiền đã chi</small>
                                <span class="h4 fw-bold mb-0 text-success"><?= function_exists('formatCurrency') ? formatCurrency($totalSpent) : number_format($totalSpent, 0, ',', '.') . '₫' ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bảng Lịch sử đơn hàng -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold" style="color: #8B4513;">
                        <i class="fas fa-history me-2"></i>Lịch sử đơn hàng
                    </h5>
                    <?php if (!empty($orders)): ?>
                        <span class="badge bg-brown text-white" style="background-color: #8B4513;"><?= count($orders) ?> đơn</span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($orders)): ?>
                        <div class="text-center py-5">
                            <div class="mb-3 text-muted">
                                <i class="fas fa-box-open fa-4x opacity-50"></i>
                            </div>
                            <h6 class="text-muted">Bạn chưa có đơn hàng nào</h6>
                            <p class="small text-muted mb-4">Hãy khám phá các món bánh tươi ngon ngay hôm nay!</p>
                            <a href="<?= url('frontend/pages/products/list.php') ?>" class="btn btn-brown btn-sm">
                                <i class="fas fa-shopping-cart me-1"></i>Mua sắm ngay
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Mã đơn</th>
                                    <th>Ngày đặt</th>
                                    <th>Tổng tiền</th>
                                    <th>Thanh toán</th>
                                    <th>Trạng thái</th>
                                    <th class="text-center pe-4">Thao tác</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold">
                                            #<?= e($order['order_number']) ?>
                                        </td>
                                        <td class="text-muted small">
                                            <?= e(date('d/m/Y H:i', strtotime($order['created_at']))) ?>
                                        </td>
                                        <td class="fw-bold text-danger">
                                            <?= e(function_exists('formatCurrency')
                                                ? formatCurrency($order['total_amount'])
                                                : number_format((float)$order['total_amount'], 0, ',', '.') . '₫') ?>
                                        </td>
                                        <td class="small">
                                            <?= strtoupper(e($order['payment_method'] ?? 'COD')) ?>
                                        </td>
                                        <td>
                                            <?php
                                            $statusMap = [
                                                'pending'    => ['label' => 'Chờ xác nhận', 'class' => 'warning text-dark'],
                                                'confirmed'  => ['label' => 'Đã xác nhận',  'class' => 'info text-dark'],
                                                'processing' => ['label' => 'Đang làm bánh',  'class' => 'primary'],
                                                'shipping'   => ['label' => 'Đang giao',    'class' => 'secondary'],
                                                'completed'  => ['label' => 'Hoàn thành',   'class' => 'success'],
                                                'cancelled'  => ['label' => 'Đã hủy',       'class' => 'danger']
                                            ];
                                            $st = $order['status'] ?? 'pending';
                                            $meta = $statusMap[$st] ?? ['label' => ucfirst($st), 'class' => 'secondary'];
                                            ?>
                                            <span class="badge bg-<?= e($meta['class']) ?>">
                                                <?= e($meta['label']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center pe-4">
                                            <a href="<?= url('frontend/pages/order-detail.php?id=' . e($order['id'])) ?>"
                                               class="btn btn-sm btn-outline-brown">
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
</div>

<?php require_once '../components/footer.php'; ?>
