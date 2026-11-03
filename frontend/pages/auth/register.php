<?php
// =========================
//  Register Page (handle-first, no output before redirect)
// =========================

require_once '../../../backend/config/config.php';
require_once '../../../backend/config/database.php';
require_once '../../../backend/src/helpers/Session.php';
require_once '../../../backend/src/helpers/Validator.php';

$pageTitle = 'Đăng ký';

// Nếu đã đăng nhập → về trang chủ
if (Session::isLoggedIn()) {
    redirect('frontend/pages/index.php');
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors  = [];
$oldData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check CSRF
    $postedToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $postedToken)) {
        $errors['general'] = 'Phiên làm việc không hợp lệ. Vui lòng thử lại.';
    } else {
        // Lấy dữ liệu
        $name            = trim($_POST['name'] ?? '');
        $email           = trim($_POST['email'] ?? '');
        $phone           = trim($_POST['phone'] ?? '');
        $password        = (string)($_POST['password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        $oldData = ['name' => $name, 'email' => $email, 'phone' => $phone];

        // Validate
        $validator = new Validator($_POST);
        $validator->required('name', 'Họ tên là bắt buộc')
                  ->required('email', 'Email là bắt buộc')->email('email', 'Email không hợp lệ')
                  ->required('password', 'Mật khẩu là bắt buộc')->min('password', 6, 'Mật khẩu phải >= 6 ký tự')
                  ->match('confirm_password', 'password', 'Mật khẩu xác nhận không khớp');

        if ($validator->fails()) {
            $errors = $validator->errors();
        } else {
            try {
                $database = new Database();
                $db = $database->getConnection(); // Nên để ATTR_ERRMODE: EXCEPTION trong Database.php

                // Email trùng?
                $stmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
                $stmt->execute([':email' => $email]);

                if ($stmt->fetch()) {
                    $errors['email'] = 'Email này đã được đăng ký';
                } else {
                    // Tạo user (role mặc định 'customer', status=1, email_verified=0, created_at/updated_at tự set)
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $insert = $db->prepare("
                        INSERT INTO users (name, email, password, phone, role, status, email_verified)
                        VALUES (:name, :email, :password, :phone, 'customer', 1, 0)
                    ");
                    $ok = $insert->execute([
                        ':name'     => $name,
                        ':email'    => $email,
                        ':password' => $hash,
                        ':phone'    => $phone
                    ]);

                    if ($ok) {
                        // Reset CSRF để tránh resubmit, set flash, redirect sớm
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        Session::setFlash('success', 'Đăng ký thành công! Vui lòng đăng nhập.');
                        redirect('frontend/pages/auth/login.php');
                    } else {
                        $errors['general'] = 'Có lỗi khi lưu dữ liệu. Vui lòng thử lại.';
                    }
                }
            } catch (Throwable $e) {
                // Không in stacktrace thật ở production
                $errors['general'] = 'Lỗi hệ thống: ' . e($e->getMessage());
            }
        }
    }
}

// Chỉ render khi KHÔNG redirect
require_once '../../components/header.php';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Đăng ký tài khoản</h2>

                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger"><?= e($errors['general']) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                        <div class="mb-3">
                            <label for="name" class="form-label">Họ tên <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                                id="name" name="name"
                                value="<?= e($oldData['name'] ?? '') ?>" required
                            >
                            <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback"><?= e($errors['name']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input
                                type="email"
                                class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                id="email" name="email"
                                value="<?= e($oldData['email'] ?? '') ?>" required
                            >
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?= e($errors['email']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input
                                type="text"
                                class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
                                id="phone" name="phone"
                                value="<?= e($oldData['phone'] ?? '') ?>"
                            >
                            <?php if (isset($errors['phone'])): ?>
                                <div class="invalid-feedback"><?= e($errors['phone']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                            <input
                                type="password"
                                class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                id="password" name="password" required
                            >
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback"><?= e($errors['password']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                            <input
                                type="password"
                                class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                                id="confirm_password" name="confirm_password" required
                            >
                            <?php if (isset($errors['confirm_password'])): ?>
                                <div class="invalid-feedback"><?= e($errors['confirm_password']) ?></div>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-brown w-100">Đăng ký</button>

                        <div class="text-center mt-3">
                            <p class="mb-0">Đã có tài khoản?
                                <a href="<?= url('frontend/pages/auth/login.php') ?>">Đăng nhập ngay</a>
                            </p>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../../components/footer.php'; ?>
