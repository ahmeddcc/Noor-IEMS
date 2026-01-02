<?php
// app/Controllers/TransactionController.php

namespace App\Controllers;

use App\Core\Session;
use App\Core\Database;
use App\Models\Transaction;
use App\Models\Client;
use App\Models\Category;

class TransactionController {
    private $db;
    
    public function __construct() {
        Session::check();
        if (!Session::hasPermission('transactions.view')) {
            Session::setFlash('error', 'ليس لديك صلاحية للوصول للمعاملات');
            redirect('index.php?page=dashboard');
        }
        $this->db = Database::getInstance();
    }
    
    public function index() {
        $transModel = new Transaction();
        $clientModel = new Client();
        
        // الفلاتر
        $filters = [
            'search' => $_GET['search'] ?? '',
            'type' => $_GET['type'] ?? '',
            'client_id' => $_GET['client_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];
        
        // Pagination
        $perPage = 25;
        $currentPage = max(1, intval($_GET['p'] ?? 1));
        $offset = ($currentPage - 1) * $perPage;
        
        // جلب العدد الإجمالي للمعاملات
        $totalCount = $transModel->getFilteredCount($filters);
        $totalPages = ceil($totalCount / $perPage);
        
        // جلب المعاملات مع الفلاتر والصفحات
        $transactions = $transModel->getFiltered($filters, $perPage, $offset);
        
        // === Smart Stats Caching (Phase 9) ===
        // تخزين مؤقت للإحصائيات فقط عندما لا توجد فلاتر
        $hasFilters = !empty($filters['search']) || !empty($filters['type']) || 
                      !empty($filters['client_id']) || !empty($filters['date_from']) || 
                      !empty($filters['date_to']);
        
        if ($hasFilters) {
            // مع الفلاتر: حساب مباشر
            $stats = $transModel->getFilteredStats($filters);
        } else {
            // بدون فلاتر: استخدام الذاكرة المؤقتة (5 دقائق)
            $cacheKey = 'trans_stats_cache';
            $cacheTime = 'trans_stats_time';
            $cacheExpiry = 300; // 5 دقائق
            
            if (Session::get($cacheKey) !== null && Session::get($cacheTime) !== null && 
                (time() - Session::get($cacheTime)) < $cacheExpiry) {
                $stats = Session::get($cacheKey);
            } else {
                $stats = $transModel->getFilteredStats($filters);
                Session::set($cacheKey, $stats);
                Session::set($cacheTime, time());
            }
        }
        
        // جلب العملاء للفلتر
        $clients = $clientModel->getAll();
        
        // جلب التصنيفات (للنافذة المنبثقة عند إضافة عميل جديد)
        $categoryModel = new Category();
        $categories = $categoryModel->getAllActive();
        
        // بيانات الـ Pagination
        $pagination = [
            'current' => $currentPage,
            'total' => $totalPages,
            'perPage' => $perPage,
            'totalRecords' => $totalCount
        ];
        
        $pageTitle = 'سجل المعاملات';
        $page = 'transactions';
        
        require_once ROOT_PATH . '/app/Views/layouts/header.php';
        require_once ROOT_PATH . '/app/Views/transactions/index.php';
        require_once ROOT_PATH . '/app/Views/layouts/footer.php';
    }
    
    // حساب الإحصائيات
    private function calculateStats($transactions) {
        $stats = [
            'total_income' => 0,
            'total_expense' => 0,
            'total_advances' => 0,
            'net' => 0,
            'count' => count($transactions)
        ];
        
        foreach ($transactions as $t) {
            if ($t['type'] == 'income') {
                $stats['total_income'] += $t['amount'];
            } else {
                $stats['total_expense'] += $t['amount'];
                if ($t['is_advance']) {
                    $stats['total_advances'] += $t['amount'];
                }
            }
        }
        
        $stats['net'] = $stats['total_income'] - $stats['total_expense'];
        
        return $stats;
    }

    public function add() {
        if (!Session::hasPermission('transactions.create')) {
            Session::setFlash('error', 'ليس لديك صلاحية لإضافة معاملات');
            redirect('index.php?page=transactions');
        }

        $type = $_REQUEST['type'] ?? 'income'; // Check both POST and GET
        $clientModel = new Client();
        $clients = $clientModel->getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // التحقق من CSRF
            if (!Session::verifyCsrf()) {
                redirect("index.php?page=transactions&action=add&type=$type");
            }

            $data = [
                'client_id' => $_POST['client_id'],
                'type' => ($type == 'advance') ? 'expense' : $type,
                'amount' => $_POST['amount'],
                'is_advance' => ($type == 'advance') ? 1 : 0,
                'date' => $_POST['date'],
                'payment_method' => $_POST['payment_method'] ?? 'cash',
                'notes' => $_POST['notes'],
                'user_id' => Session::get('user_id')
            ];

            if (empty($data['amount']) || $data['amount'] <= 0) {
                Session::setFlash('error', 'الرجاء إدخال مبلغ صحيح');
                redirect("index.php?page=transactions&action=add&type=$type");
            }

            $transModel = new Transaction();
            if ($transModel->add($data)) {
                Session::setFlash('success', 'تم تسجيل العملية بنجاح');
                
                // إذا كان الطلب عبر AJAX
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'تم تسجيل العملية بنجاح']);
                    exit;
                }
                
                redirect('index.php?page=transactions');
            } else {
                Session::setFlash('error', 'حدث خطأ أثناء التسجيل');
            }
        }

        $pageTitle = ($type == 'income') ? 'تسجيل وارد' : (($type == 'advance') ? 'تسجيل سلفة' : 'تسجيل منصرف');
        $page = 'transactions';
        
        require_once ROOT_PATH . '/app/Views/layouts/header.php';
        require_once ROOT_PATH . '/app/Views/transactions/form.php';
        require_once ROOT_PATH . '/app/Views/layouts/footer.php';
    }
    
    // AJAX: حفظ معاملة جديدة (محدث للتعامل مع العملاء الجدد)
    public function ajaxAdd() {
        header('Content-Type: application/json; charset=utf-8');
        
        if (!Session::hasPermission('transactions.create')) {
            echo json_encode(['success' => false, 'message' => 'ليس لديك صلاحية لإضافة معاملات']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'طريقة غير مسموحة']);
            exit;
        }
        
        // التحقق من CSRF
        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'خطأ في التحقق الأمني']);
            exit;
        }
        
        $type = $_POST['type'] ?? 'income';
        
        // منطق التعامل مع العميل (موجود أو جديد)
        $client_id = $_POST['client_id'] ?? 0;
        $client_name = trim($_POST['client_name'] ?? '');

        if (empty($client_id) && !empty($client_name)) {
            $clientModel = new Client();
            
            // 1. تحقق سريع بالاسم المطابق تماماً
            $existing = $this->db->fetch("SELECT id FROM clients WHERE name = ?", [$client_name]);
            if ($existing) {
                $client_id = $existing['id'];
            } else {
                // 2. إنشاء عميل جديد
                // نستخدم التصنيف القادم من الفورم أو الافتراضي
                $newId = $clientModel->add([
                    'name' => $client_name,
                    'category_id' => $_POST['category_id'] ?? 1, 
                    'phone' => '',
                    'address' => '',
                    'category_custom' => '',
                    'status' => 'active'
                ]);
                
                if ($newId) {
                    $client_id = $newId;
                } else {
                    echo json_encode(['success' => false, 'message' => 'فشل إنشاء العميل الجديد في قاعدة البيانات']);
                    exit;
                }
            }
        }

        $data = [
            'client_id' => $client_id,
            'type' => ($type == 'advance') ? 'expense' : $type,
            'amount' => floatval($_POST['amount'] ?? 0),
            'is_advance' => ($type == 'advance') ? 1 : 0,
            'date' => $_POST['date'] ?? date('Y-m-d'),
            'payment_method' => $_POST['payment_method'] ?? 'cash',
            'notes' => $_POST['notes'] ?? '',
            'user_id' => Session::get('user_id'),
            'is_repayment' => 0,
            'original_advance_id' => null
        ];
        
        if ($data['amount'] <= 0) {
            echo json_encode(['success' => false, 'message' => 'الرجاء إدخال مبلغ صحيح']);
            exit;
        }
        
        if (empty($data['client_id'])) {
            echo json_encode(['success' => false, 'message' => 'الرجاء اختيار العميل أو إدخال اسم عميل جديد']);
            exit;
        }
        
        $transModel = new Transaction();
        
        // السداد التلقائي للسلف عند إدخال وارد
        if ($type == 'income') {
            $incomeAmount = $data['amount'];
            
            // محاولة السداد التلقائي للسلف
            $repayResult = $transModel->autoRepayAdvances($client_id, $incomeAmount, $data);
            
            // إذا تبقى مبلغ بعد السداد، نسجله كوارد عادي
            if ($repayResult['remaining_income'] > 0) {
                $data['amount'] = $repayResult['remaining_income'];
                $transModel->add($data);
            }
            
            // إبطال الذاكرة المؤقتة للإحصائيات
            Session::remove('trans_stats_cache');
            Session::remove('trans_stats_time');
            
            echo json_encode(['success' => true, 'message' => 'تم تسجيل العملية بنجاح']);
        } else {
            // معاملة عادية (expense أو advance)
            if ($transModel->add($data)) {
                // إبطال الذاكرة المؤقتة للإحصائيات
                Session::remove('trans_stats_cache');
                Session::remove('trans_stats_time');
                
                echo json_encode(['success' => true, 'message' => 'تم تسجيل العملية بنجاح']);
            } else {
                echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء التسجيل']);
            }
        }
        exit;
    }

    // AJAX: بحث ذكي عن العملاء
    public function ajaxClientSearch() {
        header('Content-Type: application/json; charset=utf-8');
        $q = trim($_GET['q'] ?? '');
        
        $clientModel = new Client();
        $results = $clientModel->searchByName($q);
        echo json_encode($results);
        exit;
    }

    /**
     * AJAX: جلب رصيد دين العميل (لآلية السداد)
     */
    public function ajaxGetClientDebt() {
        header('Content-Type: application/json; charset=utf-8');
        
        $client_id = intval($_GET['client_id'] ?? 0);
        
        if ($client_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'معرف العميل غير صالح']);
            exit;
        }
        
        $transModel = new Transaction();
        $debtInfo = $transModel->getClientDebt($client_id);
        
        // جلب اسم العميل
        $clientModel = new Client();
        $client = $clientModel->getById($client_id);
        
        echo json_encode([
            'success' => true,
            'client_name' => $client['name'] ?? '',
            'debt' => $debtInfo['debt'],
            'has_debt' => $debtInfo['has_debt'],
            'debt_type' => $debtInfo['debt_type'],
            'remaining_advances' => $debtInfo['remaining_advances'],
            'total_debt' => $debtInfo['total_debt'],
            'total_advances' => $debtInfo['total_advances'],
            'total_expense' => $debtInfo['total_expense'],
            'total_income' => $debtInfo['total_income']
        ]);
        exit;
    }

    // AJAX: التحقق من تشابه الأسماء (Duplicate Check)
    public function ajaxCheckClient() {
        header('Content-Type: application/json; charset=utf-8');
        $name = trim($_POST['name'] ?? '');
        
        if (empty($name)) {
            echo json_encode(['status' => 'empty']);
            exit;
        }

        $clientModel = new Client();
        
        // 1. Exact Match
        $exact = $this->db->fetch("SELECT * FROM clients WHERE name = ?", [$name]);
        if ($exact) {
            echo json_encode(['status' => 'exact', 'client' => $exact]);
            exit;
        }

        // 2. Fuzzy Search
        $allNames = $clientModel->getAllNames(); // Returns id, name
        $candidates = [];
        
        foreach ($allNames as $c) {
            // Levenshtein cost: Insert/Replace/Delete. For Arabic, it's byte-based.
            // Converting to common encoding might be safer but usually works for simple typo detection.
            $dist = levenshtein($name, $c['name']);
            
            // Threshold logic: 
            // If name length < 5, max dist 2.
            // If name length >= 5, max dist 4.
            $len = mb_strlen($name);
            $threshold = ($len < 5) ? 2 : 5; 

            if ($dist <= $threshold) {
                // Ensure it's not totally different (simulated percentage)
                $candidates[] = $c;
            }
        }
        
        // Sort by distance (best match first) ? No, Levenshtein doesn't give direction easily in loop without storing dist.
        // We just return candidates.
        
        if (!empty($candidates)) {
            // Limit to top 5
            $candidates = array_slice($candidates, 0, 5);
            echo json_encode(['status' => 'similar', 'candidates' => $candidates]);
        } else {
            echo json_encode(['status' => 'new']);
        }
        exit;
    }

    // AJAX: إدارة التصنيفات (للنافذة المنبثقة)
    public function ajaxGetCategories() {
        header('Content-Type: application/json; charset=utf-8');
        $catModel = new \App\Models\Category();
        $cats = $catModel->getAllActive();
        echo json_encode(['success' => true, 'data' => $cats]);
        exit;
    }

    public function ajaxSaveCategory() {
        header('Content-Type: application/json; charset=utf-8');
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'اسم التصنيف مطلوب']);
            exit;
        }

        $catModel = new \App\Models\Category();
        if ($id > 0) {
            $result = $catModel->update($id, $name, 1);
        } else {
            $result = $catModel->add($name);
        }

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'تم الحفظ بنجاح']);
        } else {
            echo json_encode(['success' => false, 'message' => 'حدث خطأ']);
        }
        exit;
    }

    public function ajaxDeleteCategory() {
        header('Content-Type: application/json; charset=utf-8');
        $id = intval($_POST['id'] ?? 0);
        $catModel = new \App\Models\Category();
        
        if ($catModel->delete($id)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'لا يمكن حذف هذا التصنيف (قد يكون مرتبطاً بعملاء)']);
        }
        exit;
    }
    
    // AJAX: تعديل معاملة
    public function ajaxEdit() {
        header('Content-Type: application/json; charset=utf-8');
        
        if (!Session::hasPermission('transactions.edit')) {
            echo json_encode(['success' => false, 'message' => 'ليس لديك صلاحية لتعديل المعاملات']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'طريقة غير مسموحة']);
            exit;
        }
        
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'معاملة غير صالحة']);
            exit;
        }
        
        $data = [
            'amount' => floatval($_POST['amount'] ?? 0),
            'date' => $_POST['date'] ?? date('Y-m-d'),
            'payment_method' => $_POST['payment_method'] ?? 'cash',
            'notes' => $_POST['notes'] ?? ''
        ];
        
        $sql = "UPDATE transactions SET amount = ?, date = ?, payment_method = ?, notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $this->db->query($sql, [$data['amount'], $data['date'], $data['payment_method'], $data['notes'], $id]);
        
        echo json_encode(['success' => true, 'message' => 'تم تحديث المعاملة بنجاح']);
        exit;
    }
    
    // AJAX: حذف معاملة
    public function ajaxDelete() {
        header('Content-Type: application/json; charset=utf-8');
        
        if (!Session::hasPermission('transactions.delete')) {
            echo json_encode(['success' => false, 'message' => 'ليس لديك صلاحية لحذف المعاملات']);
            exit;
        }

        $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'معاملة غير صالحة']);
            exit;
        }
        
        $sql = "DELETE FROM transactions WHERE id = ?";
        $this->db->query($sql, [$id]);
        
        // إبطال الذاكرة المؤقتة للإحصائيات
        Session::remove('trans_stats_cache');
        Session::remove('trans_stats_time');
        
        echo json_encode(['success' => true, 'message' => 'تم حذف المعاملة بنجاح']);
        exit;
    }
    
    // AJAX: جلب بيانات معاملة للتعديل
    public function ajaxGet() {
        header('Content-Type: application/json; charset=utf-8');
        
        $id = intval($_GET['id'] ?? 0);
        $transaction = $this->db->fetch("SELECT t.*, c.name as client_name, c.category_id, cat.name as category_name 
                                        FROM transactions t 
                                        LEFT JOIN clients c ON t.client_id = c.id 
                                        LEFT JOIN categories cat ON c.category_id = cat.id 
                                        WHERE t.id = ?", [$id]);
        
        if ($transaction) {
            echo json_encode(['success' => true, 'data' => $transaction]);
        } else {
            echo json_encode(['success' => false, 'message' => 'معاملة غير موجودة']);
        }
        exit;
    }

    // AJAX: فلترة المعاملات بدون تحميل الصفحة
    public function ajaxFilter() {
        header('Content-Type: application/json; charset=utf-8');
        
        $transModel = new Transaction();
        
        // الفلاتر
        $filters = [
            'search' => $_GET['search'] ?? '',
            'type' => $_GET['type'] ?? '',
            'client_id' => $_GET['client_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];
        
        // Pagination
        $perPage = 25;
        $currentPage = max(1, intval($_GET['p'] ?? 1));
        $offset = ($currentPage - 1) * $perPage;
        
        // جلب العدد الإجمالي للمعاملات
        $totalCount = $transModel->getFilteredCount($filters);
        $totalPages = ceil($totalCount / $perPage);
        
        // جلب المعاملات مع الفلاتر والصفحات
        $transactions = $transModel->getFiltered($filters, $perPage, $offset);
        
        // حساب الإحصائيات بـ SQL (أسرع 100x!)
        $stats = $transModel->getFilteredStats($filters);
        
        // بناء HTML للصفوف (استخدام Partial View)
        ob_start();
        require ROOT_PATH . '/app/Views/transactions/partials/rows.php';
        $rowsHtml = ob_get_clean();
        
        // بناء HTML للـ Pagination (استخدام Partial View)
        ob_start();
        require ROOT_PATH . '/app/Views/transactions/partials/pagination.php';
        $paginationHtml = ob_get_clean();

        
        echo json_encode([
            'success' => true,
            'html' => $rowsHtml,
            'paginationHtml' => $paginationHtml,
            'stats' => $stats,
            'pagination' => [
                'current' => $currentPage,
                'total' => $totalPages,
                'totalRecords' => $totalCount
            ]
        ]);
        exit;
    }

    public function ledger() {
        // التحقق من صحة المدخلات (Security)
        $client_id = filter_input(INPUT_GET, 'client_id', FILTER_VALIDATE_INT);
        
        if (!$client_id || $client_id <= 0) {
            Session::setFlash('error', 'معرف العميل غير صالح');
            redirect('index.php?page=clients');
        }
        
        $clientModel = new Client();
        $client = $clientModel->getById($client_id);
        
        if (!$client) {
            Session::setFlash('error', 'العميل غير موجود');
            redirect('index.php?page=clients');
        }

        $transModel = new Transaction();
        
        // جلب المعاملات (مرتبة تصاعدياً لحساب الرصيد التراكمي)
        $transactions = $transModel->getByClient($client_id);
        
        // حساب الإحصائيات بـ SQL (أسرع 100x)
        $summary = $transModel->getLedgerSummary($client_id);
        
        // جلب اسم النظام من الإعدادات
        $companyName = \App\Models\Setting::get('company_name', 'نظام Noor للإدارة المالية');

        // حساب الرصيد التراكمي لكل صف
        $runningBalance = 0;
        foreach ($transactions as &$t) {
            if ($t['type'] == 'expense') {
                $runningBalance += $t['amount']; // مدين (عليه)
            } else {
                $runningBalance -= $t['amount']; // دائن (له)
            }
            $t['running_balance'] = $runningBalance;
        }
        unset($t); // فك المرجع

        $pageTitle = 'كشف حساب: ' . $client['name'];
        $page = 'transactions';
        
        require_once ROOT_PATH . '/app/Views/layouts/header.php';
        require_once ROOT_PATH . '/app/Views/transactions/ledger.php';
        require_once ROOT_PATH . '/app/Views/layouts/footer.php';
    }

    // AJAX: حذف جماعي
    public function bulkDelete() {
        header('Content-Type: application/json; charset=utf-8');
        
        // 1. Check Security Setting
        $isEnabled = \App\Models\Setting::get('enable_bulk_delete', '0');
        if ($isEnabled !== '1') {
            echo json_encode(['success' => false, 'message' => 'الحذف الجماعي معطل من الإعدادات']);
            exit;
        }

        // 2. Check Permissions (Delete permission AND (Admin OR Manager))
        if (!Session::hasPermission('transactions.delete') || !Session::isManager()) {
            echo json_encode(['success' => false, 'message' => 'ليس لديك صلاحية الحذف الجماعي']);
            exit;
        }

        // 3. Validate Input
        $input = json_decode(file_get_contents('php://input'), true);
        $ids = $input['ids'] ?? [];
        
        if (empty($ids) || !is_array($ids)) {
            echo json_encode(['success' => false, 'message' => 'لم يتم تحديد أي عناصر']);
            exit;
        }

        // 4. Execute Delete
        // filter integers
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids, function($id) { return $id > 0; });
        
        if (empty($ids)) {
            echo json_encode(['success' => false, 'message' => 'لا توجد عناصر صالحة للحذف']);
            exit;
        }

        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $sql = "DELETE FROM transactions WHERE id IN ($placeholders)";
        $this->db->query($sql, array_values($ids));
        
        echo json_encode(['success' => true, 'message' => 'تم حذف ' . count($ids) . ' عنصر بنجاح']);
        exit;
    }
}
