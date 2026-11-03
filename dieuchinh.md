# TÀI LIỆU TỔNG HỢP CÁC ĐIỀU CHỈNH: BẮT BUỘC ĐĂNG NHẬP ĐỂ THÊM GIỎ HÀNG VÀ THANH TOÁN

> **Ngày thực hiện:** 21/10/2026  
> **Mục tiêu:** Sửa lỗi hệ thống cho phép người dùng chưa đăng nhập vẫn có thể thêm sản phẩm vào giỏ hàng và tiến hành đặt hàng / thanh toán. Bắt buộc người dùng phải có tài khoản và đăng nhập mới được mua hàng.

---

## 1. Nguyên nhân lỗi trước khi sửa
1. **Thiếu kiểm tra đăng nhập ở tầng API Giỏ hàng (`CartController.php`):**
   - Các hành động `add`, `update`, `remove` nhận request từ client và ghi thẳng vào session giỏ hàng mà không xác thực `Session::isLoggedIn()`.
2. **Thiếu ràng buộc ở tầng Session Helper (`Session.php`):**
   - Các hàm `getCart()`, `getCartCount()`, `addToCart()`, `updateCart()` hoạt động độc lập với trạng thái đăng nhập, cho phép phiên khách vãng lai tự do lưu trữ giỏ hàng.
   - Khi đăng xuất (`logout.php`), `$_SESSION['cart']` không được giải phóng.
3. **Giao diện Client (`main.js`):**
   - Nút `.btn-add-to-cart` bấm vào là gửi ngay request API mà không kiểm tra người dùng đã đăng nhập hay chưa, không có phản hồi nhắc nhở đăng nhập.
4. **Trang Giỏ hàng & Thanh toán (`cart.php`, `checkout.php`, `vnpay_create_payment.php`):**
   - Không chặn khách chưa đăng nhập. Trang `checkout.php` cho phép khách điền thông tin và tạo đơn hàng với `user_id = null`, dẫn đến việc khách không thể theo dõi đơn hàng tại trang `order-detail.php` (trang này yêu cầu đăng nhập).

---

## 2. Danh sách các file đã điều chỉnh và nội dung chi tiết

### 2.1. `backend/src/helpers/Session.php`
- **Mục đích:** Ràng buộc an toàn ở tầng core session của toàn bộ ứng dụng.
- **Chi tiết thay đổi:**
  - Cải tiến `isLoggedIn()`: Kiểm tra `isset($_SESSION['user']) && !empty($_SESSION['user']['id'])` để đảm bảo có thông tin ID người dùng hợp lệ.
  - Cập nhật `getCart()`, `getCartCount()`, `getCartDistinctCount()`: Nếu `!isLoggedIn()` thì luôn trả về mảng rỗng `[]` hoặc số lượng `0`.
  - Cập nhật `addToCart()`, `updateCart()`: Chỉ thực thi khi `isLoggedIn() === true`.
  - Cập nhật `clearUser()`: Bổ sung xóa `unset($_SESSION['cart'])` khi đăng xuất tài khoản.

---

### 2.2. `backend/src/controllers/CartController.php`
- **Mục đích:** Bảo vệ API giỏ hàng, chặn các request thêm/sửa/xóa từ khách chưa đăng nhập (kể cả gọi trực tiếp qua Postman, cURL hay AJAX).
- **Chi tiết thay đổi:**
  - Trong `addToCart()`: Kiểm tra `if (!Session::isLoggedIn())`, trả về JSON:
    ```json
    {
      "success": false,
      "require_login": true,
      "message": "Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!",
      "redirect_url": "http://.../frontend/pages/auth/login.php"
    }
    ```
  - Trong `updateCart()` và `removeFromCart()`: Bổ sung kiểm tra `Session::isLoggedIn()`, từ chối thao tác nếu chưa đăng nhập.
  - Trong `getCartCount()`: Trả về số lượng `0` nếu chưa đăng nhập.

---

