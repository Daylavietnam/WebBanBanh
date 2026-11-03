<?php
$pageTitle = 'Tin tức & Khuyến mãi';
require_once '../../components/header.php';
require_once '../../../backend/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = NEWS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Count total
$totalNews = $db->query("SELECT COUNT(*) FROM news WHERE status = 1")->fetchColumn();
$totalPages = ceil($totalNews / $limit);

// Get news
$query = "SELECT * FROM news WHERE status = 1 ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($query);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$newsList = $stmt->fetchAll();
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('frontend/pages/index.php') ?>">Trang chủ</a></li>
            <li class="breadcrumb-item active">Tin tức & Khuyến mãi</li>
        </ol>
    </div>
</nav>

<!-- News List -->
<div class="container py-5">
    <h2 class="mb-4">Tin tức & Khuyến mãi</h2>
    
    <div class="row">
        <?php if (empty($newsList)): ?>
            <div class="col-12">
                <div class="alert alert-info">Chưa có tin tức nào.</div>
            </div>
        <?php else: ?>
            <?php foreach ($newsList as $news): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 border-0 shadow-sm hover-shadow">
                    <a href="<?= url('frontend/pages/news/detail.php?slug=' . $news['slug']) ?>">
                        <img src="<?= url('storage/uploads/news/' . ($news['image'] ?? 'default.jpg')) ?>" 
                             class="card-img-top" 
                             alt="<?= e($news['title']) ?>"
                             style="height: 200px; object-fit: cover;"
                             onerror="this.src='<?= url('frontend/assets/images/no-image.png') ?>'">
                    </a>
                    <div class="card-body">
                        <div class="text-muted small mb-2">
                            <i class="far fa-calendar me-1"></i>
                            <?= date('d/m/Y', strtotime($news['created_at'])) ?>
                            <span class="ms-3">
                                <i class="far fa-eye me-1"></i>
                                <?= $news['views'] ?> lượt xem
                            </span>
                        </div>
                        <h5 class="card-title">
                            <a href="<?= url('frontend/pages/news/detail.php?slug=' . $news['slug']) ?>" 
                               class="text-decoration-none text-dark">
                                <?= e($news['title']) ?>
                            </a>
                        </h5>
                        <?php if ($news['summary']): ?>
                            <p class="card-text text-muted"><?= e($news['summary']) ?></p>
                        <?php endif; ?>
                        <a href="<?= url('frontend/pages/news/detail.php?slug=' . $news['slug']) ?>" 
                           class="btn btn-outline-brown btn-sm">
                            Xem chi tiết <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php require_once '../../components/footer.php'; ?>
