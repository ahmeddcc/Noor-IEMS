<?php
// app/Models/Category.php

namespace App\Models;

use App\Core\Database;

class Category {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->ensureSortOrderColumn();
    }

    // التأكد من وجود عمود الترتيب
    private function ensureSortOrderColumn() {
        try {
            // فحص إذا كان العمود موجوداً
            $columns = $this->db->fetchAll("PRAGMA table_info(categories)");
            $hasColumn = false;
            foreach ($columns as $col) {
                if ($col['name'] === 'sort_order') {
                    $hasColumn = true;
                    break;
                }
            }
            
            if (!$hasColumn) {
                $this->db->query("ALTER TABLE categories ADD COLUMN sort_order INTEGER DEFAULT 0");
                // تعيين ترتيب افتراضي بناءً على ID
                $this->db->query("UPDATE categories SET sort_order = id WHERE sort_order = 0 OR sort_order IS NULL");
            }
        } catch (\Exception $e) {
            // تجاهل الخطأ إذا كان العمود موجوداً بالفعل
        }
    }

    // جلب كل التصنيفات النشطة
    public function getAllActive() {
        return $this->db->fetchAll("SELECT * FROM categories WHERE is_active = 1 OR is_mandatory = 1 ORDER BY sort_order ASC, id ASC");
    }

    // جلب كل التصنيفات (للإعدادات)
    public function getAll() {
        return $this->db->fetchAll("SELECT * FROM categories ORDER BY sort_order ASC, id ASC");
    }

    // إضافة تصنيف جديد
    public function add($name) {
        try {
            // الحصول على أعلى ترتيب حالي
            $maxOrder = $this->db->fetch("SELECT MAX(sort_order) as max_order FROM categories");
            $newOrder = ($maxOrder['max_order'] ?? 0) + 1;
            
            $this->db->query("INSERT INTO categories (name, sort_order) VALUES (?, ?)", [$name, $newOrder]);
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    // تحديث تصنيف
    public function update($id, $name, $isActive) {
        // لا يمكن تعطيل التصنيف الإجباري
        $category = $this->getById($id);
        if ($category['is_mandatory'] == 1) {
            $isActive = 1; 
        }

        try {
            $this->db->query("UPDATE categories SET name = ?, is_active = ? WHERE id = ?", [$name, $isActive, $id]);
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function getById($id) {
        return $this->db->fetch("SELECT * FROM categories WHERE id = ?", [$id]);
    }

    public function delete($id) {
        // التحقق من الاستخدام قبل الحذف (يمكن فقط التعطيل إذا كان مستخدماً)
        // هنا سنقوم بالحذف فقط إذا لم يكن مرتبطاً بعملاء، وإلا نرجع خطأ
        $count = $this->db->fetch("SELECT count(*) as c FROM clients WHERE category_id = ?", [$id]);
        if ($count['c'] > 0) {
            return false; // لا يمكن الحذف، مرتبط بعملاء
        }
        
        $cat = $this->getById($id);
        if ($cat['is_mandatory'] == 1) {
            return false; // لا يمكن حذف الإجباري
        }

        $this->db->query("DELETE FROM categories WHERE id = ?", [$id]);
        return true;
    }

    // تحديث ترتيب التصنيفات
    public function updateOrder($orderedIds) {
        try {
            foreach ($orderedIds as $index => $id) {
                $this->db->query("UPDATE categories SET sort_order = ? WHERE id = ?", [$index, $id]);
            }
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }
}
