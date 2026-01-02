<?php
// app/Core/Session.php
namespace App\Core;

class Session {
    public static function start() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        self::checkTimeout();
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public static function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public static function destroy() {
        session_unset();
        session_destroy();
    }

    // تجديد معرف الجلسة (للحماية من Session Fixation)
    public static function regenerate() {
        session_regenerate_id(true);
    }

    // تعيين رسالة مؤقتة (Flash Message)
    public static function setFlash($key, $message, $type = 'success') {
        $_SESSION['flash'][$key] = [
            'message' => $message,
            'type' => $type
        ];
    }

    // جلب رسالة مؤقتة وحذفها
    public static function getFlash($key) {
        if (isset($_SESSION['flash'][$key])) {
            $flash = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $flash;
        }
        return null;
    }

    // التحقق من تسجيل الدخول
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    // التأكد من أن المستخدم مدير
    public static function isAdmin() {
        if (!self::isLoggedIn()) return false;
        $role = strtolower(trim($_SESSION['role'] ?? ''));
        return ($role === 'admin');
    }

    // التأكد من أن المستخدم مدير أو manager
    public static function isManager() {
        return self::isLoggedIn() && in_array($_SESSION['role'], ['admin', 'manager']);
    }

    // تحميل صلاحيات المستخدم من قاعدة البيانات
    public static function loadPermissions($userId) {
        $userModel = new \App\Models\User();
        $_SESSION['permissions'] = $userModel->getPermissions($userId);
    }

    // التحقق من وجود صلاحية
    public static function hasPermission($permission) {
        // Admin لديه كل الصلاحيات دائماً
        if (self::isAdmin()) {
            return true;
        }
        
        // التحقق من الصلاحيات المحملة في الجلسة
        $perms = $_SESSION['permissions'] ?? [];
        return in_array($permission, $perms);
    }

    // التحقق من عدة صلاحيات (OR)
    public static function hasAnyPermission(array $permissions) {
        foreach ($permissions as $perm) {
            if (self::hasPermission($perm)) {
                return true;
            }
        }
        return false;
    }

    // التحقق من كل الصلاحيات (AND)
    public static function hasAllPermissions(array $permissions) {
        foreach ($permissions as $perm) {
            if (!self::hasPermission($perm)) {
                return false;
            }
        }
        return true;
    }


    // التحقق من انتهاء الجلسة
    public static function checkTimeout() {
        $timeout = 30 * 60; // 30 دقيقة افتراضيًا

        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
            // مسح بيانات الجلسة مع الاحتفاظ بالقدرة على تعيين flash message
            session_unset();
            session_regenerate_id(true);
            
            // تعيين رسالة انتهاء الجلسة (ستظهر مرة واحدة فقط)
            $_SESSION['flash']['timeout'] = [
                'message' => 'تم تسجيل الخروج لعدم النشاط.',
                'type' => 'warning'
            ];
            
            redirect('index.php?page=login');
        }
        $_SESSION['last_activity'] = time();
    }

    // ===== نظام CSRF Protection =====
    
    // توليد CSRF Token
    public static function generateCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // الحصول على حقل CSRF المخفي للنماذج
    public static function csrfField() {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    // التحقق من صحة CSRF Token
    public static function validateCsrfToken($token = null) {
        if ($token === null) {
            $token = $_POST['csrf_token'] ?? '';
        }
        
        if (!isset($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    // التحقق من CSRF مع إظهار خطأ
    public static function verifyCsrf() {
        if (!self::validateCsrfToken()) {
            self::setFlash('error', 'خطأ في التحقق من الأمان. الرجاء إعادة المحاولة.');
            return false;
        }
        return true;
    }
    // التحقق من الجلسة وبدؤها (توجيه مباشر إذا لم يكن مسجلاً)
    public static function check() {
        self::start();
        if (!self::isLoggedIn()) {
            redirect('index.php?page=login');
        }
    }
}
