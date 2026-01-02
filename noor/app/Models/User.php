<?php
// app/Models/User.php

namespace App\Models;

use App\Core\Database;

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function login($username, $password) {
        $sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
        $user = $this->db->fetch($sql, [$username]);

        if ($user) {
            // التحقق من حالة المستخدم
            if ($user['status'] !== 'active') {
                return ['success' => false, 'message' => 'الحساب موقوف، يرجى مراجعة الإدارة.'];
            }

            // تحقق الباسورد
            if (password_verify($password, $user['password_hash'])) {
                // تحديث آخر تسجيل دخول
                $this->db->query("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?", [$user['id']]);
                
                return ['success' => true, 'user' => $user];
            }
        }

        return ['success' => false, 'message' => 'اسم المستخدم أو كلمة المرور غير صحيحة.'];
    }

    public function getUserById($id) {
        return $this->db->fetch("SELECT id, username, role, status, created_at, last_login FROM users WHERE id = ?", [$id]);
    }

    /**
     * جلب جميع المستخدمين
     */
    public function getAll() {
        return $this->db->fetchAll("SELECT id, username, role, status, created_at, last_login FROM users ORDER BY id");
    }

    /**
     * إنشاء مستخدم جديد
     */
    public function create($data) {
        $sql = "INSERT INTO users (username, password_hash, role, status, created_at) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $this->db->query($sql, [
            $data['username'],
            $passwordHash,
            $data['role'] ?? 'user',
            $data['status'] ?? 'active'
        ]);
        
        return $this->db->getConnection()->lastInsertId();
    }

    /**
     * تحديث مستخدم
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        if (isset($data['username'])) {
            $fields[] = "username = ?";
            $params[] = $data['username'];
        }
        
        if (isset($data['role'])) {
            $fields[] = "role = ?";
            $params[] = $data['role'];
        }
        
        if (isset($data['status'])) {
            $fields[] = "status = ?";
            $params[] = $data['status'];
        }
        
        if (!empty($data['password'])) {
            $fields[] = "password_hash = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
        
        return $this->db->query($sql, $params);
    }

    /**
     * حذف مستخدم
     */
    public function delete($id) {
        // لا يمكن حذف آخر admin
        $admins = $this->db->fetchAll("SELECT id FROM users WHERE role = 'admin' AND id != ?", [$id]);
        $user = $this->getUserById($id);
        
        if ($user['role'] === 'admin' && empty($admins)) {
            return ['success' => false, 'message' => 'لا يمكن حذف آخر مدير في النظام'];
        }
        
        $this->db->query("DELETE FROM users WHERE id = ?", [$id]);
        return ['success' => true];
    }

    /**
     * التحقق من وجود اسم مستخدم
     */
    public function usernameExists($username, $excludeId = null) {
        if ($excludeId) {
            $result = $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE username = ? AND id != ?", [$username, $excludeId]);
        } else {
            $result = $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE username = ?", [$username]);
        }
        return $result['count'] > 0;
    }

    /**
     * تغيير كلمة المرور
     */
    public function changePassword($userId, $newPassword) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->db->query("UPDATE users SET password_hash = ? WHERE id = ?", [$hash, $userId]);
    }

    /**
     * جلب صلاحيات المستخدم (أسماء فقط)
     */
    public function getPermissions($userId) {
        $sql = "SELECT p.name FROM permissions p
                INNER JOIN user_permissions up ON p.id = up.permission_id
                WHERE up.user_id = ?";
        $result = $this->db->fetchAll($sql, [$userId]);
        return array_column($result, 'name');
    }

    /**
     * التحقق إذا كان لدى المستخدم صلاحية
     */
    public function hasPermission($userId, $permissionName) {
        // Admin لديه كل الصلاحيات
        $user = $this->getUserById($userId);
        if ($user && $user['role'] === 'admin') {
            return true;
        }
        
        $sql = "SELECT COUNT(*) as count FROM user_permissions up
                INNER JOIN permissions p ON up.permission_id = p.id
                WHERE up.user_id = ? AND p.name = ?";
        $result = $this->db->fetch($sql, [$userId, $permissionName]);
        return $result['count'] > 0;
    }
}
