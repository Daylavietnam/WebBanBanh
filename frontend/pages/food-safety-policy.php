<?php
require_once '../../backend/config/config.php';
require_once '../../backend/config/database.php';

$pageTitle = 'Chính sách chất lượng ATTP';
require_once '../components/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('frontend/pages/index.php') ?>">Trang chủ</a></li>
            <li class="breadcrumb-item active">Chính sách chất lượng ATTP</li>
        </ol>
    </div>
</nav>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <h1 class="mb-4" style="color: #8B4513;">Chính sách chất lượng ATTP</h1>
            
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-success">
                        <i class="fas fa-certificate fa-2x float-start me-3"></i>
                        <strong>Cam kết ATTP:</strong> Nguyễn Sơn Bakery cam kết tuân thủ 100% các quy định về 
                        An toàn Thực phẩm (ATTP) của Bộ Y tế và các cơ quan quản lý nhà nước.
                    </div>

                    <h4 class="mt-4" style="color: #8B4513;">
                        <i class="fas fa-check-circle me-2"></i>1. Nguồn gốc nguyên liệu
                    </h4>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <i class="fas fa-leaf text-success me-2"></i>
                            Bột mì, bơ, trứng, sữa được nhập khẩu từ các nhà cung cấp uy tín (Pháp, Úc, New Zealand)
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-leaf text-success me-2"></i>
                            Trái cây tươi được lấy từ các trang trại có chứng nhận VietGAP
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-leaf text-success me-2"></i>
                            Tất cả nguyên liệu đều có giấy kiểm định an toàn thực phẩm
                        </li>
                    </ul>

                    <h4 class="mt-4" style="color: #8B4513;">
                        <i class="fas fa-industry me-2"></i>2. Quy trình sản xuất
                    </h4>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card mb-3 border-success">
                                <div class="card-body">
                                    <h6 class="card-title text-success">
                                        <i class="fas fa-hands-wash me-2"></i>Vệ sinh
                                    </h6>
                                    <p class="card-text small">
                                        Nhân viên đeo khẩu trang, mũ, găng tay. 
                                        Xưởng sản xuất được vệ sinh, khử trùng hàng ngày.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3 border-success">
                                <div class="card-body">
                                    <h6 class="card-title text-success">
                                        <i class="fas fa-thermometer-half me-2"></i>Nhiệt độ
                                    </h6>
                                    <p class="card-text small">
                                        Nguyên liệu được bảo quản ở nhiệt độ quy định. 
                                        Bánh nướng đạt chuẩn nhiệt độ nội tâm.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3 border-success">
                                <div class="card-body">
                                    <h6 class="card-title text-success">
                                        <i class="fas fa-clock me-2"></i>Thời gian
                                    </h6>
                                    <p class="card-text small">
                                        Bánh được làm mới mỗi ngày, không sử dụng 
                                        hương liệu, chất bảo quản công nghiệp.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3 border-success">
                                <div class="card-body">
                                    <h6 class="card-title text-success">
                                        <i class="fas fa-clipboard-check me-2"></i>Kiểm tra
                                    </h6>
                                    <p class="card-text small">
                                        Mỗi mẻ bánh đều được kiểm tra chất lượng 
                                        trước khi đóng gói và giao hàng.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h4 class="mt-4" style="color: #8B4513;">
                        <i class="fas fa-box me-2"></i>3. Bao bì và bảo quản
                    </h4>
                    <ul>
                        <li>Bao bì an toàn, không chứa BPA, có tem nhãn rõ ràng</li>
                        <li>Ghi rõ hạn sử dụng, thành phần, hướng dẫn bảo quản</li>
                        <li>Bánh sinh nhật được đựng trong hộp giấy chuyên dụng, kèm túi giữ lạnh</li>
                    </ul>

                    <h4 class="mt-4" style="color: #8B4513;">
                        <i class="fas fa-shipping-fast me-2"></i>4. Giao hàng
                    </h4>
                    <ul>
                        <li>Shipper được đào tạo về vệ sinh ATTP</li>
                        <li>Phương tiện giao hàng sạch sẽ, có thùng giữ nhiệt</li>
                        <li>Bánh được kiểm tra kỹ trước khi giao cho khách</li>
                    </ul>

                    <div class="alert alert-info mt-4">
                        <h5 class="alert-heading">
                            <i class="fas fa-phone me-2"></i>Khiếu nại về chất lượng?
                        </h5>
                        <p class="mb-0">
                            Liên hệ ngay: <strong>Hotline: 02438222228</strong> hoặc 
                            <strong>Email: info@nguyenson.vn</strong>
                        </p>
                        <small class="text-muted">
                            Chúng tôi cam kết đổi trả hoặc hoàn tiền 100% nếu sản phẩm có vấn đề về chất lượng.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/footer.php'; ?>