### 2.3. `frontend/components/header.php`
- **Mục đích:** Truyền trạng thái đăng nhập xuống cho JavaScript và cập nhật badge giỏ hàng.
- **Chi tiết thay đổi:**
  - Bổ sung thuộc tính `data-login-url` và `data-is-logged-in` vào thẻ `<body>`:
    ```html
    <body data-cart-api-url="<?= url('backend/src/controllers/CartController.php') ?>" 
          data-login-url="<?= url('frontend/pages/auth/login.php') ?>" 
          data-is-logged-in="<?= Session::isLoggedIn() ? '1' : '0' ?>">
    ```
  - Cập nhật số lượng trên icon giỏ hàng: Chỉ tính và hiển thị badge nếu `Session::isLoggedIn()`.

---

### 2.4. `frontend/assets/js/main.js`
- **Mục đích:** Xử lý trải nghiệm người dùng mượt mà khi ấn nút thêm giỏ hàng.
- **Chi tiết thay đổi:**
  - Thêm hàm `getCurrentPageRedirectParam()`: Tự động trích xuất URL trang hiện tại (kèm query parameters như `slug=...`, `search=...`) để làm tham số chuyển hướng quay lại sau khi đăng nhập.
  - Bắt sự kiện click `.btn-add-to-cart`:
    - Đọc `data-is-logged-in` từ thẻ `<body>`.
    - Nếu chưa đăng nhập: Hiển thị thông báo Toast cảnh báo đỏ: *"Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!"* và tự động chuyển hướng sang trang đăng nhập sau 1.2s.
    - Nếu server trả về `data.require_login === true`: Hiển thị thông báo và điều hướng sang trang login.
  - Trong `updateCartCount()`: Nếu chưa đăng nhập, ẩn ngay badge số lượng trên thanh menu.

---

### 2.5. `frontend/pages/cart.php`
- **Mục đích:** Ngăn chặn khách chưa đăng nhập truy cập trang giỏ hàng.
- **Chi tiết thay đổi:**
  - Đặt đoạn kiểm tra đăng nhập ngay đầu file (trước khi xuất HTML):
    ```php
    if (!Session::isLoggedIn()) {
        Session::setFlash('warning', 'Vui lòng đăng nhập tài khoản để xem và quản lý giỏ hàng của bạn.');
        redirect('frontend/pages/auth/login.php?redirect=frontend/pages/cart.php');
    }
    ```
  - Bổ sung xử lý `require_login` trong các hàm AJAX cập nhật số lượng và xóa sản phẩm.

---

### 2.6. `frontend/pages/checkout.php`
- **Mục đích:** Chặn hoàn toàn khách chưa đăng nhập vào trang thanh toán và đặt hàng.
- **Chi tiết thay đổi:**
  - Đặt kiểm tra đăng nhập ngay trên đầu file trước khi include header:
    ```php
    if (!Session::isLoggedIn()) {
        Session::setFlash('warning', 'Vui lòng đăng nhập tài khoản để tiến hành đặt hàng và thanh toán.');
        redirect('frontend/pages/auth/login.php?redirect=frontend/pages/checkout.php');
    }
    ```
  - Kiểm tra giỏ hàng trống: Nếu trống, thông báo và đưa về `cart.php`.
  - Trong khối xử lý `POST`:
    - Bắt buộc `Session::isLoggedIn()`.
    - Lấy `$userId = Session::getUserId()` gắn trực tiếp vào đơn hàng (`INSERT INTO orders (user_id, ...)`), không còn trường hợp `user_id` bị `null`.
    - Sau khi tạo đơn COD thành công, chuyển hướng về `thankyou.php?order_id=...`.

---

### 2.7. `frontend/pages/payment/vnpay_create_payment.php`
- **Mục đích:** Bảo vệ trang thanh toán VNPAY-QR demo.
- **Chi tiết thay đổi:**
  - Bắt buộc người dùng phải đăng nhập: `if (!Session::isLoggedIn()) redirect('frontend/pages/auth/login.php');`.
  - Kiểm tra quyền sở hữu đơn hàng: `SELECT * FROM orders WHERE id = :id AND user_id = :user_id`. Không cho phép thanh toán chéo đơn của tài khoản khác.

---

