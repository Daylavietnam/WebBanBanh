<?php
require_once '../../../backend/config/config.php';
require_once '../../../backend/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = PRODUCTS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Filters
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$price = isset($_GET['price']) ? trim($_GET['price']) : '';

// Build WHERE clause with positional parameters
$where = ["p.status = 1"];
$params = [];

if (!empty($category)) {
    $where[] = "c.slug = ?";
    $params[] = $category;
}

if (!empty($search)) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// ✅ Bộ lọc khoảng giá
$priceMin = null;
$priceMax = null;
if (!empty($price) && preg_match('/^(\d+)-(\d+)$/', $price, $matches)) {
    $priceMin = (int)$matches[1];
    $priceMax = (int)$matches[2];
    $where[] = "COALESCE(NULLIF(p.sale_price, 0), p.price) >= ?";
    $params[] = $priceMin;
    $where[] = "COALESCE(NULLIF(p.sale_price, 0), p.price) <= ?";
    $params[] = $priceMax;
}

$whereClause = implode(' AND ', $where);

// Sorting
switch ($sort) {
    case 'price_asc':
        $orderBy = 'COALESCE(NULLIF(p.sale_price, 0), p.price) ASC';
        break;
    case 'price_desc':
        $orderBy = 'COALESCE(NULLIF(p.sale_price, 0), p.price) DESC';
        break;
    case 'name':
        $orderBy = 'p.name ASC';
        break;
    case 'newest':
    default:
        $orderBy = 'p.created_at DESC';
        break;
}

// Count total products — dùng bindValue để đảm bảo đúng kiểu dữ liệu
$countQuery = "SELECT COUNT(*) as total
               FROM products p
               LEFT JOIN categories c ON p.category_id = c.id
               WHERE $whereClause";

$countStmt = $db->prepare($countQuery);
$countIdx = 1;
foreach ($params as $pVal) {
    if (is_int($pVal)) {
        $countStmt->bindValue($countIdx, $pVal, PDO::PARAM_INT);
    } else {
        $countStmt->bindValue($countIdx, $pVal, PDO::PARAM_STR);
    }
    $countIdx++;
}
$countStmt->execute();
$totalProducts = $countStmt->fetch()['total'];
$totalPages = ceil($totalProducts / $limit);

// Get products — dùng bindValue để tránh lỗi LIMIT/OFFSET khi emulate_prepares = false
$query = "SELECT p.*, c.name as category_name, c.slug as category_slug
          FROM products p
          LEFT JOIN categories c ON p.category_id = c.id
          WHERE $whereClause
          ORDER BY $orderBy
          LIMIT ? OFFSET ?";

$stmt = $db->prepare($query);

// Bind tất cả params: các filter trước, LIMIT và OFFSET cuối cùng
$paramIndex = 1;
foreach ($params as $paramValue) {
    if (is_int($paramValue)) {
        $stmt->bindValue($paramIndex, $paramValue, PDO::PARAM_INT);
    } else {
        $stmt->bindValue($paramIndex, $paramValue, PDO::PARAM_STR);
    }
    $paramIndex++;
}
$stmt->bindValue($paramIndex, (int)$limit, PDO::PARAM_INT);
$paramIndex++;
$stmt->bindValue($paramIndex, (int)$offset, PDO::PARAM_INT);

$stmt->execute();
$products = $stmt->fetchAll();

// Get all categories for filter
$categoriesQuery = "SELECT * FROM categories WHERE status = 1 ORDER BY display_order ASC";
$categoriesStmt = $db->prepare($categoriesQuery);
$categoriesStmt->execute();
$categories = $categoriesStmt->fetchAll();

// Get current category name
$categoryName = 'Tất cả sản phẩm';
if (!empty($category)) {
    foreach ($categories as $cat) {
        if ($cat['slug'] === $category) {
            $categoryName = $cat['name'];
            break;
        }
    }
}

// ✅ Helper: tạo URL filter giữ lại các param hiện tại
function buildFilterUrl($overrides = [], $removes = []) {
    $params = $_GET;
    foreach ($removes as $key) {
        unset($params[$key]);
    }
    foreach ($overrides as $key => $value) {
        $params[$key] = $value;
    }
    // Luôn reset về page 1 khi đổi filter
    unset($params['page']);
    $qs = http_build_query($params);
    return url('frontend/pages/products/list.php') . ($qs ? '?' . $qs : '');
}

// Danh sách khoảng giá
$priceRanges = [
    ['value' => '0-20000',       'label' => 'Dưới 20,000₫'],
    ['value' => '20000-50000',   'label' => '20,000₫ - 50,000₫'],
    ['value' => '50000-100000',  'label' => '50,000₫ - 100,000₫'],
    ['value' => '100000-999999', 'label' => 'Trên 100,000₫'],
];

