<?php
// __DIR__ = .../frontend/pages/admin/includes
$ROOT = dirname(__DIR__, 4); // lên 4 cấp về tới thư mục dự án WebBanBanh

require_once $ROOT . '/backend/config/config.php';              // nơi định nghĩa url(), DB, v.v.
require_once $ROOT . '/backend/src/helpers/AdminMiddleware.php';// nạp class
AdminMiddleware::checkAdmin();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?>Admin - Nguyễn Sơn Bakery</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Custom Admin CSS -->
    <style>
        :root {
            --brown-primary: #6B4423;
            --brown-dark: #4A2F1A;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            min-height: 100vh;
            background-color: var(--brown-primary);
            padding: 0;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 1rem 1.5rem;
            border-left: 3px solid transparent;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background-color: var(--brown-dark);
            border-left-color: #ffc107;
        }
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        
        .main-content {
            padding: 2rem;
        }
        
        .stat-card {
            border-left: 4px solid var(--brown-primary);
        }
        
        .table-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block sidebar">
                <div class="position-sticky">
                    <div class="text-center py-4">
                        <h4 class="text-white">Admin Panel</h4>
                        <small class="text-white-50">Nguyễn Sơn Bakery</small>
                    </div>
                    
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" 
                                href="<?= url('frontend/pages/admin/dashboard.php') ?>">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>" 
                                href="<?= url('frontend/pages/admin/products.php') ?>">
                                    <i class="fas fa-box"></i> Sản phẩm
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : '' ?>" 
                                href="<?= url('frontend/pages/admin/categories.php') ?>">
                                    <i class="fas fa-tags"></i> Danh mục
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>" 
                                href="<?= url('frontend/pages/admin/orders.php') ?>">
                                    <i class="fas fa-shopping-cart"></i> Đơn hàng
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : '' ?>" 
                                href="<?= url('frontend/pages/admin/customers.php') ?>">
                                    <i class="fas fa-users"></i> Khách hàng
                                </a>
                            </li>

                            <!-- MỤC MỚI: LIÊN HỆ -->
                            <li class="nav-item">
                                <a class="nav-link <?= in_array(basename($_SERVER['PHP_SELF']), ['contacts.php', 'contact-detail.php']) ? 'active' : '' ?>" 
                                href="<?= url('frontend/pages/admin/contacts.php') ?>">
                                    <i class="fas fa-envelope"></i> Liên hệ
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="<?= url('frontend/pages/index.php') ?>">
                                    <i class="fas fa-home"></i> Về trang chủ
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= url('frontend/pages/auth/logout.php') ?>">
                                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                                </a>
                            </li>
                        </ul>

                </div>
            </nav>
            
            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto main-content">
                <!-- Flash Messages -->
                <?php if($success = Session::getFlash('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= e($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if($error = Session::getFlash('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= e($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>