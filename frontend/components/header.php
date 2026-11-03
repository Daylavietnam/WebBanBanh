<?php
ob_start();
require_once(__DIR__ . '/../../backend/config/config.php');
require_once(__DIR__ . '/../../backend/src/helpers/Session.php');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?>Nguyễn Sơn Bakery</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Custom CSS -->
    <link href="<?= url('frontend/assets/css/style.css') ?>" rel="stylesheet">
    
    <?php if(isset($customCSS)): ?>
        <link href="<?= url('frontend/assets/css/' . $customCSS) ?>" rel="stylesheet">
    <?php endif; ?>
</head>
<body data-cart-api-url="<?= url('backend/src/controllers/CartController.php') ?>" data-login-url="<?= url('frontend/pages/auth/login.php') ?>" data-is-logged-in="<?= Session::isLoggedIn() ? '1' : '0' ?>">
    <!-- Top Bar -->
    <div class="top-bar bg-brown text-white py-2">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <small>
                        <i class="fas fa-phone"></i> Hotline: 02438222228
                        <span class="ms-3"><i class="fas fa-envelope"></i> info@nguyenson.vn</span>
                    </small>
                </div>
                <div class="col-md-6 text-end">
                    <?php if(Session::isLoggedIn()): ?>
                        <small>
                            <i class="fas fa-user"></i> 
                            <a href="<?= url('frontend/pages/account.php') ?>" class="text-white text-decoration-none fw-bold">
                                Xin chào, <?= e(Session::getUserName() ?: (Session::get('user_name') ?: 'Tài khoản')) ?>
                            </a>
                            <?php if (Session::isAdmin()): ?>
                                <span class="ms-2">|</span>
                                <a href="<?= url('frontend/pages/admin/dashboard.php') ?>" class="badge bg-warning text-dark text-decoration-none ms-2">
                                    <i class="fas fa-shield-alt"></i> Quản trị
                                </a>
                            <?php endif; ?>
                            <span class="ms-2">|</span>
                            <a href="<?= url('frontend/pages/auth/logout.php') ?>" class="text-white text-decoration-none ms-2">
                                <i class="fas fa-sign-out-alt"></i> Đăng xuất
                            </a>
                        </small>
                    <?php else: ?>
                        <small>
                            <a href="<?= url('frontend/pages/auth/login.php') ?>" class="text-white text-decoration-none">
                                <i class="fas fa-sign-in-alt"></i> Đăng nhập
                            </a>
                            <span class="ms-2">|</span>
                            <a href="<?= url('frontend/pages/auth/register.php') ?>" class="text-white text-decoration-none ms-2">
                                Đăng ký
                            </a>
                        </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="header bg-white shadow-sm">
        <div class="container">
            <div class="row align-items-center py-3">
                <!-- Logo -->
                <div class="col-md-2">
                    <a href="<?= url('frontend/pages/index.php') ?>" class="logo">
                        <img src="<?= url('frontend/assets/images/logo.png') ?>" alt="Nguyễn Sơn Bakery" class="img-fluid" style="max-height: 60px;">
                    </a>
                </div>
                
                <!-- Search Bar -->
                <div class="col-md-6">
                    <form action="<?= url('frontend/pages/products/list.php') ?>" method="GET" class="search-form">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Tìm kiếm sản phẩm..." value="<?= isset($_GET['search']) ? e($_GET['search']) : '' ?>">
                            <button class="btn btn-brown" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Cart & Actions -->
                <div class="col-md-4 text-end">
                    <a href="<?= url('frontend/pages/cart.php') ?>" class="btn btn-outline-brown position-relative">
                        <i class="fas fa-shopping-cart"></i> Giỏ hàng
                        <?php 
                        $cartCount = Session::isLoggedIn() ? Session::getCartCount() : 0;
                        if($cartCount > 0): 
                        ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $cartCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('frontend/pages/index.php') ?>">
                            <i class="fas fa-home me-1"></i>Trang chủ
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="productsDropdown" data-bs-toggle="dropdown">
                            Danh mục sản phẩm
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= url('frontend/pages/products/list.php') ?>">Tất cả sản phẩm</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php
                            $navDatabase = new Database();
                            $navDb = $navDatabase->getConnection();
                            $navCategories = $navDb->query("SELECT * FROM categories WHERE status = 1 ORDER BY display_order ASC, name ASC")->fetchAll();
                            
                            foreach ($navCategories as $navCat):
                            ?>
                                <li>
                                    <!-- ✅ SỬA DÒNG NÀY: Thêm /list.php -->
                                    <a class="dropdown-item" href="<?= url('frontend/pages/products/list.php?category=' . $navCat['slug']) ?>">
                                        <?= e($navCat['name']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('frontend/pages/about.php') ?>">Giới thiệu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('frontend/pages/news/list.php') ?>">Tin tức & Khuyến mãi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('frontend/pages/contact.php') ?>">Liên hệ</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if($success = Session::getFlash('success')): ?>
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show">
                <?= e($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if($error = Session::getFlash('error')): ?>
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show">
                <?= e($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>
    <!-- Main Content -->
    <main class="main-content">