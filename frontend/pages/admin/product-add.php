<?php
ob_start(); // ✅ THÊM: Bật output buffering
$pageTitle = 'Thêm sản phẩm mới';
require_once 'includes/header.php';
require_once '../../../backend/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Lấy danh sách categories
$categories = $db->query("SELECT * FROM categories WHERE status = 1 ORDER BY name")->fetchAll();

$errors = [];
$oldData = [];

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
    
    $oldData = $_POST;
    
    // Validation
    if (empty($name)) $errors['name'] = 'Tên sản phẩm là bắt buộc';
    if ($categoryId <= 0) $errors['category_id'] = 'Vui lòng chọn danh mục';
    if ($price <= 0) $errors['price'] = 'Giá phải lớn hơn 0';
    
    // Upload image
    $imageName = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $fileType = $_FILES['image']['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            $errors['image'] = 'Chỉ chấp nhận file ảnh JPG, PNG';
        } elseif ($_FILES['image']['size'] > MAX_FILE_SIZE) {
            $errors['image'] = 'Kích thước file không được vượt quá 5MB';
        } else {
            $imageName = time() . '_' . $_FILES['image']['name'];
            $uploadPath = UPLOAD_PATH . 'products/' . $imageName;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $errors['image'] = 'Lỗi upload ảnh';
            }
        }
    }
    
    if (empty($errors)) {
        $slug = createSlug($name);
        
        $query = "INSERT INTO products 
                  (category_id, name, slug, description, ingredients, price, sale_price, image, stock, is_featured, is_new, status) 
                  VALUES 
                  (:category_id, :name, :slug, :description, :ingredients, :price, :sale_price, :image, :stock, :is_featured, :is_new, :status)";
        
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
            ':status' => $status
        ]);
        
        if ($result) {
            Session::setFlash('success', 'Thêm sản phẩm thành công!');
            ob_end_clean(); // ✅ THÊM: Xóa output buffer
            header('Location: products.php');
            exit();
        } else {
            $errors['general'] = 'Có lỗi xảy ra, vui lòng thử lại';
        }
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Thêm sản phẩm mới</h2>
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
                               value="<?= e($oldData['name'] ?? '') ?>" required>
                        <?php if (isset($errors['name'])): ?>
                            <div class="invalid-feedback"><?= e($errors['name']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>" required>
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" 
                                    <?= isset($oldData['category_id']) && $oldData['category_id'] == $cat['id'] ? 'selected' : '' ?>>
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
                                   value="<?= e($oldData['price'] ?? '') ?>" step="0.01" required>
                            <?php if (isset($errors['price'])): ?>
                                <div class="invalid-feedback"><?= e($errors['price']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Giá khuyến mãi</label>
                            <input type="number" name="sale_price" class="form-control" 
                                   value="<?= e($oldData['sale_price'] ?? '') ?>" step="0.01">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Số lượng</label>
                            <input type="number" name="stock" class="form-control" 
                                   value="<?= e($oldData['stock'] ?? 0) ?>" min="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea name="description" class="form-control" rows="4"><?= e($oldData['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Thành phần</label>
                        <textarea name="ingredients" class="form-control" rows="3"><?= e($oldData['ingredients'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Ảnh sản phẩm</label>
                        <input type="file" name="image" class="form-control <?= isset($errors['image']) ? 'is-invalid' : '' ?>" 
                               accept="image/*" onchange="previewImage(this)">
                        <?php if (isset($errors['image'])): ?>
                            <div class="invalid-feedback"><?= e($errors['image']) ?></div>
                        <?php endif; ?>
                        <div class="mt-3">
                            <img id="preview" src="" alt="" style="max-width: 100%; display: none;">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tùy chọn</label>
                        <div class="form-check">
                            <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured"
                                   <?= isset($oldData['is_featured']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_featured">Sản phẩm nổi bật</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_new" class="form-check-input" id="is_new"
                                   <?= isset($oldData['is_new']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_new">Sản phẩm mới</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="status" class="form-check-input" id="status" checked>
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
                <i class="fas fa-save me-2"></i>Lưu sản phẩm
            </button>
        </div>
    </form>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
