<?php
// app/Models/Permission.php

namespace App\Models;

use App\Core\Database;

class Permission {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * جلب جميع الصلاحيات
     */
    public function getAll() {
        return $this->db->fetchAll("SELECT * FROM permissions ORDER BY category, id");
    }

    /**
     * جلب الصلاحيات مجمعة حسب الفئة
     */
    public function getAllGroupedByCategory() {
        $permissions = $this->getAll();
        $grouped = [];
        
        foreach ($permissions as $perm) {
            $category = $perm['category'];
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $perm;
        }
        
        return $grouped;
    }

    /**
     * جلب صلاحية بالاسم
     */
    public function getByName($name) {
        return $this->db->fetch("SELECT * FROM permissions WHERE name = ?", [$name]);
    }

    /**
     * جلب صلاحية بالـ ID
     */
    public function getById($id) {
        return $this->db->fetch("SELECT * FROM permissions WHERE id = ?", [$id]);
    }

    /**
     * جلب صلاحيات مستخدم معين
     */
    public function getUserPermissions($userId) {
        $sql = "SELECT p.* FROM permissions p
                INNER JOIN user_permissions up ON p.id = up.permission_id
                WHERE up.user_id = ?
                ORDER BY p.category, p.id";
        return $this->db->fetchAll($sql, [$userId]);
    }

    /**
     * جلب أسماء صلاحيات مستخدم (للتخزين في الجلسة)
     */
    public function getUserPermissionNames($userId) {
        $sql = "SELECT p.name FROM permissions p
                INNER JOIN user_permissions up ON p.id = up.permission_id
                WHERE up.user_id = ?";
        $result = $this->db->fetchAll($sql, [$userId]);
        return array_column($result, 'name');
    }

    /**
     * تعيين صلاحيات لمستخدم
     */
    public function setUserPermissions($userId, array $permissionIds) {
        $pdo = $this->db->getConnection();
        $inTransaction = $pdo->inTransaction();
        
        try {
            if (!$inTransaction) {
                $pdo->beginTransaction();
            }
            
            // حذف الصلاحيات القديمة
            $this->db->query("DELETE FROM user_permissions WHERE user_id = ?", [$userId]);
            
            // إضافة الصلاحيات الجديدة
            if (!empty($permissionIds)) {
                $stmt = $pdo->prepare("INSERT INTO user_permissions (user_id, permission_id) VALUES (?, ?)");
                foreach ($permissionIds as $permId) {
                    $stmt->execute([$userId, $permId]);
                }
            }
            
            if (!$inTransaction) {
                $pdo->commit();
            }
            return true;
        } catch (\Exception $e) {
            if (!$inTransaction) {
                $pdo->rollBack();
            }
            throw new \Exception("فشل حفظ الصلاحيات: " . $e->getMessage());
        }
    }

    /**
     * التحقق إذا كان المستخدم لديه صلاحية معينة
     */
    public function userHasPermission($userId, $permissionName) {
        $sql = "SELECT COUNT(*) FROM user_permissions up
                INNER JOIN permissions p ON up.permission_id = p.id
                WHERE up.user_id = ? AND p.name = ?";
        return $this->db->fetch($sql, [$userId, $permissionName])['COUNT(*)'] > 0;
    }

    /**
     * تعيين جميع الصلاحيات لمستخدم (للـ Admin)
     */
    public function grantAllPermissions($userId) {
        $allPerms = $this->getAll();
        $permIds = array_column($allPerms, 'id');
        return $this->setUserPermissions($userId, $permIds);
    }

    /**
     * حذف جميع صلاحيات مستخدم
     */
    public function revokeAllPermissions($userId) {
        return $this->db->query("DELETE FROM user_permissions WHERE user_id = ?", [$userId]);
    }

    /**
     * الحصول على أسماء الفئات بالعربي
     */
    public static function getCategoryLabels() {
        return [
            'users' => 'المستخدمون',
            'clients' => 'العملاء',
            'transactions' => 'المعاملات',
            'reports' => 'التقارير',
            'dashboard' => 'لوحة التحكم',
            'audit' => 'سجل الأحداث',
            'settings' => 'الإعدادات'
        ];
    }
}
