<?php
class Session
{
    private static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public static function init(): void { self::start(); }

    // Key-Value
    public static function set($key, $value): void { self::start(); $_SESSION[$key] = $value; }
    public static function get($key, $default=null) { self::start(); return $_SESSION[$key] ?? $default; }
    public static function remove($key): void { self::start(); unset($_SESSION[$key]); }

    // Auth
    public static function isLoggedIn(): bool { self::start(); return isset($_SESSION['user']) && !empty($_SESSION['user']['id']); }

    public static function setUser(array $user): void
    {
        self::start();
        $_SESSION['user'] = [
            'id'      => $user['id']      ?? null,
            'name'    => $user['name']    ?? null,
            'email'   => $user['email']   ?? null,
            'phone'   => $user['phone']   ?? null,
            'address' => $user['address'] ?? null,
            'avatar'  => $user['avatar']  ?? null,
            'role'    => $user['role']    ?? 'customer',
        ];
        // Đồng bộ các biến lẻ để tương thích với các view gọi Session::get('user_name')
        $_SESSION['user_id']    = $user['id']    ?? null;
        $_SESSION['user_name']  = $user['name']  ?? null;
        $_SESSION['user_email'] = $user['email'] ?? null;
        $_SESSION['user_role']  = $user['role']  ?? 'customer';
    }

    public static function user(): ?array { self::start(); return $_SESSION['user'] ?? null; }
    public static function getUserId() { self::start(); return $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? null; }
    public static function getUserName(): ?string { self::start(); return $_SESSION['user']['name'] ?? $_SESSION['user_name'] ?? null; }
    public static function getUserEmail(): ?string { self::start(); return $_SESSION['user']['email'] ?? $_SESSION['user_email'] ?? null; }
    public static function getUserRole(): ?string { self::start(); return $_SESSION['user']['role'] ?? $_SESSION['user_role'] ?? null; }

    // >>> Thêm hàm isAdmin() để Middleware gọi
    public static function isAdmin(): bool
    {
        self::start();
        $role = self::getUserRole();
        return in_array($role, ['admin', 'superadmin'], true);
    }

    public static function clearUser(): void
    {
        self::start();
        unset(
            $_SESSION['user'],
            $_SESSION['user_id'],
            $_SESSION['user_name'],
            $_SESSION['user_email'],
            $_SESSION['user_role'],
            $_SESSION['cart']
        );
    }
    public static function destroy(): void { self::start(); session_unset(); session_destroy(); }

    // Flash
    public static function setFlash($key, $message): void { self::start(); $_SESSION['flash'][$key] = $message; }
    public static function getFlash($key) { self::start(); $m=$_SESSION['flash'][$key]??null; if(isset($_SESSION['flash'][$key])) unset($_SESSION['flash'][$key]); return $m; }
    public static function hasFlash($key): bool { self::start(); return isset($_SESSION['flash'][$key]); }

    // Cart (Yêu cầu đăng nhập mới có giỏ hàng)
    public static function getCart(): array { 
        self::start(); 
        if (!self::isLoggedIn()) return [];
        return $_SESSION['cart'] ?? []; 
    }
    public static function getCartCount(): int { 
        self::start(); 
        if (!self::isLoggedIn()) return 0;
        $t=0; 
        foreach(($_SESSION['cart']??[]) as $q){$t+=(int)$q;} 
        return $t; 
    }
    public static function getCartDistinctCount(): int { 
        self::start(); 
        if (!self::isLoggedIn()) return 0;
        return isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; 
    }
    public static function addToCart($pid, $qty=1): void { 
        self::start(); 
        if (!self::isLoggedIn()) return;
        if(!isset($_SESSION['cart'][$pid])) $_SESSION['cart'][$pid]=0; 
        $_SESSION['cart'][$pid]+=(int)$qty; 
    }
    
    public static function updateCart($pid, $qty): void { 
        self::start(); 
        if (!self::isLoggedIn()) return;
        if(!isset($_SESSION['cart'])) $_SESSION['cart']=[]; 
        if((int)$qty > 0) $_SESSION['cart'][$pid]=(int)$qty; 
        else unset($_SESSION['cart'][$pid]); 
    }
    
    public static function removeFromCart($pid): void { 
        self::start(); 
        if(isset($_SESSION['cart'][$pid])) unset($_SESSION['cart'][$pid]); 
    }
    
    public static function clearCart(): void { self::start(); unset($_SESSION['cart']); }
}
