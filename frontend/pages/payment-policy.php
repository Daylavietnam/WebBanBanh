<?php
require_once '../../backend/config/config.php';
require_once '../../backend/config/database.php';

$pageTitle = 'Chính sách thanh toán';
require_once '../components/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('frontend/pages/index.php') ?>">Trang chủ</a></li>
            <li class="breadcrumb-item active">Chính sách thanh toán</li>
        </ol>
    </div>
</nav>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <h1 class="mb-4" style="color: #8B4513;">Chính sách thanh toán</h1>
            
            <p class="lead">
                Nguyễn Sơn Bakery hỗ trợ nhiều hình thức thanh toán linh hoạt, 
                giúp quý khách dễ dàng mua sắm.
            </p>

            <!-- COD -->
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title" style="color: #8B4513;">
                        <i class="fas fa-money-bill-wave me-2"></i>1. Thanh toán khi nhận hàng (COD)
                    </h4>
                    <hr>
                    <ul>
                        <li><strong>Áp dụng:</strong> Tất cả đơn hàng trong nội thành Hà Nội</li>
                        <li><strong>Cách thức:</strong> Khách hàng trả tiền mặt cho shipper khi nhận hàng</li>
                        <li><strong>Lưu ý:</strong> Vui lòng kiểm tra kỹ sản phẩm trước khi thanh toán</li>
                        <li><strong>Phí COD:</strong> Miễn phí</li>
                    </ul>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Lưu ý:</strong> Nếu từ chối nhận hàng 3 lần liên tiếp không có lý do chính đáng, 
                        tài khoản có thể bị tạm khóa.
                    </div>
                </div>
            </div>

            <!-- Chuyển khoản -->
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title" style="color: #8B4513;">
                        <i class="fas fa-university me-2"></i>2. Chuyển khoản ngân hàng
                    </h4>
                    <hr>
                    <p><strong>Thông tin tài khoản:</strong></p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="text-primary">Vietcombank</h6>
                                    <p class="mb-1"><strong>Số TK:</strong> 1234567890</p>
                                    <p class="mb-1"><strong>Chủ TK:</strong> Nguyễn Văn Sơn</p>
                                    <p class="mb-0"><strong>Chi nhánh:</strong> Hà Nội</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="text-danger">Techcombank</h6>
                                    <p class="mb-1"><strong>Số TK:</strong> 9876543210</p>
                                    <p class="mb-1"><strong>Chủ TK:</strong> Nguyễn Văn Sơn</p>
                                    <p class="mb-0"><strong>Chi nhánh:</strong> Hà Nội</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <p><strong>Nội dung chuyển khoản:</strong></p>
                        <div class="alert alert-info">
                            <code>[Số điện thoại] - [Mã đơn hàng]</code><br>
                            <small class="text-muted">Ví dụ: 0912345678 - DH001</small>
                        </div>
                        <ul>
                            <li>Đơn hàng được xử lý sau khi nhận được tiền (15-30 phút)</li>
                            <li>Vui lòng gửi ảnh chụp biên lai chuyển khoản qua Zalo/Email để xác nhận nhanh</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Thẻ -->
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title" style="color: #8B4513;">
                        <i class="fas fa-credit-card me-2"></i>3. Thanh toán qua thẻ/Ví điện tử
                    </h4>
                    <hr>
                    <p><strong>Chấp nhận:</strong></p>
                    <div class="row text-center">
                        <div class="col-4 col-md-2 mb-3">
                            <i class="fab fa-cc-visa fa-3x text-primary"></i>
                            <p class="small mt-2">Visa</p>
                        </div>
                        <div class="col-4 col-md-2 mb-3">
                            <i class="fab fa-cc-mastercard fa-3x text-danger"></i>
                            <p class="small mt-2">Mastercard</p>
                        </div>
                        <div class="col-4 col-md-2 mb-3">
                            <i class="fas fa-mobile-alt fa-3x text-success"></i>
                            <p class="small mt-2">MoMo</p>
                        </div>
                        <div class="col-4 col-md-2 mb-3">
                            <i class="fas fa-wallet fa-3x text-warning"></i>
                            <p class="small mt-2">ZaloPay</p>
                        </div>
                        <div class="col-4 col-md-2 mb-3">
                            <i class="fas fa-qrcode fa-3x text-info"></i>
                            <p class="small mt-2">VNPay QR</p>
                        </div>
                        <div class="col-4 col-md-2 mb-3">
                            <i class="fas fa-building fa-3x text-secondary"></i>
                            <p class="small mt-2">ATM nội địa</p>
                        </div>
                    </div>
                    <p class="text-muted small">
                        <i class="fas fa-lock me-2"></i>
                        Giao dịch được mã hóa SSL, đảm bảo an toàn tuyệt đối.
                    </p>
                </div>
            </div>

            <!-- Lưu ý -->
            <div class="alert alert-success">
                <h5 class="alert-heading">
                    <i class="fas fa-info-circle me-2"></i>Lưu ý quan trọng
                </h5>
                <ul class="mb-0">
                    <li>Giá sản phẩm đã bao gồm VAT</li>
                    <li>Phí giao hàng sẽ được tính riêng (nếu có)</li>
                    <li>Khách hàng có thể yêu cầu hóa đơn đỏ VAT khi đặt hàng</li>
                    <li>Liên hệ: <strong>02438222228</strong> nếu cần hỗ trợ thanh toán</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/footer.php'; ?>
