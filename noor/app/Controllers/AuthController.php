<?php
// app/Controllers/AuthController.php

namespace App\Controllers;

use App\Core\Session;
use App\Core\TelegramNotifier;
use App\Models\User;

class AuthController {
    public function index() {
        // إذا كان مسجل الدخول بالفعل، يذهب للرئيسية
        if (Session::isLoggedIn()) {
            redirect('index.php?page=dashboard');
        }
        
        // عرض صفحة الدخول
        require_once ROOT_PATH . '/app/Views/auth/login.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // التحقق من CSRF Token
            if (!Session::verifyCsrf()) {
                // إعادة توليد CSRF token جديد للمحاولة القادمة
                unset($_SESSION['csrf_token']);
                Session::generateCsrfToken();
                Session::setFlash('error', 'انتهت صلاحية الجلسة. يرجى المحاولة مرة أخرى.');
                redirect('index.php?page=login');
            }

            // Rate Limiting Check
            $rateLimiter = new \App\Core\RateLimiter();
            $ip = $_SERVER['REMOTE_ADDR'];
            
            $limitCheck = $rateLimiter->check($ip);
            if (!$limitCheck['allowed']) {
                Session::setFlash('error', $limitCheck['message']);
                redirect('index.php?page=login');
            }

            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                Session::setFlash('error', 'الرجاء إدخال اسم المستخدم وكلمة المرور');
                redirect('index.php?page=login');
            }

            $userModel = new User();
            $result = $userModel->login($username, $password);

            if ($result['success']) {
                // Clear rate limit on success
                $rateLimiter->clear($ip);

                // تجديد معرف الجلسة للحماية من Session Fixation
                Session::regenerate();
                
                // تخزين بيانات الجلسة
                Session::set('user_id', $result['user']['id']);
                Session::set('username', $result['user']['username']);
                Session::set('role', $result['user']['role']);
                Session::set('login_time', time()); // لحساب مدة الجلسة
                
                // تحميل صلاحيات المستخدم
                Session::loadPermissions($result['user']['id']);
                
                // إشعار Telegram بتسجيل الدخول
                TelegramNotifier::notifyLogin($result['user']['username']);
                
                redirect('index.php?page=dashboard');
            } else {
                // Record failed attempt
                $rateLimiter->increment($ip);

                Session::setFlash('error', $result['message']);
                redirect('index.php?page=login');
            }
        }
    }

    public function logout() {
        $username = Session::get('username');
        $loginTime = Session::get('login_time');
        
        // حساب مدة الجلسة
        $duration = null;
        if ($loginTime) {
            $seconds = time() - $loginTime;
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $duration = ($hours > 0 ? "$hours ساعة " : "") . "$minutes دقيقة";
        }
        
        // إشعار Telegram بتسجيل الخروج
        if ($username) {
            TelegramNotifier::notifyLogout($username, $duration);
        }
        
        // مسح بيانات المستخدم فقط مع الاحتفاظ بجلسة جديدة
        Session::remove('user_id');
        Session::remove('username');
        Session::remove('role');
        Session::remove('login_time');
        Session::remove('permissions');
        
        // تجديد معرف الجلسة مع الاحتفاظ بـ CSRF
        Session::regenerate();
        
        // توليد CSRF token جديد للنموذج
        unset($_SESSION['csrf_token']);
        Session::generateCsrfToken();
        
        // بدلاً من التوجيه المباشر، نعرض صفحة الخروج
        $data = [
            'duration' => $duration
        ];
        
        require_once ROOT_PATH . '/app/Views/auth/logout.php';
        exit;
    }
    
    /**
     * Alias for login() - handles form action=submit
     */
    public function submit() {
        $this->login();
    }
}

