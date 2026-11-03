    </main>

    <!-- Footer -->
    <footer class="footer bg-dark text-white pt-5 pb-3 mt-5">
        <div class="container">
            <div class="row">
                <!-- About -->
                <div class="col-md-4 mb-4">
                    <h5 class="text-uppercase mb-3">Nguyễn Sơn Bakery</h5>
                    <p class="small">
                        Xuất thân trong gia đình có nghề làm bánh mỳ truyền thống, 
                        Chef Nguyễn Sơn cũng có thời gian dài làm việc tại Công ty Bodega 
                        rồi Sofitel Metropole.
                    </p>
                    <div class="social-links mt-3">
                        <a href="https://www.facebook.com/NguyenSonBakery" target="_blank" class="text-white me-3">
                            <i class="fab fa-facebook fa-2x"></i>
                        </a>
                        <a href="#" class="text-white me-3">
                            <i class="fab fa-instagram fa-2x"></i>
                        </a>
                        <a href="#" class="text-white">
                            <i class="fab fa-youtube fa-2x"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-md-2 mb-4">
                    <h5 class="text-uppercase mb-3">Liên kết</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?= url('frontend/pages/index.php') ?>" class="text-white-50 text-decoration-none">Trang chủ</a></li>
                        <li><a href="<?= url('frontend/pages/products/list.php') ?>" class="text-white-50 text-decoration-none">Sản phẩm</a></li>
                        <li><a href="<?= url('frontend/pages/about.php') ?>" class="text-white-50 text-decoration-none">Giới thiệu</a></li>
                        <li><a href="<?= url('frontend/pages/news/list.php') ?>" class="text-white-50 text-decoration-none">Tin tức</a></li>
                        <li><a href="<?= url('frontend/pages/contact.php') ?>" class="text-white-50 text-decoration-none">Liên hệ</a></li>
                    </ul>
                </div>

                <!-- Policies -->
                <div class="col-md-3 mb-4">
                    <h5 class="text-uppercase mb-3">Chính sách</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?= url('frontend/pages/privacy-policy.php') ?>" class="text-white-50 text-decoration-none">Bảo mật & Điều khoản</a></li>
                        <li><a href="<?= url('frontend/pages/food-safety-policy.php') ?>" class="text-white-50 text-decoration-none">Chất lượng ATTP</a></li>
                        <li><a href="<?= url('frontend/pages/payment-policy.php') ?>" class="text-white-50 text-decoration-none">Thanh toán</a></li>
                        <li><a href="<?= url('frontend/pages/return-policy.php') ?>" class="text-white-50 text-decoration-none">Đổi trả</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="col-md-3 mb-4">
                    <h5 class="text-uppercase mb-3">Liên hệ</h5>
                    <ul class="list-unstyled small">
                        <li class="mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            Số 15, hẻm 75 ngách 51, ngõ Linh Quang,<br>
                            phường Văn Miếu - Quốc Tử Giám, Hà Nội
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-phone me-2"></i>
                            <a href="tel:02438222228" class="text-white-50 text-decoration-none">02438222228</a>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            <a href="mailto:info@nguyenson.vn" class="text-white-50 text-decoration-none">info@nguyenson.vn</a>
                        </li>
                    </ul>
                </div>
            </div>

            <hr class="bg-secondary">

            <div class="row">
                <div class="col-md-12 text-center">
                    <p class="small mb-0">
                        &copy; <?= date('Y') ?> Nguyễn Sơn Bakery. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="backToTop" class="btn btn-brown back-to-top" style="display: none;">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?= url('frontend/assets/js/main.js') ?>"></script>
    
    <?php if(isset($customJS)): ?>
        <script src="<?= url('frontend/assets/js/' . $customJS) ?>"></script>
    <?php endif; ?>
</body>
</html>
<?php ob_end_flush(); ?>