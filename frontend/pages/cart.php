<?php
require_once '../../backend/config/config.php';
require_once '../../backend/config/database.php';
require_once '../../backend/src/helpers/Session.php';

// Bắt buộc đăng nhập để xem và quản lý giỏ hàng
if (!Session::isLoggedIn()) {
    Session::setFlash('warning', 'Vui lòng đăng nhập tài khoản để xem và quản lý giỏ hàng của bạn.');
    redirect('frontend/pages/auth/login.php?redirect=frontend/pages/cart.php');
}

$pageTitle = 'Giỏ hàng';
require_once '../components/header.php';

// Lấy giỏ hàng từ Session
$cart = Session::getCart();

// Lấy thông tin chi tiết sản phẩm trong giỏ
$productsInfo = [];
$total = 0;

if (!empty($cart)) {
    $productIds = array_keys($cart);
    $idsStr = implode(',', array_map('intval', $productIds));

    $database = new Database();
    $db = $database->getConnection();
    $query = "SELECT * FROM products WHERE id IN ($idsStr)";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $productsInfo = $stmt->fetchAll();

    foreach ($productsInfo as $p) {
        $price = $p['sale_price'] > 0 ? $p['sale_price'] : $p['price'];
        $total += $cart[$p['id']] * $price;
    }
}
?>

<!-- ✅ Toast Container -->
<div id="toast-container" style="position: fixed; top: 80px; right: 20px; z-index: 9999;"></div>

<div class="container py-5">
    <h2 class="mb-4">Giỏ hàng của bạn</h2>
    <?php if (empty($cart)): ?>
        <div class="alert alert-info">Giỏ hàng hiện đang trống.</div>
        <a href="products/list.php" class="btn btn-brown">Tiếp tục mua hàng</a>
    <?php else: ?>
        <div id="cart-content">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Sản phẩm</th>
                            <th scope="col">Đơn giá</th>
                            <th scope="col">Số lượng</th>
                            <th scope="col">Thành tiền</th>
                            <th scope="col">Xóa</th>
                        </tr>
                    </thead>
                    <tbody id="cart-items">
                        <?php foreach ($productsInfo as $product): 
                            $price = $product['sale_price'] > 0 ? $product['sale_price'] : $product['price'];
                            $subtotal = $cart[$product['id']] * $price;
                        ?>
                        <tr data-product-id="<?= $product['id'] ?>" data-price="<?= $price ?>">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?= url('storage/uploads/products/' . $product['image']) ?>" alt="" style="width:60px;height:60px;object-fit:cover;border-radius:8px;">
                                    <a href="products/detail.php?slug=<?= $product['slug'] ?>" class="ms-3 text-dark text-decoration-none fw-medium"><?= e($product['name']) ?></a>
                                </div>
                            </td>
                            <td class="product-price"><?= formatCurrency($price) ?></td>
                            <td style="max-width:100px;">
                                <input type="number" min="1" max="<?= $product['stock'] ?>"
                                       class="form-control cart-qty"
                                       value="<?= $cart[$product['id']] ?>"
                                       data-id="<?= $product['id'] ?>"
                                       data-stock="<?= $product['stock'] ?>">
                            </td>
                            <td class="subtotal"><?= formatCurrency($subtotal) ?></td>
                            <td>
                                <button type="button" class="btn btn-danger btn-remove" data-id="<?= $product['id'] ?>">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-end mb-3">
                <h4>Tổng: <span class="text-danger" id="cart-total"><?= formatCurrency($total) ?></span></h4>
                <a href="checkout.php" class="btn btn-brown btn-lg">Thanh toán</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
/* ✅ Toast Notification Styles */
.toast {
    min-width: 300px;
    padding: 15px 20px;
    margin-bottom: 10px;
    border-radius: 8px;
    color: white;
    font-size: 14px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    animation: slideIn 0.3s ease-out, fadeOut 0.3s ease-in 2.7s;
    display: flex;
    align-items: center;
    gap: 10px;
}

