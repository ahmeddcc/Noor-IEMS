<?php
namespace App\Core;

use App\Models\Setting;

/**
 * Telegram Notifier - إرسال إشعارات Telegram
 * يعمل تلقائيًا مع النظام
 * 
 * @version 2.0 - Enhanced with code snippets & file attachments
 */
class TelegramNotifier
{
    private static $botToken;
    private static $chatId;
    private static $systemName;
    
    /**
     * تهيئة الإعدادات
     */
    private static function init()
    {
        if (self::$botToken === null) {
            self::$botToken = Setting::get('telegram_bot_token', '');
            self::$chatId = Setting::get('telegram_chat_id', '');
            self::$systemName = Setting::get('company_name', 'النظام');
        }
    }
    
    /**
     * إرسال رسالة نصية
     */
    public static function send($message, $parseMode = 'HTML')
    {
        self::init();
        
        if (empty(self::$botToken) || empty(self::$chatId)) {
            return false;
        }
        
        $url = "https://api.telegram.org/bot" . self::$botToken . "/sendMessage";
        
        $data = [
            'chat_id' => self::$chatId,
            'text' => $message,
            'parse_mode' => $parseMode
        ];
        
        return self::makeRequest($url, $data);
    }
    
    /**
     * إرسال صورة مع رسالة
     */
    public static function sendPhoto($imagePath, $caption = '')
    {
        self::init();
        
        if (empty(self::$botToken) || empty(self::$chatId)) {
            return false;
        }
        
        $url = "https://api.telegram.org/bot" . self::$botToken . "/sendPhoto";
        
        $data = [
            'chat_id' => self::$chatId,
            'photo' => new \CURLFile($imagePath),
            'caption' => $caption,
            'parse_mode' => 'HTML'
        ];
        
        return self::makeRequest($url, $data, true);
    }
    
    /**
     * إرسال ملف نصي (سهل النسخ)
     */
    public static function sendDocument($content, $filename = 'error_details.txt', $caption = '')
    {
        self::init();
        
        if (empty(self::$botToken) || empty(self::$chatId)) {
            return false;
        }
        
        // إنشاء ملف مؤقت
        $tempFile = sys_get_temp_dir() . '/' . $filename;
        file_put_contents($tempFile, $content);
        
        $url = "https://api.telegram.org/bot" . self::$botToken . "/sendDocument";
        
        $data = [
            'chat_id' => self::$chatId,
            'document' => new \CURLFile($tempFile, 'text/plain', $filename),
            'caption' => $caption,
            'parse_mode' => 'HTML'
        ];
        
        $result = self::makeRequest($url, $data, true);
        
        // حذف الملف المؤقت
        @unlink($tempFile);
        
        return $result;
    }
    
    /**
     * جلب لقطة من الكود حول السطر المحدد
     */
    public static function getCodeSnippet($file, $line, $padding = 5)
    {
        if (!file_exists($file) || !is_readable($file)) {
            return "⚠️ الملف غير قابل للقراءة";
        }
        
        $lines = file($file);
        $totalLines = count($lines);
        $start = max(0, $line - $padding - 1);
        $end = min($totalLines, $line + $padding);
        $snippet = "";
        
        for ($i = $start; $i < $end; $i++) {
            $currentLineNum = $i + 1;
            $marker = ($currentLineNum == $line) ? ">> " : "   ";
            $snippet .= sprintf("%s%4d | %s", $marker, $currentLineNum, $lines[$i]);
        }
        
        return $snippet;
    }
    
