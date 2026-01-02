<?php
// app/Controllers/ReportController.php

namespace App\Controllers;

use App\Core\Session;
use App\Models\Transaction;
use App\Models\Client;

class ReportController {
    
    public function __construct() {
        Session::check();
        if (!Session::hasPermission('reports.view')) {
            Session::setFlash('error', 'ليس لديك صلاحية للوصول للتقارير');
            redirect('index.php?page=dashboard');
        }
    }
    
    public function index() {
        $pageTitle = 'التقارير';
        $page = 'reports';
        require_once ROOT_PATH . '/app/Views/layouts/header.php';
        require_once ROOT_PATH . '/app/Views/reports/index.php';
        require_once ROOT_PATH . '/app/Views/layouts/footer.php';
    }

    public function daily() {
        $date = $_GET['date'] ?? date('Y-m-d');
        $transModel = new Transaction();
        
        $stats = $transModel->getDailyStats($date);
        $transactions = $transModel->getByDateRange($date, $date);
        $stats['net_balance'] = $stats['income'] - ($stats['expense'] + $stats['advances']);

        $pageTitle = 'تقرير يومي: ' . $date;
        $page = 'reports';
        require_once ROOT_PATH . '/app/Views/layouts/header.php';
        require_once ROOT_PATH . '/app/Views/reports/daily.php';
        require_once ROOT_PATH . '/app/Views/layouts/footer.php';
    }

    public function range() {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');
        $page = isset($_GET['p']) && is_numeric($_GET['p']) ? (int)$_GET['p'] : 1;
        $limit = 50; // عدد المعاملات في الصفحة
        $offset = ($page - 1) * $limit;

        $transModel = new Transaction();
        
        $filters = [
            'date_from' => $from,
            'date_to' => $to
        ];

        // استخدام الدوال المحسنة
        $stats = $transModel->getFilteredStats($filters);
        $totalRecords = $transModel->getFilteredCount($filters);
        $transactions = $transModel->getFiltered($filters, $limit, $offset);
        
        // تنسيق البيانات للعرض (Mapping)
        $summary = [
            'income' => $stats['total_income'],
            'expense' => $stats['total_expense'],
            'advances' => $stats['total_advances'],
            'net_balance' => $stats['net']
        ];
        
        // حساب إجمالي الصفحات
        $totalPages = ceil($totalRecords / $limit);
        $currentPage = $page;

        $pageTitle = "تقرير الفترة من $from إلى $to";
        $page = 'reports';
        require_once ROOT_PATH . '/app/Views/layouts/header.php';
        require_once ROOT_PATH . '/app/Views/reports/range.php';
        require_once ROOT_PATH . '/app/Views/layouts/footer.php';
    }

    public function advances() {
        $transModel = new Transaction();
        $advances = $transModel->getOpenAdvances();
        
        // حساب الإجمالي
        $totalAdvances = 0;
        foreach ($advances as $adv) {
            $totalAdvances += $adv['remaining_debt'];
        }
        
        $pageTitle = 'تقرير السلف المفتوحة';
        $page = 'reports';
        require_once ROOT_PATH . '/app/Views/layouts/header.php';
        require_once ROOT_PATH . '/app/Views/reports/advances.php';
        require_once ROOT_PATH . '/app/Views/layouts/footer.php';
    }
}
