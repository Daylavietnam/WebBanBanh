<?php
ob_start(); // ✅ THÊM: Bật output buffering
$pageTitle = 'Quản lý sản phẩm';
require_once 'includes/header.php';
require_once '../../../backend/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Xóa sản phẩm
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Lấy thông tin sản phẩm để xóa ảnh
    $stmt = $db->prepare("SELECT image FROM products WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch();
    
    // Xóa ảnh nếu có
    if ($product && $product['image']) {
        $imagePath = UPLOAD_PATH . 'products/' . $product['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    
    // Xóa sản phẩm
    $stmt = $db->prepare("DELETE FROM products WHERE id = :id");
    $stmt->execute([':id' => $id]);
    
    Session::setFlash('success', 'Xóa sản phẩm thành công!');
    ob_end_clean(); // ✅ THÊM: Xóa buffer
    header('Location: products.php');
    exit();
}

// Lấy danh sách sản phẩm
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$where = "1=1";
$params = [];

if ($search) {
    $where .= " AND p.name LIKE :search";
    $params[':search'] = "%$search%";
}

if ($category) {
    $where .= " AND c.id = :category";
    $params[':category'] = $category;
}

$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE $where 
          ORDER BY p.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Lấy danh mục để filter
$categories = $db->query("SELECT * FROM categories WHERE status = 1 ORDER BY name")->fetchAll();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Quản lý sản phẩm</h2>
        <a href="product-add.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Thêm sản phẩm
        </a>
    </div>
    
    <!-- Search & Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Tìm kiếm sản phẩm..." 
                           value="<?= e($search) ?>">
                </div>
                <div class="col-md-4">
                    <select name="category" class="form-select">
                        <option value="">Tất cả danh mục</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Lọc
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Products Table -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($products)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>Không có sản phẩm nào.
                </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="80">Ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Giá</th>
                            <th>Giá KM</th>
                            <th>Tồn kho</th>
                            <th>Trạng thái</th>
                            <th width="120">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <?php if ($product['image']): ?>
                                    <img src="<?= url('storage/uploads/products/' . $product['image']) ?>" 
                                         alt="<?= e($product['name']) ?>" 
                                         class="img-thumbnail" 
                                         style="width: 60px; height: 60px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light text-center" style="width: 60px; height: 60px; line-height: 60px;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= e($product['name']) ?></strong>
                                <?php if ($product['is_featured']): ?>
                                    <span class="badge bg-warning text-dark ms-2">Nổi bật</span>
                                <?php endif; ?>
                                <?php if ($product['is_new']): ?>
                                    <span class="badge bg-info ms-2">Mới</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($product['category_name']) ?></td>
                            <td><?= formatCurrency($product['price']) ?></td>
                            <td>
                                <?php if ($product['sale_price']): ?>
                                    <span class="text-danger"><?= formatCurrency($product['sale_price']) ?></span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($product['stock'] > 0): ?>
                                    <span class="badge bg-success"><?= $product['stock'] ?></span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Hết hàng</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($product['status']): ?>
                                    <span class="badge bg-success">Hoạt động</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Ẩn</span>
                                <?php endif; ?>
                            </td>
                            <td class="table-actions">
                                <a href="product-edit.php?id=<?= $product['id'] ?>" 
                                   class="btn btn-sm btn-warning" 
                                   title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?delete=<?= $product['id'] ?>" 
                                   class="btn btn-sm btn-danger" 
                                   title="Xóa"
                                   onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>