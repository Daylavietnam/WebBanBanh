<?php
require_once '../../backend/config/config.php';
require_once '../../backend/config/database.php';

$pageTitle = 'Giới thiệu';
require_once '../components/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('frontend/pages/index.php') ?>">Trang chủ</a></li>
            <li class="breadcrumb-item active">Giới thiệu</li>
        </ol>
    </div>
</nav>

<!-- About Content -->
<div class="container my-5">
    <!-- Page Title -->
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h1 class="mb-3" style="color: #8B4513; font-weight: 600;">Giới thiệu</h1>
            <div style="width: 80px; height: 3px; background-color: #8B4513; margin: 0 auto;"></div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row mb-5">
        <!-- Image - 25% width -->
        <div class="col-md-3 mb-4">
            <img src="<?= url('frontend/assets/images/about-1.jpg') ?>" 
                 alt="Nguyễn Sơn Bakery" 
                 class="img-fluid rounded shadow-sm">
        </div>
        
        <!-- Text - 75% width -->
        <div class="col-md-9">
            <div style="color: #555; line-height: 1.9; text-align: justify; font-size: 15px;">
                <p class="mb-3">
                    Có lẽ những người yêu thích bánh ngọt, đặc biệt là bánh được làm theo phong cách Pháp không xa lạ gì với thương hiệu Nguyễn Sơn Bakery.
                </p>
                
                <p class="mb-3">
                    Mỗi chiếc bánh ở Nguyễn Sơn Bakery lại mang một vẻ riêng, từ hương vị đến cách trang trí. Hình thức giản dị chỉ với hai màu đen trắng làm chủ đạo nhưng chất lượng nhờ cách làm tinh tế và tỉ mỉ. Bánh có vị ngọt không quá đậm, vị béo thì thanh nên không gây cảm giác ngán cho người thưởng thức. Cũng rất hiếm khi tìm thấy sự trùng lặp trong các loại bánh ở Nguyễn Sơn Bakery vì tất cả chúng, từ bánh mì, bánh ngọt, bánh quy đều được làm 100% hand-made.
                </p>
                
                <p class="mb-3">
                    Hơn nữa, ông chủ của tiệm bánh, Chef Nguyễn Sơn, cũng là người khá khó tính trong việc lựa chọn nguyên liệu cho các sản phẩm của cửa hàng.
                </p>
                
                <p class="mb-3">
                    Xuất thân trong gia đình có nghề làm bánh mỳ truyền thống, Chef Nguyễn Sơn cũng có thời gian dài làm việc tại Công ty Bodega rồi Sofitel Metropole. Anh có hơn 10 năm kinh nghiệm làm Chef bánh tại khách sạn danh tiếng Sofitel Metropole Legende Hanoi.
                </p>
                
                <p class="mb-3">
                    Và cũng chính ông chủ Nguyễn Sơn vẫn tự tay làm ra những chiếc bánh ngọt độc đáo. Bên cạnh việc kinh doanh, với ông chủ trẻ này thì làm bánh là một nghệ thuật đầy sáng tạo, được thể hiện cầu kỳ và nghiêm ngặt từ khâu chế biến cho đến việc trang trí, trình bày các họa tiết. Mỗi chiếc bánh được anh làm ra đều thỏa mãn hai ước mơ: nghệ thuật và kinh doanh.
                </p>
                
                <p class="mb-0">
                    Đến nay Nguyễn Sơn Bakery đã phát triển với một chuỗi cửa hàng tại Hà Nội, Hải Phòng, Bắc Ninh, Hưng Yên. Mỗi nơi có một phong cách, một ấn tượng riêng nhưng tất cả đều hướng tới một điều là chất lượng và trang nhã.
                </p>
            </div>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-award fa-3x" style="color: #8B4513;"></i>
                </div>
                <h5 style="color: #8B4513; font-weight: 600;">Hơn 20 năm kinh nghiệm</h5>
                <p class="small mb-0" style="color: #666;">
                    Thương hiệu bánh Pháp uy tín hàng đầu tại Việt Nam
                </p>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-hands fa-3x" style="color: #8B4513;"></i>
                </div>
                <h5 style="color: #8B4513; font-weight: 600;">100% thủ công</h5>
                <p class="small mb-0" style="color: #666;">
                    Mỗi sản phẩm đều được làm thủ công bởi thợ bánh giàu kinh nghiệm
                </p>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-sm text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-store fa-3x" style="color: #8B4513;"></i>
                </div>
                <h5 style="color: #8B4513; font-weight: 600;">20+ cửa hàng</h5>
                <p class="small mb-0" style="color: #666;">
                    Hệ thống cửa hàng tại Hà Nội, Hải Phòng, Bắc Ninh, Hưng Yên
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once '../components/footer.php'; ?>
