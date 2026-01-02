<?php
// app/Controllers/DashboardController.php

namespace App\Controllers;

use App\Core\Session;
use App\Core\Database;
use App\Models\Transaction;
use App\Models\Client;

class DashboardController {
    private $db;
    
    public function __construct() {
        Session::check();
        
        if (!Session::hasPermission('dashboard.view')) {
            Session::setFlash('error', 'ليس لديك صلاحية للوصول للوحة التحكم');
            redirect('index.php?page=login');
        }
        
        $this->db = Database::getInstance();
    }
    
    public function index() {
        $transactionModel = new Transaction();
        $clientModel = new Client();
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        
        // ===== إحصائيات اليوم =====
        $stats = $transactionModel->getDailyStats($today);
        $stats['net_balance'] = $stats['income'] - ($stats['expense'] + $stats['advances']);
        
        // ===== إحصائيات الأمس (للمقارنة) =====
        $yesterdayStats = $transactionModel->getDailyStats($yesterday);
        $yesterdayStats['net_balance'] = $yesterdayStats['income'] - ($yesterdayStats['expense'] + $yesterdayStats['advances']);
        
        // حساب نسب التغيير
        $stats['income_change'] = $this->calculateChangePercent($stats['income'], $yesterdayStats['income']);
        $stats['expense_change'] = $this->calculateChangePercent($stats['expense'], $yesterdayStats['expense']);
        $stats['advances_change'] = $this->calculateChangePercent($stats['advances'], $yesterdayStats['advances']);
        $stats['balance_change'] = $this->calculateChangePercent($stats['net_balance'], $yesterdayStats['net_balance']);
        
        // ===== آخر 5 معاملات =====
        $latestTransactions = $transactionModel->getLatest(5);
        
        // ===== بيانات الرسم البياني (آخر 7 أيام) =====
        $chartData = $this->getWeeklyChartData();
        
        // ===== التنبيهات الذكية =====
        $alerts = $this->getSmartAlerts();
        
        // ===== إحصائيات سريعة =====
        // استخدام دالة الموديل المركزية لضمان تطابق الأرقام مع التقارير
        // التعديل: حساب عدد العملاء الذين عليهم ديون (وليس عدد المعاملات)
        $openAdvancesData = $transactionModel->getOpenAdvances();
        $openAdvancesCount = count($openAdvancesData);

        $quickStats = [
            'total_clients' => $this->getTotalActiveClients(),
            'today_transactions' => $this->getTodayTransactionsCount(),
            'open_advances_count' => $openAdvancesCount
        ];

        $pageTitle = 'لوحة التحكم';
        $page = 'dashboard';
        
        require_once ROOT_PATH . '/app/Views/layouts/header.php';
        require_once ROOT_PATH . '/app/Views/dashboard.php';
        require_once ROOT_PATH . '/app/Views/layouts/footer.php';
    }
    
    // حساب نسبة التغيير
    private function calculateChangePercent($current, $previous) {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }
    
    // بيانات الرسم البياني الأسبوعي
    private function getWeeklyChartData() {
        $data = [
            'labels' => [],
            'income' => [],
            'expense' => [],
            'balance' => []
        ];
        
        $transactionModel = new Transaction();
        
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dayStats = $transactionModel->getDailyStats($date);
            
            $data['labels'][] = date('d/m', strtotime($date));
            $data['income'][] = $dayStats['income'];
            $data['expense'][] = $dayStats['expense'] + $dayStats['advances'];
            $data['balance'][] = $dayStats['income'] - ($dayStats['expense'] + $dayStats['advances']);
        }
        
        return $data;
    }
    
    // التنبيهات الذكية
    private function getSmartAlerts() {
        $alerts = [];
        
        // 1. السلف المفتوحة
        $openAdvances = $this->getOpenAdvances();
        if (count($openAdvances) > 0) {
            $totalAdvances = array_sum(array_column($openAdvances, 'remaining'));
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'fa-exclamation-triangle',
                'message' => 'يوجد ' . count($openAdvances) . ' سلفة مفتوحة بقيمة إجمالية ' . number_format($totalAdvances, 2) . ' ج.م',
                'link' => 'index.php?page=reports&action=advances'
            ];
        }
        
        // 2. العملاء المدينون
        $debtorClients = $this->getDebtorClients();
        if (count($debtorClients) > 0) {
            $totalDebt = array_sum(array_column($debtorClients, 'balance'));
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'fa-users',
                'message' => count($debtorClients) . ' عميل لديهم أرصدة مدينة بقيمة ' . number_format(abs($totalDebt), 2) . ' ج.م',
                'link' => 'index.php?page=clients'
            ];
        }
        
        return $alerts;
    }
    
    // السلف المفتوحة
    private function getOpenAdvances() {
        $sql = "SELECT c.id, c.name, 
                SUM(CASE WHEN t.is_advance = 1 THEN t.amount ELSE 0 END) as total_given,
                SUM(CASE WHEN t.type = 'income' THEN t.amount ELSE 0 END) as total_paid
                FROM clients c
                JOIN transactions t ON c.id = t.client_id
                GROUP BY c.id, c.name
                HAVING total_given > total_paid";
        
        $results = $this->db->fetchAll($sql);
        
        foreach ($results as &$row) {
            $row['remaining'] = $row['total_given'] - $row['total_paid'];
        }
        
        return $results;
    }
    
    // العملاء المدينون
    private function getDebtorClients() {
        $sql = "SELECT c.id, c.name,
                (SUM(CASE WHEN t.type = 'expense' THEN t.amount ELSE 0 END) - 
                 SUM(CASE WHEN t.type = 'income' THEN t.amount ELSE 0 END)) as balance
                FROM clients c
                LEFT JOIN transactions t ON c.id = t.client_id
                GROUP BY c.id
                HAVING balance > 0";
        
        return $this->db->fetchAll($sql);
    }
    
    // عدد العملاء النشطين
    private function getTotalActiveClients() {
        $result = $this->db->fetch("SELECT COUNT(*) as count FROM clients WHERE status = 'active'");
        return $result['count'] ?? 0;
    }
    
    // عدد معاملات اليوم
    private function getTodayTransactionsCount() {
        $result = $this->db->fetch("SELECT COUNT(*) as count FROM transactions WHERE date = ?", [date('Y-m-d')]);
        return $result['count'] ?? 0;
    }
}
