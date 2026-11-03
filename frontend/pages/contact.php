<?php
$pageTitle = 'Liên hệ';
require_once '../components/header.php';
require_once '../../backend/config/database.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    // Validation
    if (empty($name)) $errors['name'] = 'Vui lòng nhập họ tên';
    if (empty($email)) $errors['email'] = 'Vui lòng nhập email';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email không hợp lệ';
    if (empty($message)) $errors['message'] = 'Vui lòng nhập nội dung';
    
    if (empty($errors)) {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "INSERT INTO contacts (name, email, phone, subject, message) 
                  VALUES (:name, :email, :phone, :subject, :message)";
        $stmt = $db->prepare($query);
        $result = $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':subject' => $subject,
            ':message' => $message
        ]);
        
        if ($result) {
            $success = true;
            Session::setFlash('success', 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất.');
        } else {
            $errors['general'] = 'Có lỗi xảy ra, vui lòng thử lại';
        }
    }
}
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('frontend/pages/index.php') ?>">Trang chủ</a></li>
            <li class="breadcrumb-item active">Liên hệ</li>
        </ol>
    </div>
</nav>

<!-- Contact Section -->
<div class="container py-5">
    <h2 class="mb-4 text-center">Liên hệ với chúng tôi</h2>
    <p class="text-center text-muted mb-5">
        Hãy để lại thông tin, chúng tôi sẽ liên hệ lại với bạn sớm nhất
    </p>
    
    <div class="row">
        <!-- Contact Info -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-4">Thông tin liên hệ</h5>
                    
                    <div class="mb-4">
                        <div class="d-flex align-items-start">
                            <div class="text-brown me-3 fs-4">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <strong>Địa chỉ:</strong><br>
                                <span class="text-muted">
                                    Số 15, hẻm 75 ngách 51, ngõ Linh Quang,<br>
                                    phường Văn Miếu - Quốc Tử Giám, Hà Nội
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex align-items-start">
                            <div class="text-brown me-3 fs-4">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <strong>Điện thoại:</strong><br>
                                <a href="tel:02438222228" class="text-muted text-decoration-none">02438222228</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex align-items-start">
                            <div class="text-brown me-3 fs-4">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <strong>Email:</strong><br>
                                <a href="mailto:info@nguyenson.vn" class="text-muted text-decoration-none">info@nguyenson.vn</a>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="d-flex align-items-start">
                            <div class="text-brown me-3 fs-4">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <strong>Giờ làm việc:</strong><br>
                                <span class="text-muted">
                                    Thứ 2 - Chủ nhật: 8:00 - 22:00
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div>
                        <strong class="d-block mb-3">Kết nối với chúng tôi:</strong>
                        <a href="https://www.facebook.com/NguyenSonBakery" target="_blank" class="btn btn-outline-primary btn-sm me-2">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </a>
                        <a href="#" class="btn btn-outline-danger btn-sm">
                            <i class="fab fa-instagram"></i> Instagram
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contact Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất.
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger"><?= e($errors['general']) ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                       value="<?= e($_POST['name'] ?? '') ?>" required>
                                <?php if (isset($errors['name'])): ?>
                                    <div class="invalid-feedback"><?= e($errors['name']) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                       value="<?= e($_POST['email'] ?? '') ?>" required>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?= e($errors['email']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control" value="<?= e($_POST['phone'] ?? '') ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tiêu đề</label>
                                <input type="text" name="subject" class="form-control" value="<?= e($_POST['subject'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control <?= isset($errors['message']) ? 'is-invalid' : '' ?>" 
                                      rows="6" required><?= e($_POST['message'] ?? '') ?></textarea>
                            <?php if (isset($errors['message'])): ?>
                                <div class="invalid-feedback"><?= e($errors['message']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" class="btn btn-brown btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Gửi tin nhắn
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Map -->
    <div class="row mt-5">
        <div class="col-12">
            <h4 class="mb-4">Bản đồ</h4>
            <div class="map-container" style="height: 400px;">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3724.4911926778607!2d105.83525931533447!3d21.011632393829866!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135ab953357c995%3A0x54ab87e11d8f8fb!2zVsSDbiBNaeG6v3UsIMSQw7RuZyDEkGEsIEjDoCBO4buZaQ!5e0!3m2!1svi!2s!4v1234567890123!5m2!1svi!2s" 
                        width="100%" 
                        height="100%" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy">
                </iframe>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/footer.php'; ?>
