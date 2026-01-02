<?php
// config.php

// منع الدخول المباشر للملف
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

// إعدادات التطبيق
define('APP_NAME', 'نظام Noor للإدارة المالية');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/noor'); // قم بتغييره حسب الرابط المحلي

// إعدادات قاعدة البيانات
define('DB_PATH', ROOT_PATH . '/db/database.sqlite');

// إعدادات المنطقة الزمنية
date_default_timezone_set('Africa/Cairo');

// إعدادات الجلسة (Session)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_lifetime', 60 * 60 * 12); // 12 ساعة

// إعدادات عرض الأخطاء (تلقائي حسب البيئة)
$isDevelopment = (
    $_SERVER['SERVER_NAME'] === 'localhost' || 
    $_SERVER['SERVER_NAME'] === '127.0.0.1' ||
    strpos($_SERVER['SERVER_NAME'] ?? '', '.local') !== false
);

if ($isDevelopment) {
    // بيئة التطوير - عرض الأخطاء للمطور
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // بيئة الإنتاج - إخفاء الأخطاء وتسجيلها في ملف
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . '/logs/error.log');
}

// دوال مساعدة عامة
function base_url($path = '') {
    return APP_URL . '/' . ltrim($path, '/');
}

function redirect($path) {
    header("Location: " . base_url($path));
    exit;
}

function asset($path) {
    return base_url('assets/' . ltrim($path, '/'));
}

// دالة لتنظيف المدخلات (Basic XSS Protection)
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
