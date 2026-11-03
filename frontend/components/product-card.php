<div class="product-card card h-100 border-0 shadow-sm hover-shadow">
    <?php if(isset($product['sale_price']) && $product['sale_price'] > 0): ?>
        <span class="badge bg-danger position-absolute top-0 end-0 m-2">SALE</span>
    <?php endif; ?>
    
    <?php if(isset($product['is_new']) && $product['is_new']): ?>
        <span class="badge bg-success position-absolute top-0 start-0 m-2">MỚI</span>
    <?php endif; ?>
    
    <a href="<?= url('frontend/pages/products/detail.php?slug=' . $product['slug']) ?>">
        <img src="<?= url('storage/uploads/products/' . ($product['image'] ?? 'default.jpg')) ?>" 
             class="card-img-top" 
             alt="<?= e($product['name']) ?>"
             onerror="this.src='<?= url('frontend/assets/images/no-image.png') ?>'">
    </a>
    
    <div class="card-body d-flex flex-column">
        <p class="text-muted small mb-1"><?= e($product['category_name'] ?? '') ?></p>
        
        <h5 class="card-title">
            <a href="<?= url('frontend/pages/products/detail.php?slug=' . $product['slug']) ?>" 
               class="text-decoration-none text-dark">
                <?= e($product['name']) ?>
            </a>
        </h5>
        
        <div class="mt-auto">
            <div class="price mb-3">
                <?php if(isset($product['sale_price']) && $product['sale_price'] > 0): ?>
                    <span class="text-danger fw-bold fs-5"><?= formatCurrency($product['sale_price']) ?></span>
                    <span class="text-muted text-decoration-line-through ms-2"><?= formatCurrency($product['price']) ?></span>
                <?php else: ?>
                    <span class="text-brown fw-bold fs-5"><?= formatCurrency($product['price']) ?></span>
                <?php endif; ?>
            </div>
            
            <div class="d-grid">
                <button class="btn btn-brown btn-add-to-cart" 
                        data-product-id="<?= $product['id'] ?>"
                        data-product-name="<?= e($product['name']) ?>">
                    <i class="fas fa-shopping-cart me-2"></i>Thêm vào giỏ
                </button>
            </div>
        </div>
    </div>
</div>
