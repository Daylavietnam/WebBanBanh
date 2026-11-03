<?php
require_once __DIR__ . '/Session.php';

class AdminMiddleware
{
    public static function checkAdmin(): void
    {
        Session::init();

        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Vui lòng đăng nhập để tiếp tục');
            $login = function_exists('url')
                ? url('frontend/pages/auth/login.php')
                : '/WebBanBanh/frontend/pages/auth/login.php';
            header('Location: ' . $login);
            exit;
        }

        if (!Session::isAdmin()) {
            Session::setFlash('error', 'Bạn không có quyền truy cập trang này');
            $home = function_exists('url')
                ? url('frontend/pages/index.php')
                : '/WebBanBanh/frontend/pages/index.php';
            header('Location: ' . $home);
            exit;
        }
    }
}
