<?php
require_once '../../../backend/config/config.php';
require_once '../../../backend/config/database.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($slug)) {
    redirect('frontend/pages/news/list.php');
}

$database = new Database();
$db = $database->getConnection();

// Get news details
$query = "SELECT * FROM news WHERE slug = :slug AND status = 1";
$stmt = $db->prepare($query);
$stmt->bindParam(':slug', $slug);
$stmt->execute();
$news = $stmt->fetch();

if (!$news) {
    Session::setFlash('error', 'Tin tức không tồn tại');
    redirect('frontend/pages/news/list.php');
}

// Update view count
$updateViewQuery = "UPDATE news SET views = views + 1 WHERE id = :id";
$stmtUpdateView = $db->prepare($updateViewQuery);
$stmtUpdateView->bindParam(':id', $news['id']);
$stmtUpdateView->execute();

// Get related news
$queryRelated = "SELECT * FROM news 
                 WHERE id != :news_id AND status = 1 
                 ORDER BY created_at DESC 
                 LIMIT 3";
$stmtRelated = $db->prepare($queryRelated);
$stmtRelated->bindParam(':news_id', $news['id']);
$stmtRelated->execute();
$relatedNews = $stmtRelated->fetchAll();

$pageTitle = $news['title'];
require_once '../../components/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('frontend/pages/index.php') ?>">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?= url('frontend/pages/news/list.php') ?>">Tin tức</a></li>
            <li class="breadcrumb-item active"><?= e($news['title']) ?></li>
        </ol>
    </div>
</nav>

<!-- News Detail -->
<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <article class="news-detail">
                <h1 class="mb-3"><?= e($news['title']) ?></h1>
                
                <div class="text-muted mb-4">
                    <i class="far fa-calendar me-2"></i><?= date('d/m/Y H:i', strtotime($news['created_at'])) ?>
                    <span class="ms-3"><i class="far fa-user me-2"></i><?= e($news['author'] ?? 'Admin') ?></span>
                    <span class="ms-3"><i class="far fa-eye me-2"></i><?= $news['views'] ?> lượt xem</span>
                </div>
                
                <?php if ($news['image']): ?>
                    <img src="<?= url('storage/uploads/news/' . $news['image']) ?>" 
                         alt="<?= e($news['title']) ?>" 
                         class="img-fluid rounded mb-4"
                         onerror="this.src='<?= url('frontend/assets/images/no-image.png') ?>'">
                <?php endif; ?>
                
                <?php if ($news['summary']): ?>
                    <div class="lead mb-4">
                        <?= nl2br(e($news['summary'])) ?>
                    </div>
                <?php endif; ?>
                
                <div class="news-content">
                    <?= $news['content'] ?>
                </div>
                
                <hr class="my-4">
                
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Chia sẻ:</strong>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(url('frontend/pages/news/detail.php?slug=' . $news['slug'])) ?>" 
                           target="_blank" class="btn btn-sm btn-primary ms-2">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </a>
                    </div>
                    <a href="<?= url('frontend/pages/news/list.php') ?>" class="btn btn-outline-brown">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại
                    </a>
                </div>
            </article>
            
            <!-- Related News -->
            <?php if (!empty($relatedNews)): ?>
                <div class="related-news mt-5">
                    <h3 class="mb-4">Tin tức liên quan</h3>
                    <div class="row">
                        <?php foreach ($relatedNews as $related): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card border-0 shadow-sm">
                                <a href="<?= url('frontend/pages/news/detail.php?slug=' . $related['slug']) ?>">
                                    <img src="<?= url('storage/uploads/news/' . ($related['image'] ?? 'default.jpg')) ?>" 
                                         class="card-img-top" 
                                         alt="<?= e($related['title']) ?>"
                                         style="height: 150px; object-fit: cover;"
                                         onerror="this.src='<?= url('frontend/assets/images/no-image.png') ?>'">
                                </a>
                                <div class="card-body">
                                    <h6>
                                        <a href="<?= url('frontend/pages/news/detail.php?slug=' . $related['slug']) ?>" 
                                           class="text-decoration-none text-dark">
                                            <?= e($related['title']) ?>
                                        </a>
                                    </h6>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../../components/footer.php'; ?>
