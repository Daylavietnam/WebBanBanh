<?php
// _whoami.php  (đặt tại: frontend/pages/admin/_whoami.php)
$ROOT = dirname(__DIR__, 3); // lên 3 cấp: admin -> pages -> frontend -> WebBanBanh
require_once $ROOT . '/backend/src/helpers/Session.php';

Session::init();
echo '<pre>'; var_dump(Session::user()); echo '</pre>';
