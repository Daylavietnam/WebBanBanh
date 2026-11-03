<?php
require_once '../../../backend/config/config.php';
require_once '../../../backend/config/database.php';
require_once '../../../backend/src/helpers/AdminMiddleware.php';

AdminMiddleware::checkAdmin();

$contactId = (int)($_GET['id'] ?? 0);

if ($contactId > 0) {
    $database = new Database();
    $db = $database->getConnection();
    
    $stmt = $db->prepare("DELETE FROM contacts WHERE id = ?");
    $result = $stmt->execute([$contactId]);
    
    if ($result) {
        Session::setFlash('success', 'Xóa liên hệ thành công!');
    } else {
        Session::setFlash('error', 'Có lỗi xảy ra!');
    }
} else {
    Session::setFlash('error', 'Liên hệ không tồn tại!');
}

redirect('frontend/pages/admin/contacts.php');
