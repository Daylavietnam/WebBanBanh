<?php
require_once '../../backend/config/config.php';
require_once '../../backend/config/database.php';
require_once '../../backend/src/helpers/Session.php';

// Bắt buộc đăng nhập để mua hàng và thanh toán
if (!Session::isLoggedIn()) {
    Session::setFlash('warning', 'Vui lòng đăng nhập tài khoản để tiến hành đặt hàng và thanh toán.');
    redirect('frontend/pages/auth/login.php?redirect=frontend/pages/checkout.php');
}

$cart = Session::getCart();
if (empty($cart)) {
    Session::setFlash('info', 'Giỏ hàng của bạn đang trống.');
    redirect('frontend/pages/cart.php');
}

$total = 0;
$products = [];

if (!empty($cart)) {
    $productIds = array_keys($cart);
    $idsStr = implode(',', array_map('intval', $productIds));
    $database = new Database();
    $db = $database->getConnection();
    $query = "SELECT * FROM products WHERE id IN ($idsStr)";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $products = $stmt->fetchAll();
    
    // Tính tổng tiền
    foreach ($products as $p) {
        $price = $p['sale_price'] > 0 ? $p['sale_price'] : $p['price'];
        $total += $cart[$p['id']] * $price;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Session::isLoggedIn()) {
        Session::setFlash('warning', 'Vui lòng đăng nhập tài khoản để tiến hành đặt hàng.');
        redirect('frontend/pages/auth/login.php?redirect=frontend/pages/checkout.php');
    }

    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $method = trim($_POST['payment_method']);

    if ($name && $phone && $address && $total > 0) {
        $database = new Database();
        $db = $database->getConnection();
        
        // ✅ BƯỚC 1: KIỂM TRA TỒN KHO TRƯỚC KHI ĐẶT HÀNG
        $stockError = false;
        $errorProducts = [];
        
        foreach ($products as $p) {
            $requestedQty = $cart[$p['id']];
            
            // Kiểm tra số lượng yêu cầu có vượt quá tồn kho không
            if ($requestedQty > $p['stock']) {
                $stockError = true;
                $errorProducts[] = $p['name'] . " (còn " . $p['stock'] . " sản phẩm)";
            }
        }
        
        // Nếu có lỗi tồn kho, không cho phép đặt hàng
        if ($stockError) {
            $error = 'Số lượng sản phẩm trong kho không đủ: ' . implode(', ', $errorProducts);
        } else {
            // ✅ BƯỚC 2: TẠO ĐỐN HÀNG
            try {
                // Bắt đầu transaction để đảm bảo tính toàn vẹn dữ liệu
                $db->beginTransaction();
                
                $orderNumber = 'ORD' . time();
                $userId = Session::getUserId();

                // Thêm đơn hàng
                $sql = "INSERT INTO orders 
                    (user_id, order_number, customer_name, customer_email, customer_phone, customer_address, total_amount, payment_method, status) 
                    VALUES 
                    (:user_id, :order_number, :customer_name, :customer_email, :customer_phone, :customer_address, :total_amount, :payment_method, 'pending')";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':user_id' => $userId,
                    ':order_number' => $orderNumber,
                    ':customer_name' => $name,
                    ':customer_email' => $email,
                    ':customer_phone' => $phone,
                    ':customer_address' => $address,
                    ':total_amount' => $total,
                    ':payment_method' => $method,
                ]);
                $orderId = $db->lastInsertId();

                // ✅ BƯỚC 3: THÊM CHI TIẾT ĐƠN HÀNG VÀ TRỪ TỒN KHO
                foreach ($products as $p) {
                    $price = $p['sale_price'] > 0 ? $p['sale_price'] : $p['price'];
                    $qty = $cart[$p['id']];
                    
                    // Thêm vào order_items
                    $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_image, price, quantity, subtotal) 
                                          VALUES (:order_id, :product_id, :product_name, :product_image, :price, :quantity, :subtotal)");
                    $stmt->execute([
                        ':order_id' => $orderId,
                        ':product_id' => $p['id'],
                        ':product_name' => $p['name'],
                        ':product_image' => $p['image'],
                        ':price' => $price,
                        ':quantity' => $qty,
                        ':subtotal' => $qty * $price
                    ]);
                    
                    // ✅ TRỪ TỒN KHO
                    $updateStockStmt = $db->prepare("UPDATE products SET stock = stock - :quantity WHERE id = :product_id");
                    $updateStockStmt->execute([
                        ':quantity' => $qty,
                        ':product_id' => $p['id']
                    ]);
                }

                // Commit transaction
                $db->commit();

                // Nếu thanh toán COD: giữ flow cũ
                if ($method === 'cod') {
                    Session::clearCart();
                    Session::setFlash('success', 'Đặt hàng thành công!');
                    redirect('frontend/pages/thankyou.php?order_id=' . $orderId);
                }

                // Nếu thanh toán qua VNPAY
                if ($method === 'vnpay') {
                    // Không xóa giỏ ngay, đợi thanh toán thành công mới xóa
                    // Lưu order_id vào session để dùng sau nếu cần
                    Session::set('last_order_id', $orderId);

                    // Chuyển sang bước tạo URL thanh toán VNPAY
                    redirect('frontend/pages/payment/vnpay_create_payment.php?order_id=' . $orderId);
                }
                
            } catch (Exception $e) {
                // Rollback nếu có lỗi
                $db->rollBack();
                $error = 'Có lỗi xảy ra khi đặt hàng. Vui lòng thử lại!';
            }
        }
    } else {
        $error = 'Vui lòng điền đầy đủ thông tin!';
    }
}

