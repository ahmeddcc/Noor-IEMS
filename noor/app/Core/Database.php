<?php
// app/Core/Database.php

namespace App\Core;

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            // التأكد من وجود مجلد قاعدة البيانات
            $dbDir = dirname(DB_PATH);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }

            // الاتصال بقاعدة البيانات
            $this->pdo = new \PDO("sqlite:" . DB_PATH);
            
            // إعدادات PDO
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            $this->pdo->exec("PRAGMA foreign_keys = ON;"); // تفعيل المفاتيح الأجنبية

        } catch (\PDOException $e) {
            die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
        }
    }

    // الحصول على النسخة الحالية (Singleton)
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // الحصول على كائن PDO
    public function getConnection() {
        return $this->pdo;
    }

    // دالة لتنفيذ استعلام عادي (بدون إرجاع بيانات)
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    // دالة لجلب صف واحد
    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    // دالة لجلب كل الصفوف
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    // دالة للحصول على آخر ID تم إدخاله
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
}
