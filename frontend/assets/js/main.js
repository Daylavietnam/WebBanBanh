// Main JavaScript file
document.addEventListener('DOMContentLoaded', function () {

  // ✅ Lấy đường dẫn API từ body attribute (fallback về absolute path)
  const CART_API_URL = document.body.getAttribute('data-cart-api-url') ||
    window.location.origin + '/WebBanBanh/backend/src/controllers/CartController.php';

  // Back to top button
  const backToTopBtn = document.getElementById('backToTop');

  if (backToTopBtn) {
    window.addEventListener('scroll', function () {
      if (window.pageYOffset > 300) {
        backToTopBtn.style.display = 'block';
      } else {
        backToTopBtn.style.display = 'none';
      }
    });

    backToTopBtn.addEventListener('click', function () {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  }

  // Helper lấy URL chuyển hướng sau khi đăng nhập
  function getCurrentPageRedirectParam() {
    const path = window.location.pathname;
    const match = path.match(/(frontend\/pages\/.*)$/);
    if (match) {
      return match[1] + window.location.search;
    }
    return 'frontend/pages/index.php';
  }

  // Add to cart functionality
  const addToCartButtons = document.querySelectorAll('.btn-add-to-cart');

  addToCartButtons.forEach(button => {
    button.addEventListener('click', function (e) {
      e.preventDefault();

      const isLoggedIn = document.body.getAttribute('data-is-logged-in') === '1';
      const loginUrl = document.body.getAttribute('data-login-url') || (window.location.origin + '/WebBanBanh/frontend/pages/auth/login.php');

      // Nếu chưa đăng nhập: Thông báo và chuyển hướng sang trang đăng nhập
      if (!isLoggedIn) {
        showToast('error', 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!');
        setTimeout(() => {
          const redirectParam = encodeURIComponent(getCurrentPageRedirectParam());
          window.location.href = loginUrl + '?redirect=' + redirectParam;
        }, 1200);
        return;
      }

      const productId = this.dataset.productId;
      const productName = this.dataset.productName;
      const quantity = this.dataset.quantity || 1;

      fetch(CART_API_URL, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add&product_id=${productId}&quantity=${quantity}`
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Show success message
            showToast('success', `Đã thêm "${productName}" vào giỏ hàng`);

            // ✅ Update cart count
            updateCartCount();
          } else {
            showToast('error', data.message || 'Có lỗi xảy ra');
            if (data.require_login) {
              setTimeout(() => {
                const targetLogin = data.redirect_url || loginUrl;
                const redirectParam = encodeURIComponent(getCurrentPageRedirectParam());
                window.location.href = targetLogin + '?redirect=' + redirectParam;
              }, 1200);
            }
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToast('error', 'Không thể thêm vào giỏ hàng');
        });
    });
  });

  // Toast notification function
  function showToast(type, message) {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();

    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

    toastContainer.appendChild(toast);

    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();

    toast.addEventListener('hidden.bs.toast', function () {
      toast.remove();
    });
  }

  function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    document.body.appendChild(container);
    return container;
  }

  // ✅ SỬA HÀM updateCartCount - SELECTOR CHÍNH XÁC HƠN
  function updateCartCount() {
    const isLoggedIn = document.body.getAttribute('data-is-logged-in') === '1';
    if (!isLoggedIn) {
      const cartLink = document.querySelector('a[href*="cart.php"]');
      if (cartLink) {
        const cartBadge = cartLink.querySelector('.badge');
        if (cartBadge) cartBadge.style.display = 'none';
      }
      return;
    }

    fetch(CART_API_URL + '?action=count')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // ✅ Tìm chính xác badge trong nút giỏ hàng
          const cartLink = document.querySelector('a[href*="cart.php"]');

          if (cartLink) {
            let cartBadge = cartLink.querySelector('.badge');

            // ✅ Nếu chưa có badge, tạo mới
            if (!cartBadge) {
              cartBadge = document.createElement('span');
              cartBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
              cartLink.appendChild(cartBadge);
            }

            // ✅ Cập nhật số lượng
            cartBadge.textContent = data.count;

            // ✅ Hiện/ẩn badge
            if (data.count > 0) {
              cartBadge.style.display = 'inline-block';
            } else {
              cartBadge.style.display = 'none';
            }

            // ✅ Animation khi cập nhật
            cartBadge.classList.add('animate__animated', 'animate__pulse');
            setTimeout(() => {
              cartBadge.classList.remove('animate__animated', 'animate__pulse');
            }, 600);
          }
        }
      })
      .catch(error => {
        console.error('Error updating cart count:', error);
      });
  }

  // Auto-hide alerts
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    setTimeout(() => {
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    }, 5000);
  });

  const NAV_BREAKPOINT = 992; // lg

  // (Tuỳ chọn) đánh số item để so-le (khớp với CSS comment ở trên)
  document.querySelectorAll('.navbar .dropdown').forEach(function (dd) {
    const items = dd.querySelectorAll('.dropdown-menu > li > a.dropdown-item');
    items.forEach((a, idx) => a.style.setProperty('--i', idx));
  });

  document.querySelectorAll('.navbar .dropdown').forEach(function (dd) {
    const toggle = dd.querySelector('[data-bs-toggle="dropdown"]');
    const menu = dd.querySelector('.dropdown-menu');
    if (!toggle || !menu) return;

    let showTimer, hideTimer;

    // Hover in (desktop): mở mượt
    dd.addEventListener('mouseenter', function () {
      if (window.innerWidth < NAV_BREAKPOINT) return;
      clearTimeout(hideTimer);
      showTimer = setTimeout(function () {
        dd.classList.add('open-hover');
        toggle.setAttribute('aria-expanded', 'true');
        // dọn "show" nếu vô tình có
        dd.classList.remove('show');
        menu.classList.remove('show');
      }, 150);
    });

    // Hover out (desktop): đóng mượt (đảo chiều)
    dd.addEventListener('mouseleave', function () {
      if (window.innerWidth < NAV_BREAKPOINT) return;
      clearTimeout(showTimer);
      hideTimer = setTimeout(function () {
        dd.classList.remove('open-hover');
        toggle.setAttribute('aria-expanded', 'false');
        // đảm bảo không kẹt trạng thái bootstrap
        dd.classList.remove('show');
        menu.classList.remove('show');
      }, 180);
    });

    // Desktop: chặn click vào nút toggle để không bật cơ chế .show của Bootstrap
    toggle.addEventListener('click', function (e) {
      if (window.innerWidth >= NAV_BREAKPOINT) {
        e.preventDefault();
        e.stopPropagation();
      }
    });
  });

  // Khi đổi kích thước từ mobile ↔ desktop: dọn trạng thái cũ
  window.addEventListener('resize', function () {
    document.querySelectorAll('.navbar .dropdown').forEach(function (dd) {
      const toggle = dd.querySelector('[data-bs-toggle="dropdown"]');
      const menu = dd.querySelector('.dropdown-menu');
      if (window.innerWidth < 992) {
        dd.classList.remove('open-hover');
        toggle && toggle.setAttribute('aria-expanded', 'false');
      } else {
        // trên desktop, dọn .show để chỉ còn open-hover điều khiển
        dd.classList.remove('show');
        menu && menu.classList.remove('show');
      }
    });
  });
});
