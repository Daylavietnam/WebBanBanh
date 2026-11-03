<?php
require_once '../../backend/config/config.php';
require_once '../../backend/config/database.php';

$pageTitle = 'Chính sách đổi trả';
require_once '../components/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('frontend/pages/index.php') ?>">Trang chủ</a></li>
            <li class="breadcrumb-item active">Chính sách đổi trả</li>
        </ol>
    </div>
</nav>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <h1 class="mb-4" style="color: #8B4513;">Chính sách đổi trả</h1>
            
            <div class="alert alert-success">
                <strong>Cam kết:</strong> Nguyễn Sơn Bakery cam kết đổi trả 100% nếu sản phẩm có lỗi do nhà sản xuất.
            </div>

            <!-- Điều kiện đổi trả -->
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title" style="color: #8B4513;">
                        <i class="fas fa-check-circle me-2"></i>1. Điều kiện đổi trả
                    </h4>
                    <hr>
                    
                    <h5 class="text-success mt-4">✓ Được chấp nhận đổi trả khi:</h5>
                    <ul>
                        <li>Sản phẩm bị hỏng, vỡ, biến dạng do vận chuyển</li>
                        <li>Sản phẩm không đúng như đơn đặt hàng</li>
                        <li>Sản phẩm có mùi lạ, có dấu hiệu hư hỏng</li>
                        <li>Bánh bị chảy, tan do giao hàng không đúng nhiệt độ</li>
                    </ul>

                    <h5 class="text-danger mt-4">✗ Không chấp nhận đổi trả khi:</h5>
                    <ul>
                        <li>Khách hàng tự ý đổi ý không thích (sản phẩm thực phẩm tươi sống)</li>
                        <li>Sản phẩm đã qua sử dụng hoặc đã cắt miếng</li>
                        <li>Hết hạn đổi trả (quá 24h kể từ khi nhận hàng)</li>
                        <li>Không còn bao bì, hộp đựng nguyên vẹn</li>
                    </ul>
                </div>
            </div>

            <!-- Thời gian đổi trả -->
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title" style="color: #8B4513;">
                        <i class="fas fa-clock me-2"></i>2. Thời gian đổi trả
                    </h4>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h2 class="text-danger">24 giờ</h2>
                                    <p class="mb-0">Thời gian đổi trả tối đa</p>
                                    <small class="text-muted">(Kể từ khi nhận hàng)</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h2 class="text-success">Ngay lập tức</h2>
                                    <p class="mb-0">Nếu phát hiện lỗi khi nhận hàng</p>
                                    <small class="text-muted">(Báo shipper ngay)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quy trình đổi trả -->
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title" style="color: #8B4513;">
                        <i class="fas fa-sync-alt me-2"></i>3. Quy trình đổi trả
                    </h4>
                    <hr>
                    <div class="timeline">
                        <div class="d-flex mb-4">
                            <div class="badge bg-primary rounded-circle me-3" style="width: 40px; height: 40px; line-height: 40px;">1</div>
                            <div>
                                <h6 class="mb-1">Liên hệ ngay</h6>
                                <p class="mb-0 text-muted">
                                    Gọi hotline <strong>02438222228</strong> hoặc nhắn tin Zalo/Facebook 
                                    để thông báo lỗi sản phẩm.
                                </p>
                            </div>
                        </div>
                        <div class="d-flex mb-4">
                            <div class="badge bg-primary rounded-circle me-3" style="width: 40px; height: 40px; line-height: 40px;">2</div>
                            <div>
                                <h6 class="mb-1">Chụp ảnh bằng chứng</h6>
                                <p class="mb-0 text-muted">
                                    Chụp ảnh sản phẩm lỗi, hộp đựng và gửi cho chúng tôi. 
                                    Bao bì cần còn nguyên vẹn.
                                </p>
                            </div>
                        </div>
                        <div class="d-flex mb-4">
                            <div class="badge bg-primary rounded-circle me-3" style="width: 40px; height: 40px; line-height: 40px;">3</div>
                            <div>
                                <h6 class="mb-1">Xác nhận đổi trả</h6>
                                <p class="mb-0 text-muted">
                                    Bộ phận CSKH sẽ xác nhận và hướng dẫn đổi trả trong vòng 30 phút.
                                </p>
                            </div>
                        </div>
                        <div class="d-flex">
                            <div class="badge bg-success rounded-circle me-3" style="width: 40px; height: 40px; line-height: 40px;">4</div>
                            <div>
                                <h6 class="mb-1">Nhận sản phẩm mới hoặc hoàn tiền</h6>
                                <p class="mb-0 text-muted">
                                    - <strong>Đổi sản phẩm mới:</strong> Giao trong 2-4 giờ (miễn phí)<br>
                                    - <strong>Hoàn tiền:</strong> Chuyển khoản trong 24 giờ
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lưu ý -->
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title" style="color: #8B4513;">
                        <i class="fas fa-exclamation-circle me-2"></i>4. Lưu ý quan trọng
                    </h4>
                    <hr>
                    <ul>
                        <li><strong>Chi phí vận chuyển:</strong> Miễn phí (nếu lỗi do Nguyễn Sơn Bakery)</li>
                        <li><strong>Hình thức bồi thường:</strong> Đổi sản phẩm mới hoặc hoàn tiền 100%</li>
                        <li><strong>Bánh sinh nhật:</strong> Nếu lỗi, chúng tôi làm lại miễn phí + tặng voucher 100k</li>
                        <li><strong>Bảo quản:</strong> Giữ nguyên bao bì, hộp đựng để thuận tiện đổi trả</li>
                    </ul>

                    <div class="alert alert-info mt-3">
                        <h6 class="alert-heading">Cần hỗ trợ?</h6>
                        <p class="mb-0">
                            <i class="fas fa-phone me-2"></i><strong>Hotline: 02438222228</strong> (8:00 - 21:00)<br>
                            <i class="fas fa-envelope me-2"></i><strong>Email: info@nguyenson.vn</strong><br>
                            <i class="fab fa-facebook-messenger me-2"></i><strong>Messenger: m.me/nguyensonbakery</strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/footer.php'; ?>
