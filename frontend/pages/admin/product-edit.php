<?php
ob_start(); // ✅ THÊM: Bật output buffering
$pageTitle = 'Chỉnh sửa sản phẩm';
require_once 'includes/header.php';
require_once '../../../backend/config/database.php';

$database = new Database();
$db = $database->getConnection();

$productId = (int)($_GET['id'] ?? 0);
if ($productId <= 0) {
    Session::setFlash('error', 'Sản phẩm không tồn tại');
    ob_end_clean();
    header('Location: products.php');
    exit();
}

// Lấy thông tin sản phẩm
$stmt = $db->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute([':id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    Session::setFlash('error', 'Sản phẩm không tồn tại');
    ob_end_clean();
    header('Location: products.php');
    exit();
}

// Lấy danh sách categories
$categories = $db->query("SELECT * FROM categories WHERE status = 1 ORDER BY name")->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $categoryId = (int)$_POST['category_id'];
    $price = (float)$_POST['price'];
    $salePrice = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
    $stock = (int)$_POST['stock'];
    $description = trim($_POST['description']);
    $ingredients = trim($_POST['ingredients']);
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isNew = isset($_POST['is_new']) ? 1 : 0;
    $status = isset($_POST['status']) ? 1 : 0;
    
    // Validation
    if (empty($name)) $errors['name'] = 'Tên sản phẩm là bắt buộc';
    if ($categoryId <= 0) $errors['category_id'] = 'Vui lòng chọn danh mục';
    if ($price <= 0) $errors['price'] = 'Giá phải lớn hơn 0';
    
    // Upload image mới (nếu có)
    $imageName = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $fileType = $_FILES['image']['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            $errors['image'] = 'Chỉ chấp nhận file ảnh JPG, PNG';
        } elseif ($_FILES['image']['size'] > MAX_FILE_SIZE) {
            $errors['image'] = 'Kích thước file không được vượt quá 5MB';
        } else {
            // Xóa ảnh cũ
            if ($product['image'] && file_exists(UPLOAD_PATH . 'products/' . $product['image'])) {
                unlink(UPLOAD_PATH . 'products/' . $product['image']);
            }
            
            $imageName = time() . '_' . $_FILES['image']['name'];
            $uploadPath = UPLOAD_PATH . 'products/' . $imageName;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $errors['image'] = 'Lỗi upload ảnh';
            }
        }
    }
    
    if (empty($errors)) {
        $slug = createSlug($name);
        
        $query = "UPDATE products 
                  SET category_id = :category_id, name = :name, slug = :slug, 
                      description = :description, ingredients = :ingredients, 
                      price = :price, sale_price = :sale_price, image = :image, 
                      stock = :stock, is_featured = :is_featured, is_new = :is_new, status = :status 
                  WHERE id = :id";
        
        $stmt = $db->prepare($query);
        $result = $stmt->execute([
            ':category_id' => $categoryId,
            ':name' => $name,
            ':slug' => $slug,
            ':description' => $description,
            ':ingredients' => $ingredients,
            ':price' => $price,
            ':sale_price' => $salePrice,
            ':image' => $imageName,
            ':stock' => $stock,
            ':is_featured' => $isFeatured,
            ':is_new' => $isNew,
            ':status' => $status,
            ':id' => $productId
        ]);
        
        if ($result) {
            Session::setFlash('success', 'Cập nhật sản phẩm thành công!');
            ob_end_clean(); // ✅ THÊM: Xóa buffer
            header('Location: products.php');
            exit();
        } else {
            $errors['general'] = 'Có lỗi xảy ra, vui lòng thử lại';
        }
    } else {
        // Cập nhật dữ liệu để hiển thị lại form
        $product = array_merge($product, $_POST);
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Chỉnh sửa sản phẩm: <?= e($product['name']) ?></h2>
        <a href="products.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
    
    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i><?= e($errors['general']) ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data" class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                               value="<?= e($product['name']) ?>" required>
                        <?php if (isset($errors['name'])): ?>
                            <div class="invalid-feedback"><?= e($errors['name']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>" required>
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                    <?= e($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['category_id'])): ?>
                            <div class="invalid-feedback"><?= e($errors['category_id']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Giá <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control <?= isset($errors['price']) ? 'is-invalid' : '' ?>" 
                                   value="<?= e($product['price']) ?>" step="0.01" required>
                            <?php if (isset($errors['price'])): ?>
                                <div class="invalid-feedback"><?= e($errors['price']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Giá khuyến mãi</label>
                            <input type="number" name="sale_price" class="form-control" 
                                   value="<?= e($product['sale_price'] ?? '') ?>" step="0.01">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Số lượng</label>
                            <input type="number" name="stock" class="form-control" 
                                   value="<?= e($product['stock']) ?>" min="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea name="description" class="form-control" rows="4"><?= e($product['description']) ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Thành phần</label>
                        <textarea name="ingredients" class="form-control" rows="3"><?= e($product['ingredients']) ?></textarea>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Ảnh sản phẩm</label>
                        <?php if ($product['image']): ?>
                            <div class="mb-2">
                                <img src="<?= url('storage/uploads/products/' . $product['image']) ?>" 
                                     alt="<?= e($product['name']) ?>" 
                                     class="img-fluid rounded"
                                     id="currentImage">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image" class="form-control <?= isset($errors['image']) ? 'is-invalid' : '' ?>" 
                               accept="image/*" onchange="previewImage(this)">
                        <small class="text-muted">Để trống nếu không muốn thay đổi ảnh</small>
                        <?php if (isset($errors['image'])): ?>
                            <div class="invalid-feedback"><?= e($errors['image']) ?></div>
                        <?php endif; ?>
                        <div class="mt-2">
                            <img id="preview" src="" alt="" style="max-width: 100%; display: none;">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tùy chọn</label>
                        <div class="form-check">
                            <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured"
                                   <?= $product['is_featured'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_featured">Sản phẩm nổi bật</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_new" class="form-check-input" id="is_new"
                                   <?= $product['is_new'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_new">Sản phẩm mới</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="status" class="form-check-input" id="status"
                                   <?= $product['status'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="status">Hiển thị</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-footer text-end">
            <a href="products.php" class="btn btn-secondary me-2">
                <i class="fas fa-times me-2"></i>Hủy
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Cập nhật
            </button>
        </div>
    </form>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('preview');
    const currentImage = document.getElementById('currentImage');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            if (currentImage) currentImage.style.display = 'none';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>