.toast-success {
    background: linear-gradient(135deg, #28a745, #218838);
}

.toast-error {
    background: linear-gradient(135deg, #dc3545, #c82333);
}

.toast i {
    font-size: 20px;
}

@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes fadeOut {
    to {
        opacity: 0;
        transform: translateX(400px);
    }
}

/* ✅ Loading Spinner */
.btn-loading {
    position: relative;
    pointer-events: none;
    opacity: 0.6;
}

.btn-loading::after {
    content: "";
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-left: -8px;
    margin-top: -8px;
    border: 2px solid white;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spinner 0.6s linear infinite;
}

@keyframes spinner {
    to { transform: rotate(360deg); }
}

/* ✅ Fade out animation cho row bị xóa */
.row-removing {
    animation: fadeOutRow 0.3s ease-out forwards;
}

@keyframes fadeOutRow {
    to {
        opacity: 0;
        transform: translateX(-20px);
    }
}

/* ✅ Hover effect cho tên sản phẩm */
.text-dark.text-decoration-none:hover {
    color: #8B4513 !important;
}
</style>

<script>
const CART_API = '<?= url("backend/src/controllers/CartController.php") ?>';

// ✅ Toast Notification Function
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    toast.innerHTML = `<i class="fa ${icon}"></i><span>${message}</span>`;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// ✅ Format Currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', { 
        style: 'currency', 
        currency: 'VND' 
    }).format(amount).replace('₫', 'đ');
}

// ✅ Update Cart Total
function updateCartTotal() {
    let total = 0;
    document.querySelectorAll('#cart-items tr').forEach(row => {
        const price = parseFloat(row.dataset.price);
        const qty = parseInt(row.querySelector('.cart-qty').value);
        total += price * qty;
    });
    document.getElementById('cart-total').textContent = formatCurrency(total);
    
    // Update cart count in header
    const cartCount = Array.from(document.querySelectorAll('.cart-qty'))
        .reduce((sum, input) => sum + parseInt(input.value), 0);
    const badge = document.querySelector('.badge.bg-danger');
    if (badge) badge.textContent = cartCount;
}

// ✅ Update Subtotal
function updateSubtotal(row) {
    const price = parseFloat(row.dataset.price);
    const qty = parseInt(row.querySelector('.cart-qty').value);
    const subtotal = price * qty;
    row.querySelector('.subtotal').textContent = formatCurrency(subtotal);
}

// ✅ Update Quantity
document.querySelectorAll('.cart-qty').forEach(input => {
    // Debounce để tránh gọi API liên tục
    let timeout;
    
    input.addEventListener('change', function() {
        clearTimeout(timeout);
        
        const id = this.dataset.id;
        const qty = parseInt(this.value);
        const stock = parseInt(this.dataset.stock);
        const row = this.closest('tr');
        
        if (qty < 1) {
            this.value = 1;
            showToast('Số lượng phải lớn hơn 0', 'error');
            return;
        }
        
        if (qty > stock) {
            this.value = stock;
            showToast(`Chỉ còn ${stock} sản phẩm trong kho`, 'error');
            return;
        }
        
        // ✅ Show loading
        this.classList.add('btn-loading');
        
        timeout = setTimeout(() => {
            fetch(CART_API, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=update&product_id=${id}&quantity=${qty}`
            })
            .then(response => response.json())
            .then(data => {
                this.classList.remove('btn-loading');
                
                if (data.success) {
                    updateSubtotal(row);
                    updateCartTotal();
                    showToast('Đã cập nhật số lượng', 'success');
                } else {
                    showToast(data.message || 'Có lỗi xảy ra', 'error');
                    if (data.require_login) {
                        setTimeout(() => {
                            window.location.href = data.redirect_url || '<?= url("frontend/pages/auth/login.php?redirect=frontend/pages/cart.php") ?>';
                        }, 1200);
                    }
                }
            })
            .catch(error => {
                this.classList.remove('btn-loading');
                showToast('Có lỗi xảy ra, vui lòng thử lại', 'error');
            });
        }, 500); // Delay 500ms
    });
});

// ✅ Remove Product (KHÔNG CÓ CONFIRM)
document.querySelectorAll('.btn-remove').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const row = this.closest('tr');
        
        // ✅ Show loading
        this.classList.add('btn-loading');
        
        fetch(CART_API, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=remove&product_id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // ✅ Animate row removal
                row.classList.add('row-removing');
                
                setTimeout(() => {
                    row.remove();
                    updateCartTotal();
                    
                    // ✅ Check if cart is empty
                    if (document.querySelectorAll('#cart-items tr').length === 0) {
                        location.reload();
                    }
                }, 300);
                
                showToast('Đã xóa sản phẩm khỏi giỏ hàng', 'success');
            } else {
                this.classList.remove('btn-loading');
                showToast(data.message || 'Có lỗi xảy ra', 'error');
                if (data.require_login) {
                    setTimeout(() => {
                        window.location.href = data.redirect_url || '<?= url("frontend/pages/auth/login.php?redirect=frontend/pages/cart.php") ?>';
                    }, 1200);
                }
            }
        })
        .catch(error => {
            this.classList.remove('btn-loading');
            showToast('Có lỗi xảy ra, vui lòng thử lại', 'error');
        });
    });
});
</script>

<?php require_once '../components/footer.php'; ?>
