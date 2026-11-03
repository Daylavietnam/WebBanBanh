<?php
require_once '../../../backend/config/config.php';
require_once '../../../backend/src/helpers/Session.php';

Session::clearUser();
Session::clearCart();
Session::setFlash('success', 'Đăng xuất thành công!');
redirect('frontend/pages/index.php');
?>
