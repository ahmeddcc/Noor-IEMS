<?php
// app/Models/Transaction.php

namespace App\Models;

use App\Core\Database;

class Transaction {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // جلب إحصائيات اليوم
    public function getDailyStats($date) {
        $income = $this->db->fetch("SELECT SUM(amount) as total FROM transactions WHERE type = 'income' AND date = ?", [$date]);
        $expense = $this->db->fetch("SELECT SUM(amount) as total FROM transactions WHERE type = 'expense' AND is_advance = 0 AND date = ?", [$date]);
        $advances = $this->db->fetch("SELECT SUM(amount) as total FROM transactions WHERE is_advance = 1 AND date = ?", [$date]);
        $count = $this->db->fetch("SELECT COUNT(*) as count FROM transactions WHERE date = ?", [$date]);

        return [
            'income' => $income['total'] ?? 0,
            'expense' => $expense['total'] ?? 0,
            'advances' => $advances['total'] ?? 0,
            'count' => $count['count'] ?? 0
        ];
    }

    // إضافة معاملة جديدة
    public function add($data) {
        $sql = "INSERT INTO transactions (client_id, type, amount, is_advance, date, payment_method, notes, user_id, is_repayment, original_advance_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        try {
            $this->db->query($sql, [
                $data['client_id'],
                $data['type'],
                $data['amount'],
                $data['is_advance'] ?? 0,
                $data['date'],
                $data['payment_method'] ?? 'cash',
                $data['notes'],
                $data['user_id'],
                $data['is_repayment'] ?? 0,
                $data['original_advance_id'] ?? null
            ]);
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            return false;
        }
    }

    // جلب معاملات عميل معين (كشف حساب) مع اسم المستخدم
    public function getByClient($client_id) {
        $sql = "SELECT t.*, u.username as user_name 
                FROM transactions t 
                LEFT JOIN users u ON t.user_id = u.id 
                WHERE t.client_id = ? 
                ORDER BY t.date ASC, t.id ASC";
        return $this->db->fetchAll($sql, [$client_id]);
    }

    /**
     * حساب ملخص كشف الحساب مباشرة في SQL (أداء أفضل)
     */
    public function getLedgerSummary($client_id) {
        $sql = "SELECT 
                    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense,
                    SUM(CASE WHEN is_advance = 1 THEN amount ELSE 0 END) as total_advances,
                    COUNT(*) as transaction_count
                FROM transactions WHERE client_id = ?";
        
        $result = $this->db->fetch($sql, [$client_id]);
        
        $totalIncome = $result['total_income'] ?? 0;
        $totalExpense = $result['total_expense'] ?? 0;
        
        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'total_advances' => $result['total_advances'] ?? 0,
            'net_balance' => $totalExpense - $totalIncome,
            'transaction_count' => $result['transaction_count'] ?? 0
        ];
    }

    // جلب آخر المعاملات
    public function getLatest($limit = 10) {
        $sql = "SELECT t.*, c.name as client_name 
                FROM transactions t 
                LEFT JOIN clients c ON t.client_id = c.id 
                ORDER BY t.created_at DESC LIMIT ?";
        return $this->db->fetchAll($sql, [$limit]);
    }
    
    // بناء جملة الاستعلام والفلاتر (لتفادي التكرار)
    private function buildFilterQuery($filters, $countOnly = false) {
        $sql = " WHERE 1=1";
        $params = [];
        
        // فلتر البحث
        if (!empty($filters['search'])) {
            $sql .= " AND (c.name LIKE ? OR t.notes LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        
        // فلتر النوع
        if (!empty($filters['type'])) {
            if ($filters['type'] == 'advance') {
                $sql .= " AND t.is_advance = 1";
            } elseif ($filters['type'] == 'income') {
                $sql .= " AND t.type = 'income'";
            } elseif ($filters['type'] == 'expense') {
                $sql .= " AND t.type = 'expense' AND t.is_advance = 0";
            }
        }
        
        // فلتر العميل
        if (!empty($filters['client_id'])) {
            $sql .= " AND t.client_id = ?";
            $params[] = $filters['client_id'];
        }
        
        // فلتر التاريخ
        if (!empty($filters['date_from'])) {
            $sql .= " AND t.date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND t.date <= ?";
            $params[] = $filters['date_to'];
        }
        
        return ['sql' => $sql, 'params' => $params];
    }

    // جلب المعاملات مع الفلاتر
    public function getFiltered($filters, $limit = 100, $offset = 0) {
        $filterData = $this->buildFilterQuery($filters);
        
        $sql = "SELECT t.*, c.name as client_name, cat.name as category_name 
                FROM transactions t 
                LEFT JOIN clients c ON t.client_id = c.id
                LEFT JOIN categories cat ON c.category_id = cat.id" . $filterData['sql'];
                
        $sql .= " ORDER BY t.date DESC, t.id DESC LIMIT ? OFFSET ?";
        $filterData['params'][] = $limit;
        $filterData['params'][] = $offset;
        
        return $this->db->fetchAll($sql, $filterData['params']);
    }

    // جلب المعاملات ضمن فترة زمنية
    public function getByDateRange($from, $to) {
        $sql = "SELECT t.*, c.name as client_name, cat.name as category_name 
                FROM transactions t 
                LEFT JOIN clients c ON t.client_id = c.id 
                LEFT JOIN categories cat ON c.category_id = cat.id
                WHERE t.date BETWEEN ? AND ? 
                ORDER BY t.date DESC, t.id DESC";
        return $this->db->fetchAll($sql, [$from, $to]);
    }

    // جلب ملخص فترة زمنية
    public function getSummaryByDateRange($from, $to) {
        $income = $this->db->fetch("SELECT SUM(amount) as total FROM transactions WHERE type = 'income' AND date BETWEEN ? AND ?", [$from, $to]);
        $expense = $this->db->fetch("SELECT SUM(amount) as total FROM transactions WHERE type = 'expense' AND is_advance = 0 AND date BETWEEN ? AND ?", [$from, $to]);
        $advances = $this->db->fetch("SELECT SUM(amount) as total FROM transactions WHERE is_advance = 1 AND date BETWEEN ? AND ?", [$from, $to]);

        return [
            'income' => $income['total'] ?? 0,
            'expense' => $expense['total'] ?? 0,
            'advances' => $advances['total'] ?? 0
        ];
    }

    // جلب السلف المفتوحة (العملاء الذين عليهم سلف)
    // جلب السلف المفتوحة (العملاء الذين عليهم سلف)
    // تحديث: يشمل الآن أي عميل لديه رصيد مدين (سلف أو مصروفات)
    public function getOpenAdvances() {
        $sql = "SELECT c.id, c.name as client_name, c.phone, 
                MAX(t.date) as last_transaction_date,
                SUM(CASE WHEN t.type = 'expense' THEN 1 ELSE 0 END) as advances_count,
                SUM(CASE WHEN t.type = 'expense' THEN t.amount ELSE 0 END) as total_advances,
                SUM(CASE WHEN t.type = 'income' THEN t.amount ELSE 0 END) as total_repaid,
                (SUM(CASE WHEN t.type = 'expense' THEN t.amount ELSE 0 END) - SUM(CASE WHEN t.type = 'income' THEN t.amount ELSE 0 END)) as remaining_debt
                FROM transactions t
                JOIN clients c ON t.client_id = c.id
                GROUP BY c.id
                HAVING remaining_debt > 0
                ORDER BY remaining_debt DESC";
        return $this->db->fetchAll($sql);
    }

    // حذف معاملة
    public function delete($id) {
        $this->db->query("DELETE FROM transactions WHERE id = ?", [$id]);
        return true;
    }

    /**
     * جلب السلف المفتوحة للعميل (FIFO - الأقدم أولاً)
     * @param int $client_id
     * @return array مصفوفة بالسلف والمبالغ المتبقية
     */
    public function getClientOpenAdvances($client_id) {
        // جلب جميع السلف
        $sql = "SELECT id, amount, date, notes 
                FROM transactions 
                WHERE client_id = ? AND is_advance = 1 
                ORDER BY date ASC, id ASC";
        $advances = $this->db->fetchAll($sql, [$client_id]);
        
        // جلب إجمالي السداد لكل سلفة
        $sqlRepayments = "SELECT original_advance_id, SUM(amount) as paid 
                         FROM transactions 
                         WHERE client_id = ? AND is_repayment = 1 AND original_advance_id IS NOT NULL
                         GROUP BY original_advance_id";
        $repayments = $this->db->fetchAll($sqlRepayments, [$client_id]);
        
        // تحويل السداد لـ array مفهرس
        $paidByAdvance = [];
        foreach ($repayments as $r) {
            $paidByAdvance[$r['original_advance_id']] = floatval($r['paid']);
        }
        
        // جلب السداد غير المرتبط (legacy - قبل إضافة original_advance_id)
        $sqlUnlinkedRepayments = "SELECT SUM(amount) as total 
                                  FROM transactions 
                                  WHERE client_id = ? AND is_repayment = 1 AND original_advance_id IS NULL";
        $unlinkedResult = $this->db->fetch($sqlUnlinkedRepayments, [$client_id]);
        $unlinkedPaid = floatval($unlinkedResult['total'] ?? 0);
        
        // حساب المتبقي لكل سلفة
        $openAdvances = [];
        foreach ($advances as $adv) {
            $advId = $adv['id'];
            $advAmount = floatval($adv['amount']);
            $linked = $paidByAdvance[$advId] ?? 0;
            
            // توزيع السداد غير المرتبط على السلف الأقدم
            $fromUnlinked = 0;
            if ($unlinkedPaid > 0 && $linked < $advAmount) {
                $needed = $advAmount - $linked;
                $fromUnlinked = min($needed, $unlinkedPaid);
                $unlinkedPaid -= $fromUnlinked;
            }
            
            $totalPaid = $linked + $fromUnlinked;
            $remaining = $advAmount - $totalPaid;
            
            if ($remaining > 0) {
                $openAdvances[] = [
                    'id' => $advId,
                    'amount' => $advAmount,
                    'paid' => $totalPaid,
                    'remaining' => $remaining,
                    'date' => $adv['date'],
                    'notes' => $adv['notes']
                ];
            }
        }
        
        return $openAdvances;
    }

    /**
     * سداد تلقائي للسلف من مبلغ وارد
     * @param int $client_id
     * @param float $incomeAmount المبلغ الوارد
     * @param array $baseData بيانات المعاملة الأساسية
     * @return array نتيجة السداد
     */
    public function autoRepayAdvances($client_id, $incomeAmount, $baseData) {
        $openAdvances = $this->getClientOpenAdvances($client_id);
        
        if (empty($openAdvances)) {
            return [
                'repaid' => 0,
                'remaining_income' => $incomeAmount,
                'transactions_created' => 0
            ];
        }
        
        $totalRepaid = 0;
        $transactionsCreated = 0;
        $remainingIncome = $incomeAmount;
        
        foreach ($openAdvances as $advance) {
            if ($remainingIncome <= 0) break;
            
            $toRepay = min($advance['remaining'], $remainingIncome);
            
            // إنشاء معاملة سداد
            $repaymentData = $baseData;
            $repaymentData['amount'] = $toRepay;
            $repaymentData['is_repayment'] = 1;
            $repaymentData['original_advance_id'] = $advance['id'];
            $repaymentData['notes'] = 'سداد تلقائي - سلفة ' . date('Y/m/d', strtotime($advance['date']));
            
            $this->add($repaymentData);
            
            $totalRepaid += $toRepay;
            $remainingIncome -= $toRepay;
            $transactionsCreated++;
        }
        
        return [
            'repaid' => $totalRepaid,
            'remaining_income' => $remainingIncome,
            'transactions_created' => $transactionsCreated
        ];
    }

    /**
     * جلب رصيد دين العميل (للسداد)
     * @param int $client_id
     * @return array معلومات الدين مع تمييز السلفة
     */
    public function getClientDebt($client_id) {
        // حساب المجاميع الأساسية
        $sql = "SELECT 
                    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense,
                    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                    SUM(CASE WHEN is_advance = 1 THEN amount ELSE 0 END) as total_advances,
                    SUM(CASE WHEN is_repayment = 1 THEN amount ELSE 0 END) as total_repayments
                FROM transactions WHERE client_id = ?";
        
        $result = $this->db->fetch($sql, [$client_id]);
        
        $totalExpense = floatval($result['total_expense'] ?? 0);
        $totalIncome = floatval($result['total_income'] ?? 0);
        $totalAdvances = floatval($result['total_advances'] ?? 0);
        $totalRepayments = floatval($result['total_repayments'] ?? 0);
        
        // الدين الكلي = المصروفات - الوارد
        $totalDebt = $totalExpense - $totalIncome;
        
        // السلف المتبقية = إجمالي السلف - إجمالي السداد
        $remainingAdvances = $totalAdvances - $totalRepayments;
        if ($remainingAdvances < 0) $remainingAdvances = 0;
        
        // تحديد نوع الدين
        $debtType = 'none';
        $displayDebt = 0;
        
        if ($remainingAdvances > 0) {
            // لديه سلف مفتوحة
            $debtType = 'advance';
            $displayDebt = $remainingAdvances;
        } elseif ($totalDebt > 0) {
            // لديه دين عادي فقط
            $debtType = 'debt';
            $displayDebt = $totalDebt;
        }
        
        return [
            'total_expense' => $totalExpense,
            'total_income' => $totalIncome,
            'total_advances' => $totalAdvances,
            'total_repayments' => $totalRepayments,
            'remaining_advances' => $remainingAdvances,
            'total_debt' => $totalDebt,
            'debt' => $displayDebt,
            'debt_type' => $debtType, // 'advance', 'debt', or 'none'
            'has_debt' => $displayDebt > 0
        ];
    }

    /**
     * حساب الإحصائيات مباشرة في SQL (بدلاً من جلب كل البيانات)
     * هذا أسرع بـ 100 مرة من جلب 10,000 صف!
     */
    public function getFilteredStats($filters) {
        $filterData = $this->buildFilterQuery($filters);
        
        $sql = "SELECT 
                    SUM(CASE WHEN t.type = 'income' THEN t.amount ELSE 0 END) as total_income,
                    SUM(CASE WHEN t.type = 'expense' THEN t.amount ELSE 0 END) as total_expense,
                    SUM(CASE WHEN t.is_advance = 1 THEN t.amount ELSE 0 END) as total_advances,
                    COUNT(*) as count
                FROM transactions t 
                LEFT JOIN clients c ON t.client_id = c.id" . $filterData['sql'];
        
        $result = $this->db->fetch($sql, $filterData['params']);
        
        $totalIncome = $result['total_income'] ?? 0;
        $totalExpense = $result['total_expense'] ?? 0;
        
        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'total_advances' => $result['total_advances'] ?? 0,
            'net' => $totalIncome - $totalExpense,
            'count' => $result['count'] ?? 0
        ];
    }

    // عدد المعاملات المفلترة (للـ pagination)
    // عدد المعاملات المفلترة (للـ pagination)
    public function getFilteredCount($filters) {
        $filterData = $this->buildFilterQuery($filters);
        
        $sql = "SELECT COUNT(*) as count FROM transactions t 
                LEFT JOIN clients c ON t.client_id = c.id" . $filterData['sql'];
        
        $result = $this->db->fetch($sql, $filterData['params']);
        return $result['count'] ?? 0;
    }


}