    /**
     * إشعار خطأ محسّن
     * يُرسل رسالة منسقة + ملف نصي للنسخ السهل
     */
    public static function notifyError($error, $file, $line, $trace = '', $suggestion = '')
    {
        self::init();
        
        $priority = self::getPriority($error);
        $emoji = self::getPriorityEmoji($priority);
        $priorityBar = self::getPriorityBar($priority);
        $timestamp = date('Y-m-d H:i:s');
        $url = $_SERVER['REQUEST_URI'] ?? 'CLI';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        
        // جلب لقطة الكود
        $codeSnippet = self::getCodeSnippet($file, $line);
        
        // ═══════════════════════════════════════════════════
        // 📱 الرسالة المنسقة (مع code blocks للنسخ السهل)
        // ═══════════════════════════════════════════════════
        $message = "$emoji <b>خطأ $priority - " . self::$systemName . "</b> $priorityBar\n\n";
        
        // معلومات الموقع
        $message .= "━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "📍 <b>مكان الخطأ:</b>\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "📄 الملف: <code>" . basename($file) . "</code>\n";
        $message .= "📌 السطر: <code>$line</code>\n";
        $message .= "🌐 الصفحة: <code>$url</code>\n";
        
        // رسالة الخطأ (في code block للنسخ)
        $message .= "\n━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "❌ <b>الخطأ:</b>\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "<code>" . htmlspecialchars(substr($error, 0, 400)) . "</code>\n";
        
        // لقطة الكود (مختصرة)
        if (!empty($codeSnippet)) {
            $message .= "\n━━━━━━━━━━━━━━━━━━━━━━\n";
            $message .= "📸 <b>الكود:</b>\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━━━\n";
            $message .= "<pre>" . htmlspecialchars(substr($codeSnippet, 0, 500)) . "</pre>\n";
        }
        
        // اقتراح الحل
        if (!empty($suggestion)) {
            $message .= "\n━━━━━━━━━━━━━━━━━━━━━━\n";
            $message .= "💡 <b>خطوات الإصلاح:</b>\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━━━\n";
            $message .= $suggestion . "\n";
        }
        
        $message .= "\n⏰ $timestamp";
        $message .= "\n\n📎 <i>التفاصيل الكاملة في الملف المرفق ⬇️</i>";
        
        // إرسال الرسالة المنسقة
        $sent = self::send($message);
        
        // ═══════════════════════════════════════════════════
        // 📄 الملف النصي (للنسخ الكامل)
        // ═══════════════════════════════════════════════════
        $fileContent = "╔══════════════════════════════════════════════════════════════╗\n";
        $fileContent .= "║             تقرير الخطأ - " . self::$systemName . "\n";
        $fileContent .= "║             $timestamp\n";
        $fileContent .= "╚══════════════════════════════════════════════════════════════╝\n\n";
        
        $fileContent .= "▶ الأولوية: $priority\n";
        $fileContent .= "▶ الملف: $file\n";
        $fileContent .= "▶ السطر: $line\n";
        $fileContent .= "▶ الصفحة: $url\n";
        $fileContent .= "▶ IP: $ip\n\n";
        
        $fileContent .= "═══════════════════════════════════════════════════════════════\n";
        $fileContent .= "رسالة الخطأ:\n";
        $fileContent .= "═══════════════════════════════════════════════════════════════\n";
        $fileContent .= $error . "\n\n";
        
        if (!empty($codeSnippet)) {
            $fileContent .= "═══════════════════════════════════════════════════════════════\n";
            $fileContent .= "لقطة الكود:\n";
            $fileContent .= "═══════════════════════════════════════════════════════════════\n";
            $fileContent .= $codeSnippet . "\n\n";
        }
        
        if (!empty($trace)) {
            $fileContent .= "═══════════════════════════════════════════════════════════════\n";
            $fileContent .= "Stack Trace:\n";
            $fileContent .= "═══════════════════════════════════════════════════════════════\n";
            $fileContent .= $trace . "\n\n";
        }
        
        if (!empty($suggestion)) {
            $fileContent .= "═══════════════════════════════════════════════════════════════\n";
            $fileContent .= "اقتراح الحل:\n";
            $fileContent .= "═══════════════════════════════════════════════════════════════\n";
            $fileContent .= $suggestion . "\n";
        }
        
        // إرسال الملف النصي
        $filename = 'error_' . date('Y-m-d_H-i-s') . '.txt';
        self::sendDocument($fileContent, $filename, '📄 <b>التفاصيل الكاملة للنسخ</b>');
        
        return $sent;
    }
    
    /**
     * إشعار تسجيل دخول
     */
    public static function notifyLogin($username, $ip = null)
    {
        self::init();
        
        $ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? 'غير معروف');
        
        $message = "🟢 <b>تسجيل دخول - " . self::$systemName . "</b>\n\n";
        $message .= "👤 المستخدم: <b>$username</b>\n";
        $message .= "🌐 IP: <code>$ip</code>\n";
        $message .= "⏰ " . date('Y-m-d H:i:s');
        
        return self::send($message);
    }
    
    /**
     * إشعار تسجيل خروج
     */
    public static function notifyLogout($username, $sessionDuration = null)
    {
        self::init();
        
        $message = "🔵 <b>تسجيل خروج - " . self::$systemName . "</b>\n\n";
        $message .= "👤 المستخدم: <b>$username</b>\n";
        
        if ($sessionDuration) {
            $message .= "⏱️ مدة الجلسة: $sessionDuration\n";
        }
        
        $message .= "⏰ " . date('Y-m-d H:i:s');
        
        return self::send($message);
    }
    
    /**
     * تحديد أولوية الخطأ
     */
    private static function getPriority($error)
    {
        $error = strtolower($error);
        
        if (strpos($error, 'fatal') !== false || strpos($error, 'database') !== false) {
            return 'حرج';
        }
        if (strpos($error, 'exception') !== false || strpos($error, '500') !== false) {
            return 'عالي';
        }
        if (strpos($error, 'warning') !== false || strpos($error, 'undefined') !== false) {
            return 'متوسط';
        }
        return 'منخفض';
    }
    
    /**
     * إيموجي الأولوية
     */
    private static function getPriorityEmoji($priority)
    {
        $emojis = [
            'حرج' => '🔴',
            'عالي' => '🟠',
            'متوسط' => '🟡',
            'منخفض' => '🟢'
        ];
        return $emojis[$priority] ?? '⚪';
    }
    
    /**
     * شريط الأولوية المرئي
     */
    private static function getPriorityBar($priority)
    {
        $bars = [
            'حرج' => '🔴🔴🔴',
            'عالي' => '🟠🟠⚪',
            'متوسط' => '🟡⚪⚪',
            'منخفض' => '🟢⚪⚪'
        ];
        return $bars[$priority] ?? '⚪⚪⚪';
    }
    
    /**
     * إرسال طلب HTTP
     */
    private static function makeRequest($url, $data, $isMultipart = false)
    {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        if ($isMultipart) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        return $result !== false;
    }
}
