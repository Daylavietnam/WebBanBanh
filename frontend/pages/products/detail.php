<?php
require_once '../../../backend/config/database.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get product slug from URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (empty($slug)) {
    header('Location: ' . url('frontend/pages/products/list.php'));
    exit();
}

// Get product details
$query = "SELECT p.*, c.name as category_name, c.slug as category_slug
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.slug = :slug AND p.status = 1";
$stmt = $db->prepare($query);
$stmt->bindParam(':slug', $slug);
$stmt->execute();
$product = $stmt->fetch();

// If product not found
if (!$product) {
    header('Location: ' . url('frontend/pages/products/list.php'));
    exit();
}

// Update view count
$updateViewQuery = "UPDATE products SET views = views + 1 WHERE id = :id";
$updateViewStmt = $db->prepare($updateViewQuery);
$updateViewStmt->bindParam(':id', $product['id']);
$updateViewStmt->execute();

// Get related products (same category)
$relatedQuery = "SELECT p.*, c.name as category_name 
                 FROM products p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 WHERE p.category_id = :category_id 
                 AND p.id != :product_id 
                 AND p.status = 1 
                 LIMIT 4";
$relatedStmt = $db->prepare($relatedQuery);
$relatedStmt->bindParam(':category_id', $product['category_id']);
$relatedStmt->bindParam(':product_id', $product['id']);
$relatedStmt->execute();
$relatedProducts = $relatedStmt->fetchAll();

// Calculate discount percentage
$discountPercent = 0;
if ($product['sale_price'] && $product['sale_price'] > 0) {
    $discountPercent = round((($product['price'] - $product['sale_price']) / $product['price']) * 100);
}

$pageTitle = $product['name'];
require_once '../../components/header.php';
?>

<!-- Breadcrumb -->
<section class="breadcrumb-section py-3 bg-light">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('frontend/pages/index.php') ?>">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="<?= url('frontend/pages/products/list.php') ?>">Sản phẩm</a></li>
                <li class="breadcrumb-item">
                    <a href="<?= url('frontend/pages/products/list.php?category=' . $product['category_slug']) ?>">
                        <?= e($product['category_name']) ?>
                    </a>
                </li>
                <li class="breadcrumb-item active"><?= e($product['name']) ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- Product Detail -->