### 2.8. `frontend/pages/auth/login.php`
- **Mục đích:** Hiển thị thông báo cảnh báo điều hướng từ giỏ hàng / thanh toán.
- **Chi tiết thay đổi:**
  - Bổ sung hiển thị `Session::getFlash('warning')` và `Session::getFlash('info')` với icon cảnh báo Bootstrap trực quan.
  - Giữ nguyên cơ chế chuyển hướng thông minh qua tham số `redirect` (sau khi đăng nhập xong sẽ tự động trở về đúng trang người dùng đang thao tác dở dang).

---

### 2.9. `frontend/pages/auth/logout.php`
- **Mục đích:** Dọn dẹp dữ liệu phiên làm việc an toàn.
- **Chi tiết thay đổi:**
  - Bổ sung gọi `Session::clearCart();` để làm trống giỏ hàng khi người dùng đăng xuất, tránh lưu đọng vào phiên khách vãng lai kế tiếp.

---

## 3. Luồng hoạt động hoàn chỉnh sau khi sửa (Workflow)

```
1. Khách chưa đăng nhập:
   ├── Bấm "Thêm vào giỏ" (Trang chủ / Danh mục / Chi tiết sản phẩm):
   │   └── Hiện Toast thông báo "Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!"
   │   └── Chuyển hướng sang login.php?redirect=<trang_hien_tai>
   │
   ├── Bấm trực tiếp vào icon Giỏ hàng hoặc vào checkout.php:
   │   └── Server chặn ngay lập tức, chuyển hướng về login.php
   │   └── Xuất hiện banner thông báo màu vàng yêu cầu đăng nhập
   │
   └── Gửi request ngầm POST tới CartController / checkout.php:
       └── Server từ chối xử lý, trả về require_login = true.

2. Người dùng đăng nhập:
   ├── Đăng nhập thành công -> Tự động quay lại đúng sản phẩm / trang vừa xem.
   ├── Có thể thêm sản phẩm vào giỏ, cập nhật số lượng, xóa sản phẩm.
   ├── Vào giỏ hàng xem danh sách sản phẩm và nhấn "Thanh toán".
   ├── Đặt hàng thành công: Đơn hàng gắn liền với user_id của tài khoản.
   └── Có thể xem lại đơn hàng trong phần Quản lý tài khoản (account.php / order-detail.php).

3. Người dùng đăng xuất:
   └── Xóa thông tin user và dọn sạch session giỏ hàng.
```

---

## 4. Hướng dẫn kiểm thử (Test Checklist)

| STT | Tình huống kiểm thử | Kết quả mong đợi | Trạng thái |
|---|---|---|---|
| 1 | Chưa đăng nhập -> Bấm nút "Thêm vào giỏ" ở trang danh sách | Hiện toast lỗi yêu cầu đăng nhập, tự động chuyển đến trang `login.php` | Đạt |
| 2 | Chưa đăng nhập -> Bấm "Thêm vào giỏ hàng" ở trang chi tiết | Hiện toast lỗi yêu cầu đăng nhập, sau khi login tự động quay lại trang chi tiết sản phẩm đó | Đạt |
| 3 | Chưa đăng nhập -> Gõ trực tiếp URL `cart.php` trên trình duyệt | Bị redirect về `login.php`, hiện thông báo cảnh báo yêu cầu đăng nhập | Đạt |
| 4 | Chưa đăng nhập -> Gõ trực tiếp URL `checkout.php` trên trình duyệt | Bị redirect về `login.php`, hiện thông báo cảnh báo yêu cầu đăng nhập | Đạt |
| 5 | Đăng nhập tài khoản -> Thêm bánh vào giỏ | Thêm thành công, icon giỏ hàng cập nhật số lượng badge chính xác | Đạt |
| 6 | Đã đăng nhập -> Vào giỏ hàng và chuyển sang thanh toán | Vào bình thường, điền thông tin và đặt hàng COD/VNPAY thành công | Đạt |
| 7 | Đã đăng nhập -> Bấm "Đăng xuất" | Đăng xuất thành công, giỏ hàng được làm sạch, không còn lưu badge | Đạt |