$pageTitle = 'Thanh toán';
require_once '../components/header.php';
?>

<div class="container py-5">
    <h2 class="mb-4">Thanh toán</h2>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>
    
    <form action="" method="post" class="row g-4 mb-5">
        <div class="col-md-6">
            <?php $curUser = Session::user() ?? []; ?>
            <h5>Thông tin khách hàng</h5>
            <div class="mb-3">
                <label>Họ tên*</label>
                <input type="text" name="name" class="form-control" required 
                       value="<?= e($_POST['name'] ?? (Session::getUserName() ?: '')) ?>">
            </div>
            <div class="mb-3">
                <label>Số điện thoại*</label>
                <input type="text" name="phone" class="form-control" required 
                       value="<?= e($_POST['phone'] ?? ($curUser['phone'] ?? '')) ?>">
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" 
                       value="<?= e($_POST['email'] ?? (Session::getUserEmail() ?: '')) ?>">
            </div>
            <div class="mb-3">
                <label>Địa chỉ nhận hàng*</label>
                <textarea name="address" class="form-control" rows="3" required><?= e($_POST['address'] ?? ($curUser['address'] ?? '')) ?></textarea>
            </div>       
            <div class="mb-3">
                <label class="form-label">Phương thức thanh toán</label>
                <div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" id="payment_cod" value="cod" checked>
                        <label class="form-check-label" for="payment_cod">
                            Thanh toán khi nhận hàng (COD)
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="payment_method" id="payment_vnpay" value="vnpay">
                        <label class="form-check-label" for="payment_vnpay">
                            <strong>Thanh toán qua VNPAY-QR</strong> <span class="badge bg-primary">Mã QR Demo</span>
                            <small class="d-block text-muted">Quét mã QR qua ứng dụng ngân hàng hoặc ví VNPAY (Mã mẫu an toàn không trừ tiền)</small>
                        </label>
                    </div>
                </div>
            </div>  

        </div>
        
        <div class="col-md-6">
            <h5>Đơn hàng của bạn</h5>
            <ul class="list-group mb-3">
                <?php foreach ($products as $p): ?>
                <li class="list-group-item d-flex justify-content-between align-items-start">
                    <div class="d-flex align-items-center">
                        <?php if ($p['image']): ?>
                            <img src="<?= url('storage/uploads/products/' . $p['image']) ?>" 
                                 alt="" style="width:50px;height:50px;object-fit:cover;" class="me-2 rounded">
                        <?php endif; ?>
                        <div>
                            <strong><?= e($p['name']) ?></strong><br>
                            <small class="text-muted">Số lượng: <?= $cart[$p['id']] ?></small>
                            <?php if ($cart[$p['id']] > $p['stock']): ?>
                                <br><span class="badge bg-danger">Không đủ hàng!</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span class="text-end">
                        <?= formatCurrency(($p['sale_price'] > 0 ? $p['sale_price'] : $p['price']) * $cart[$p['id']]) ?>
                    </span>
                </li>
                <?php endforeach; ?>
            </ul>
            
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tạm tính:</span>
                        <strong><?= formatCurrency($total) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Phí vận chuyển:</span>
                        <strong>Miễn phí</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Tổng thanh toán:</strong>
                        <strong class="text-danger fs-5"><?= formatCurrency($total) ?></strong>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 text-end">
            <a href="<?= url('frontend/pages/cart.php') ?>" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Quay lại giỏ hàng
            </a>
            <button type="submit" class="btn btn-brown btn-lg">
                <i class="fas fa-check-circle me-2"></i>Đặt hàng
            </button>
        </div>
    </form>
</div>

<?php require_once '../components/footer.php'; ?>