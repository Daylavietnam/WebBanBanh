<?php
require_once '../../backend/config/config.php';
require_once '../../backend/config/database.php';

$pageTitle = 'Chính sách bảo mật & Điều khoản sử dụng';
require_once '../components/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('frontend/pages/index.php') ?>">Trang chủ</a></li>
            <li class="breadcrumb-item active">Chính sách bảo mật & Điều khoản sử dụng</li>
        </ol>
    </div>
</nav>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <h1 class="mb-4" style="color: #8B4513;">Chính sách bảo mật & Điều khoản sử dụng</h1>
            
            <!-- Chính sách bảo mật -->
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="card-title" style="color: #8B4513;">
                        <i class="fas fa-shield-alt me-2"></i>Chính sách bảo mật thông tin
                    </h3>
                    <hr>
                    
                    <h5 class="mt-4">1. Mục đích thu thập thông tin</h5>
                    <p>Nguyễn Sơn Bakery thu thập thông tin khách hàng nhằm:</p>
                    <ul>
                        <li>Xử lý đơn hàng và giao hàng chính xác</li>
                        <li>Cung cấp dịch vụ chăm sóc khách hàng tốt nhất</li>
                        <li>Thông báo về chương trình khuyến mãi, sản phẩm mới</li>
                        <li>Cải thiện chất lượng dịch vụ</li>
                    </ul>

                    <h5 class="mt-4">2. Phạm vi sử dụng thông tin</h5>
                    <p>Thông tin khách hàng được sử dụng trong phạm vi:</p>
                    <ul>
                        <li>Xác nhận và xử lý đơn hàng</li>
                        <li>Liên hệ giao hàng và hỗ trợ khách hàng</li>
                        <li>Gửi thông tin khuyến mãi (nếu khách hàng đăng ký)</li>
                        <li>Phân tích và cải thiện dịch vụ</li>
                    </ul>

                    <h5 class="mt-4">3. Cam kết bảo mật</h5>
                    <p>Chúng tôi cam kết:</p>
                    <ul>
                        <li><strong>Không</strong> chia sẻ thông tin cá nhân cho bên thứ ba khi chưa có sự đồng ý</li>
                        <li>Bảo vệ thông tin khách hàng bằng các biện pháp bảo mật kỹ thuật</li>
                        <li>Chỉ sử dụng thông tin đúng mục đích đã nêu</li>
                        <li>Xóa thông tin khi khách hàng yêu cầu (theo quy định pháp luật)</li>
                    </ul>

                    <h5 class="mt-4">4. Quyền của khách hàng</h5>
                    <p>Khách hàng có quyền:</p>
                    <ul>
                        <li>Yêu cầu xem, chỉnh sửa hoặc xóa thông tin cá nhân</li>
                        <li>Từ chối nhận email quảng cáo bất cứ lúc nào</li>
                        <li>Khiếu nại về việc lộ thông tin cá nhân do lỗi của chúng tôi</li>
                    </ul>

                    <p class="mt-4 text-muted">
                        <i class="fas fa-info-circle me-2"></i>
                        Liên hệ: <strong>info@nguyenson.vn</strong> hoặc <strong>02438222228</strong> nếu có thắc mắc.
                    </p>
                </div>
            </div>

            <!-- Điều khoản sử dụng -->
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title" style="color: #8B4513;">
                        <i class="fas fa-file-contract me-2"></i>Điều khoản sử dụng
                    </h3>
                    <hr>

                    <h5 class="mt-4">1. Quy định chung</h5>
                    <p>Khi sử dụng website và dịch vụ của Nguyễn Sơn Bakery, quý khách đồng ý tuân thủ các điều khoản sau:</p>
                    <ul>
                        <li>Cung cấp thông tin chính xác, đầy đủ khi đặt hàng</li>
                        <li>Không sử dụng website cho mục đích bất hợp pháp</li>
                        <li>Tôn trọng quyền sở hữu trí tuệ của Nguyễn Sơn Bakery</li>
                    </ul>

                    <h5 class="mt-4">2. Đặt hàng và thanh toán</h5>
                    <ul>
                        <li>Đơn hàng được xác nhận qua điện thoại hoặc email</li>
                        <li>Giá sản phẩm có thể thay đổi mà không cần báo trước</li>
                        <li>Thanh toán khi nhận hàng (COD) hoặc chuyển khoản</li>
                    </ul>

                    <h5 class="mt-4">3. Hủy đơn hàng</h5>
                    <ul>
                        <li>Khách hàng có thể hủy đơn hàng trước khi đơn được xử lý</li>
                        <li>Đơn hàng đã xử lý không thể hủy (chỉ đổi trả theo chính sách)</li>
                    </ul>

                    <h5 class="mt-4">4. Trách nhiệm</h5>
                    <ul>
                        <li>Nguyễn Sơn Bakery không chịu trách nhiệm về các thiệt hại gián tiếp</li>
                        <li>Chúng tôi cam kết bồi thường nếu sản phẩm có vấn đề do lỗi của chúng tôi</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/footer.php'; ?>