$pageTitle = $categoryName;
require_once '../../components/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('frontend/pages/index.php') ?>">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?= url('frontend/pages/products/list.php') ?>">Sản phẩm</a></li>
            <?php if (!empty($category)): ?>
                <li class="breadcrumb-item active"><?= e($categoryName) ?></li>
            <?php else: ?>
                <li class="breadcrumb-item active">Tất cả sản phẩm</li>
            <?php endif; ?>
        </ol>
    </div>
</nav>

<div class="container my-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <!-- Search box nhỏ trong sidebar -->
            <div class="card mb-3">
                <div class="card-header" style="background-color: #8B4513; color: white;">
                    <h5 class="mb-0"><i class="fas fa-search me-2"></i>Tìm kiếm</h5>
                </div>
                <div class="card-body">
                    <form action="<?= url('frontend/pages/products/list.php') ?>" method="GET">
                        <?php if (!empty($category)): ?>
                            <input type="hidden" name="category" value="<?= e($category) ?>">
                        <?php endif; ?>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control form-control-sm" 
                                   placeholder="Tìm sản phẩm..." value="<?= e($search) ?>">
                            <button class="btn btn-sm btn-brown" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                    <?php if (!empty($search)): ?>
                        <div class="mt-2">
                            <small class="text-muted">Kết quả cho: "<strong><?= e($search) ?></strong>"</small>
                            <a href="<?= buildFilterUrl([], ['search']) ?>" class="text-danger ms-1" title="Xóa tìm kiếm">
                                <i class="fas fa-times-circle"></i>
                            </a>
                            <?php if (!empty($category)): ?>
                                <div class="mt-1">
                                    <a href="<?= url('frontend/pages/products/list.php?search=' . urlencode($search)) ?>" class="small text-primary text-decoration-none">
                                        <i class="fas fa-globe me-1"></i>Tìm trong toàn shop
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header" style="background-color: #8B4513; color: white;">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Danh mục</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="<?= buildFilterUrl([], ['category']) ?>" 
                       class="list-group-item list-group-item-action <?= empty($category) ? 'active' : '' ?>">
                        Tất cả sản phẩm
                    </a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="<?= buildFilterUrl(['category' => $cat['slug']]) ?>" 
                           class="list-group-item list-group-item-action <?= $category === $cat['slug'] ? 'active' : '' ?>">
                            <?= e($cat['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Price Range Filter -->
            <div class="card mb-3">
                <div class="card-header" style="background-color: #8B4513; color: white;">
                    <h5 class="mb-0"><i class="fas fa-dollar-sign me-2"></i>Khoảng giá</h5>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($priceRanges as $range): ?>
                        <a href="<?= buildFilterUrl(['price' => $range['value']]) ?>" 
                           class="list-group-item list-group-item-action <?= $price === $range['value'] ? 'active' : '' ?>">
                            <?= $range['label'] ?>
                        </a>
                    <?php endforeach; ?>
                    <?php if (!empty($price)): ?>
                        <a href="<?= buildFilterUrl([], ['price']) ?>" 
                           class="list-group-item list-group-item-action text-danger text-center">
                            <i class="fas fa-times me-1"></i>Bỏ lọc giá
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Featured Products -->
            <div class="card">
                <div class="card-header" style="background-color: #8B4513; color: white;">
                    <h5 class="mb-0"><i class="fas fa-star me-2"></i>Sản phẩm nổi bật</h5>
                </div>
                <div class="card-body">
                    <?php
                    $featuredQuery = "SELECT * FROM products WHERE is_featured = 1 AND status = 1 LIMIT 3";
                    $featuredStmt = $db->prepare($featuredQuery);
                    $featuredStmt->execute();
                    $featuredProducts = $featuredStmt->fetchAll();
                    
                    foreach ($featuredProducts as $featured):
                    ?>
                        <div class="d-flex mb-3 pb-3 border-bottom">
                            <?php if ($featured['image']): ?>
                                <img src="<?= url('storage/uploads/products/' . $featured['image']) ?>" 
                                     alt="<?= e($featured['name']) ?>" 
                                     class="me-2 rounded" 
                                     style="width: 60px; height: 60px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light me-2 rounded d-flex align-items-center justify-content-center" 
                                     style="width: 60px; height: 60px;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <a href="<?= url('frontend/pages/products/detail.php?id=' . $featured['id']) ?>" 
                                   class="text-decoration-none text-dark">
                                    <small><?= e($featured['name']) ?></small>
                                </a>
                                <br>
                                <strong style="color: #8B4513;"><?= formatCurrency($featured['price']) ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-lg-9">
            <!-- Active Filters -->
            <?php if (!empty($search) || !empty($price) || !empty($category)): ?>
                <div class="mb-3 d-flex flex-wrap align-items-center gap-2">
                    <span class="text-muted"><i class="fas fa-filter me-1"></i>Đang lọc:</span>
                    <?php if (!empty($search)): ?>
                        <span class="badge bg-secondary">
                            Tìm: "<?= e($search) ?>"
                            <a href="<?= buildFilterUrl([], ['search']) ?>" class="text-white ms-1"><i class="fas fa-times"></i></a>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($category)): ?>
                        <span class="badge bg-secondary">
                            <?= e($categoryName) ?>
                            <a href="<?= buildFilterUrl([], ['category']) ?>" class="text-white ms-1"><i class="fas fa-times"></i></a>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($price)):
                        $activePriceLabel = $price;
                        foreach ($priceRanges as $range) {
                            if ($range['value'] === $price) { $activePriceLabel = $range['label']; break; }
                        }
                    ?>
                        <span class="badge bg-secondary">
                            <?= $activePriceLabel ?>
                            <a href="<?= buildFilterUrl([], ['price']) ?>" class="text-white ms-1"><i class="fas fa-times"></i></a>
                        </span>
                    <?php endif; ?>
                    <a href="<?= url('frontend/pages/products/list.php') ?>" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-times-circle me-1"></i>Xóa tất cả bộ lọc
                    </a>
                </div>
            <?php endif; ?>

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><?= e($categoryName) ?></h2>
                    <p class="text-muted mb-0">
                        <i class="fas fa-box me-1"></i>
                        Hiển thị <?= count($products) ?> trên <?= $totalProducts ?> sản phẩm
                    </p>
                </div>
                <div class="d-flex align-items-center">
                    <label class="me-2 mb-0"><i class="fas fa-sort me-1"></i>Sắp xếp:</label>
                    <select class="form-select" style="width: 200px;" onchange="window.location.href=this.value">
                        <?php
                        $sortOptions = [
                            'newest' => 'Mới nhất',
                            'price_asc' => 'Giá: Thấp đến cao',
                            'price_desc' => 'Giá: Cao đến thấp',
                            'name' => 'Tên: A-Z'
                        ];
                        
                        foreach ($sortOptions as $value => $label):
                            $urlParams = $_GET;
                            $urlParams['sort'] = $value;
                            $url = '?' . http_build_query($urlParams);
                        ?>
                            <option value="<?= $url ?>" <?= $sort === $value ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <?php if (!empty($products)): ?>
                <!-- Products -->
                <div class="row">
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-4 mb-4">
                            <?php include '../../components/product-card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <?php
                                    $prevParams = $_GET;
                                    $prevParams['page'] = $page - 1;
                                    ?>
                                    <a class="page-link" href="?<?= http_build_query($prevParams) ?>">
                                        <i class="fas fa-chevron-left"></i> Trước
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php
                            $start = max(1, $page - 2);
                            $end = min($totalPages, $page + 2);
                            
                            for ($i = $start; $i <= $end; $i++):
                                $pageParams = $_GET;
                                $pageParams['page'] = $i;
                            ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query($pageParams) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <?php
                                    $nextParams = $_GET;
                                    $nextParams['page'] = $page + 1;
                                    ?>
                                    <a class="page-link" href="?<?= http_build_query($nextParams) ?>">
                                        Sau <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <!-- No Products -->
                <div class="alert alert-info text-center py-5">
                    <i class="fas fa-search fa-3x mb-3 text-muted"></i>
                    <h4>Không tìm thấy sản phẩm</h4>
                    <p class="mb-3">
                        <?php if (!empty($search)): ?>
                            Không có kết quả nào cho từ khóa "<strong><?= e($search) ?></strong>"
                            <?php if (!empty($category)): ?>
                                trong danh mục "<strong><?= e($categoryName) ?></strong>"
                            <?php endif; ?>
                        <?php elseif (!empty($price)): ?>
                            Không có sản phẩm nào trong khoảng giá đã chọn.
                        <?php else: ?>
                            Danh mục này chưa có sản phẩm nào.
                        <?php endif; ?>
                    </p>
                    <p class="text-muted">Vui lòng thử tìm kiếm với từ khóa khác hoặc xem tất cả sản phẩm.</p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <?php if (!empty($search) && !empty($category)): ?>
                            <a href="<?= url('frontend/pages/products/list.php?search=' . urlencode($search)) ?>" class="btn btn-outline-brown">
                                <i class="fas fa-search me-1"></i>Tìm "<?= e($search) ?>" trong tất cả danh mục
                            </a>
                        <?php endif; ?>
                        <a href="<?= url('frontend/pages/products/list.php') ?>" class="btn btn-brown">
                            <i class="fas fa-list me-1"></i>Xem tất cả sản phẩm
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../../components/footer.php'; ?>
