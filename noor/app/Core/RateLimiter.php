<?php

namespace App\Core;

class RateLimiter {
    private $db;
    private $maxAttempts = 5;
    private $lockoutTime = 900; // 15 minutes in seconds

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // التحقق مما إذا كان الـ IP محظوراً
    public function check($ip) {
        // تنظيف المحاولات القديمة
        $this->cleanup($ip);

        $sql = "SELECT attempts, locked_until FROM login_attempts WHERE ip_address = ?";
        $result = $this->db->fetch($sql, [$ip]);

        if ($result && $result['locked_until']) {
            if (strtotime($result['locked_until']) > time()) {
                $remaining = ceil((strtotime($result['locked_until']) - time()) / 60);
                return [
                    'allowed' => false,
                    'message' => "تم حظر المحاولات مؤقتاً بسبب تكرار الخطأ. حاول بعد $remaining دقيقة."
                ];
            }
        }

        return ['allowed' => true];
    }

    // تسجيل محاولة فاشلة
    public function increment($ip) {
        $sql = "SELECT attempts FROM login_attempts WHERE ip_address = ?";
        $result = $this->db->fetch($sql, [$ip]);

        if ($result) {
            $attempts = $result['attempts'] + 1;
            
            if ($attempts >= $this->maxAttempts) {
                // حظر لمدة 15 دقيقة
                $lockedUntil = date('Y-m-d H:i:s', time() + $this->lockoutTime);
                $updateSql = "UPDATE login_attempts SET attempts = ?, locked_until = ?, last_attempt_at = CURRENT_TIMESTAMP WHERE ip_address = ?";
                $this->db->query($updateSql, [$attempts, $lockedUntil, $ip]);
            } else {
                $updateSql = "UPDATE login_attempts SET attempts = ?, last_attempt_at = CURRENT_TIMESTAMP WHERE ip_address = ?";
                $this->db->query($updateSql, [$attempts, $ip]);
            }
        } else {
            $insertSql = "INSERT INTO login_attempts (ip_address) VALUES (?)";
            $this->db->query($insertSql, [$ip]);
        }
    }

    // مسح المحاولات عند النجاح
    public function clear($ip) {
        $sql = "DELETE FROM login_attempts WHERE ip_address = ?";
        $this->db->query($sql, [$ip]);
    }

    // تنظيف السجلات القديمة (اختياري، يمكن استدعاؤه دورياً)
    private function cleanup($ip) {
        // إذا مر وقت الحظر، صفر العداد
        // أو يمكننا فقط التحقق من locked_until في check() وهذا كافٍ للمنطق الأساسي
        // هنا سنقوم بإزالة القفل إذا انتهى وقته لتصفية العداد
        $sql = "SELECT locked_until FROM login_attempts WHERE ip_address = ?";
        $result = $this->db->fetch($sql, [$ip]);

        if ($result && $result['locked_until'] && strtotime($result['locked_until']) <= time()) {
             $this->clear($ip);
        }
    }
}