<section class="product-detail py-5">
    <div class="container">
        <div class="row">
            <!-- Product Images -->
            <div class="col-lg-5 mb-4">
                <div class="product-image-container">
                    <img src="<?= url('storage/uploads/products/' . $product['image']) ?>" 
                         alt="<?= e($product['name']) ?>" 
                         class="img-fluid rounded shadow"
                         id="mainImage"
                         onerror="this.src='<?= url('frontend/assets/images/no-image.png') ?>'">
                    
                    <?php if ($discountPercent > 0): ?>
                        <span class="badge bg-danger position-absolute top-0 end-0 m-3 fs-6">
                            -<?= $discountPercent ?>%
                        </span>
                    <?php endif; ?>
                </div>
                
                <!-- Gallery thumbnails (if you implement gallery later) -->
                <div class="product-gallery mt-3">
                    <div class="row g-2">
                        <div class="col-3">
                            <img src="<?= url('storage/uploads/products/' . $product['image']) ?>" 
                                 class="img-fluid rounded border" 
                                 style="cursor: pointer;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Info -->
            <div class="col-lg-7">
                <div class="product-info">
                    <h1 class="product-title mb-3"><?= e($product['name']) ?></h1>
                    
                    <!-- Category & SKU -->
                    <div class="mb-3">
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-tag me-1"></i>
                            <?= e($product['category_name']) ?>
                        </span>
                        <span class="text-muted ms-3">
                            <i class="fas fa-eye me-1"></i>
                            <?= number_format($product['views']) ?> lượt xem
                        </span>
                    </div>

                    <!-- Price -->
                    <div class="product-price mb-4">
                        <?php if ($product['sale_price'] && $product['sale_price'] > 0): ?>
                            <h2 class="text-danger mb-2">
                                <?= formatCurrency($product['sale_price']) ?>
                            </h2>
                            <p class="text-muted text-decoration-line-through mb-0">
                                <?= formatCurrency($product['price']) ?>
                            </p>
                        <?php else: ?>
                            <h2 class="text-brown mb-2">
                                <?= formatCurrency($product['price']) ?>
                            </h2>
                        <?php endif; ?>
                    </div>

                    <!-- Description -->
                    <div class="product-description mb-4">
                        <h5>Mô tả sản phẩm</h5>
                        <p class="text-muted"><?= nl2br(e($product['description'])) ?></p>
                    </div>

                    <!-- Ingredients (if available) -->
                    <?php if (!empty($product['ingredients'])): ?>
                        <div class="product-ingredients mb-4">
                            <h5>Thành phần</h5>
                            <p class="text-muted"><?= nl2br(e($product['ingredients'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Stock Status -->
                    <div class="stock-status mb-4">
                        <?php if ($product['stock'] > 0): ?>
                            <span class="badge bg-success">
                                <i class="fas fa-check-circle me-1"></i>
                                Còn hàng (<?= $product['stock'] ?> sản phẩm)
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger">
                                <i class="fas fa-times-circle me-1"></i>
                                Hết hàng
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Quantity & Add to Cart -->
                    <div class="product-actions">
                        <div class="row g-3">
                            <div class="col-auto">
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary" type="button" id="decreaseQty">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" class="form-control text-center" 
                                           id="quantity" value="1" min="1" max="<?= $product['stock'] ?>" 
                                           style="width: 80px;">
                                    <button class="btn btn-outline-secondary" type="button" id="increaseQty">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col">
                                <button class="btn btn-brown btn-lg w-100 btn-add-to-cart" 
                                        data-product-id="<?= $product['id'] ?>"
                                        data-product-name="<?= e($product['name']) ?>"
                                        data-quantity="1"
                                        <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                                    <i class="fas fa-shopping-cart me-2"></i>
                                    Thêm vào giỏ hàng
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="additional-info mt-4 p-3 bg-light rounded">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-truck text-brown me-2"></i>
                                Giao hàng nhanh trong 2-4 giờ
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-shield-alt text-brown me-2"></i>
                                Đảm bảo chất lượng 100%
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-phone text-brown me-2"></i>
                                Hỗ trợ: 02438222228
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (count($relatedProducts) > 0): ?>
            <section class="related-products mt-5 pt-5 border-top">
                <h3 class="mb-4">Sản phẩm liên quan</h3>
                <div class="row g-4">
                    <?php foreach ($relatedProducts as $product): ?>
                        <div class="col-lg-3 col-md-6">
                            <?php include '../../components/product-card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</section>

<!-- JavaScript for quantity controls -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const qtyInput = document.getElementById('quantity');
    const decreaseBtn = document.getElementById('decreaseQty');
    const increaseBtn = document.getElementById('increaseQty');
    const addToCartBtn = document.querySelector('.btn-add-to-cart');
    const maxQty = parseInt(qtyInput.getAttribute('max'));

    // Giảm số lượng
    decreaseBtn.addEventListener('click', function() {
        let currentValue = parseInt(qtyInput.value);
        if (currentValue > 1) {
            qtyInput.value = currentValue - 1;
            // ✅ Cập nhật data-quantity
            if (addToCartBtn) {
                addToCartBtn.setAttribute('data-quantity', qtyInput.value);
            }
        }
    });

    // Tăng số lượng
    increaseBtn.addEventListener('click', function() {
        let currentValue = parseInt(qtyInput.value);
        if (currentValue < maxQty) {
            qtyInput.value = currentValue + 1;
            // ✅ Cập nhật data-quantity
            if (addToCartBtn) {
                addToCartBtn.setAttribute('data-quantity', qtyInput.value);
            }
        }
    });

    // ✅ Cập nhật data-quantity khi input thay đổi trực tiếp
    qtyInput.addEventListener('change', function() {
        if (addToCartBtn) {
            addToCartBtn.setAttribute('data-quantity', this.value);
        }
    });
});
</script>

<?php require_once '../../components/footer.php'; ?>
