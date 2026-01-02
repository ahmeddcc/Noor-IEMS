<?php
// app/Models/Client.php

namespace App\Models;

use App\Core\Database;

class Client {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll() {
        $sql = "SELECT c.*, cat.name as category_name 
                FROM clients c 
                LEFT JOIN categories cat ON c.category_id = cat.id 
                ORDER BY c.created_at DESC";
        return $this->db->fetchAll($sql);
    }

    public function getById($id) {
        return $this->db->fetch("SELECT * FROM clients WHERE id = ?", [$id]);
    }

    public function add($data) {
        $sql = "INSERT INTO clients (name, phone, address, category_id, category_custom, status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        try {
            $this->db->query($sql, [
                $data['name'],
                $data['phone'] ?? '',
                $data['address'] ?? '',
                $data['category_id'],
                $data['category_custom'] ?? '',
                $data['status'] ?? 'active'
            ]);
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function update($id, $data) {
        $sql = "UPDATE clients SET 
                name = ?, phone = ?, address = ?, category_id = ?, category_custom = ?, status = ? 
                WHERE id = ?";
        
        try {
            $this->db->query($sql, [
                $data['name'],
                $data['phone'],
                $data['address'],
                $data['category_id'],
                $data['category_custom'],
                $data['status'],
                $id
            ]);
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function delete($id) {
        // التحقق من وجود معاملات مالية
        $transCheck = $this->db->fetch("SELECT count(*) as c FROM transactions WHERE client_id = ?", [$id]);
        if ($transCheck['c'] > 0) {
            // لا يمكن الحذف، نقوم بتحويل الحالة إلى suspended أو نرجع خطأ
            // حسب المتطلبات: "Edit/Delete actions recorded in audit log" ولكن لم يذكر منع حذف العميل صراحة،
            // لكن منطقياً لا يجوز حذف عميل له معاملات. سنقوم فقط بتغيير حالته.
            // أو نمنع الحذف ونطلب من المستخدم إيقافه.
            return false; 
        }
        
        $this->db->query("DELETE FROM clients WHERE id = ?", [$id]);
        return true;
    }

    // دالة لحساب رصيد العميل
    public function getBalance($client_id) {
        // الوارد (Income) يقلل المديونية (أو يزيد رصيد العملاء if we view it as funds)
        // عادة في أنظمة العملاء:
        // منصرف (Expense) = أعطيناه مال أو بضاعة = عليه فلوس (دين)
        // سلفة (Advance) = منصرف أيضاً = عليه فلوس
        // وارد (Income) = دفع لنا = سداد
        
        // الرصيد = (المنصرف + السلف) - الوارد
        // موجب = عليه فلوس
        // سالب = له فلوس (رصيد دائن)
        
        $income = $this->db->fetch("SELECT SUM(amount) as t FROM transactions WHERE client_id = ? AND type = 'income'", [$client_id]);
        $expense = $this->db->fetch("SELECT SUM(amount) as t FROM transactions WHERE client_id = ? AND type = 'expense'", [$client_id]);
        
        $total_income = $income['t'] ?? 0;
        $total_expense = $expense['t'] ?? 0; // يشمل السلف لأن السلف نوع من ال transactions بنوع expense و flag is_advance

        return $total_expense - $total_income;
    }
    public function searchByName($term) {
        $term = "%$term%";
        return $this->db->fetchAll("SELECT c.*, cat.name as category_name 
                                    FROM clients c 
                                    LEFT JOIN categories cat ON c.category_id = cat.id 
                                    WHERE c.name LIKE ? LIMIT 20", [$term]);
    }

    public function getAllNames() {
         return $this->db->fetchAll("SELECT id, name FROM clients");
    }
}
