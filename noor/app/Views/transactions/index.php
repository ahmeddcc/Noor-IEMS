<!-- صفحة سجل المعاملات - تصميم Neon Glass -->
<script>window.currentDate = "<?php echo date('Y-m-d'); ?>";</script>
<style>
/* =============================================
   Critical CSS - Above The Fold (Phase 5)
   يُحمَّل فوراً لتسريع العرض الأولي
   ============================================= */

/* Page Container - Instant display */
.transactions-page {
    opacity: 1 !important;
    animation: none !important;
    transform: none !important;
}

/* DISABLE ALL ENTRY ANIMATIONS - Critical */
.trans-header-wrapper,
.header-action-buttons-neon,
.stats-bar-neon,
.stat-card-neon,
.stat-icon,
.stat-icon i,
.filter-bar-neon,
.table-card-neon,
.neon-table,
.neon-hero-header, 
.action-btn,
i, .fa, .fas, .far, .fab,
button,
input,
select {
    animation: none !important;
    transform: none !important;
    transition: none !important;
    opacity: 1 !important;
}

/* Neon Hero Header - Critical */
.neon-hero-header {
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(6, 182, 212, 0.3);
    border-radius: 16px;
    padding: 20px 25px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.neon-title-group {
    display: flex;
    align-items: center;
    gap: 15px;
}

.neon-title-icon {
    font-size: 2rem;
    color: #06b6d4;
    filter: drop-shadow(0 0 10px #06b6d4);
}

.neon-title-text {
    color: #fff;
    font-size: 1.5rem;
    font-weight: 700;
}

.neon-title-text small {
    display: block;
    font-size: 0.85rem;
    color: rgba(255,255,255,0.6);
    font-weight: 400;
}

/* Stats Bar - Critical */
.stats-bar-neon {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.stat-card-neon {
    background: rgba(15, 23, 42, 0.5);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    padding: 15px 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-card-neon .stat-icon {
    width: 45px;
    height: 45px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.stat-card-neon.income .stat-icon { background: rgba(16, 185, 129, 0.15); color: #10b981; }
.stat-card-neon.expense .stat-icon { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
.stat-card-neon.balance .stat-icon { background: rgba(6, 182, 212, 0.15); color: #06b6d4; }

.stat-label { color: rgba(255,255,255,0.6); font-size: 0.85rem; }
.stat-value { color: #fff; font-size: 1.3rem; font-weight: 700; }

/* Action Buttons - Critical (prevent white flash) */
.header-action-btns .action-btn,
.header-action-buttons-neon .action-btn,
.neon-action-btn {
    background: rgba(15, 23, 42, 0.8);
    border: 1px solid rgba(255,255,255,0.2);
    color: #fff;
    padding: 10px 18px;
    border-radius: 10px;
}
.action-btn.income-btn { background: rgba(16, 185, 129, 0.2); border-color: #10b981; color: #10b981; }
.action-btn.expense-btn { background: rgba(239, 68, 68, 0.2); border-color: #ef4444; color: #ef4444; }
.action-btn.advance-btn { background: rgba(245, 158, 11, 0.2); border-color: #f59e0b; color: #f59e0b; }

/* Filter Bar - Critical (prevent white flash) */
.filter-bar-neon {
    background: rgba(15, 23, 42, 0.8);
    border: 1px solid rgba(0, 212, 255, 0.2);
    border-radius: 12px;
    padding: 15px 20px;
}

/* Filter Inputs - Critical */
.filter-input-neon,
.search-input-neon,
input[type="text"],
input[type="date"],
select {
    background: rgba(0, 0, 0, 0.3) !important;
    border: 1px solid rgba(255,255,255,0.2) !important;
    color: #fff !important;
    border-radius: 8px;
}

/* Table - Critical */
.neon-table, .table-card-neon {
    background: rgba(30, 41, 59, 0.6);
    border-radius: 16px;
}

/* Glass Modal Close Button Fix */
.glass-modal-close {
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    color: #fff !important;
    width: 34px !important;
    height: 34px !important;
    border-radius: 8px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    cursor: pointer !important;
    transition: all 0.3s !important;
    outline: none !important;
    padding: 0 !important;
}

.glass-modal-close:hover {
    background: rgba(244, 63, 94, 0.2) !important;
    border-color: #f43f5e !important;
    color: #f43f5e !important;
    transform: rotate(90deg) !important;
}
</style>
<div class="transactions-page">
    
    <!-- الهيدر بنفس تنسيق لوحة التحكم -->
    <!-- الهيدر الموحد (Unified Neon Header) -->
    <div class="neon-hero-header" style="border: 1px solid #3b82f6 !important;">
        <div class="neon-title-group">
            <i class="fas fa-exchange-alt neon-title-icon"></i>
            <div class="neon-title-text">
                سجل المعاملات
                <small>إدارة ومتابعة الحركات المالية</small>
            </div>
        </div>
        <div class="header-action-buttons-neon">
            <button type="button" class="btn-action-neon income" onclick="openModal('income')">
                <span>وارد</span>
                <i class="fas fa-plus-circle"></i>
            </button>
            <button type="button" class="btn-action-neon expense" onclick="openModal('expense')">
                <span>منصرف</span>
                <i class="fas fa-minus-circle"></i>
            </button>
            <button type="button" class="btn-action-neon advance" onclick="openModal('advance')">
                <span>سلفة</span>
                <i class="fas fa-hand-holding-usd"></i>
            </button>
        </div>
    </div>
    
    <!-- شريط الإحصائيات (منفصل) -->
    <div class="stats-bar-neon">
        <div class="stat-card-neon income">
            <div class="stat-icon"><i class="fas fa-arrow-down"></i></div>
            <div class="stat-content">
                <span class="stat-label">إجمالي الوارد</span>
                <span class="stat-value"><?php echo toArabicNum(number_format($stats['total_income'], 2)); ?> <small>ج.م</small></span>
            </div>
        </div>
        <div class="stat-card-neon expense">
            <div class="stat-icon"><i class="fas fa-arrow-up"></i></div>
            <div class="stat-content">
                <span class="stat-label">إجمالي المنصرف</span>
                <span class="stat-value"><?php echo toArabicNum(number_format($stats['total_expense'], 2)); ?> <small>ج.م</small></span>
            </div>
        </div>
        <div class="stat-card-neon advance">
            <div class="stat-icon"><i class="fas fa-hand-holding-usd"></i></div>
            <div class="stat-content">
                <span class="stat-label">إجمالي السلف</span>
                <span class="stat-value"><?php echo toArabicNum(number_format($stats['total_advances'], 2)); ?> <small>ج.م</small></span>
            </div>
        </div>
        <div class="stat-card-neon net <?php echo $stats['net'] >= 0 ? 'positive' : 'negative'; ?>">
            <div class="stat-icon"><i class="fas fa-wallet"></i></div>
            <div class="stat-content">
                <span class="stat-label">الصافي</span>
                <span class="stat-value"><?php echo toArabicNum(number_format($stats['net'], 2)); ?> <small>ج.م</small></span>
            </div>
        </div>
    </div>

    <!-- شريط البحث والفلترة - Neon Glass -->
    <div class="filter-bar-neon">
        <form method="GET" action="" class="filter-form-neon">
            <input type="hidden" name="page" value="transactions">
            
            <!-- مربع البحث الذكي -->
            <div class="filter-group-neon search-group">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="search" id="instantSearch" placeholder="بحث فوري..." 
                       value="<?php echo e($filters['search'] ?? ''); ?>" class="filter-input-neon search-input-neon">
            </div>
            
            <div class="filter-group-neon">
                <i class="fas fa-calendar"></i>
                <input type="date" name="date_from" value="<?php echo e($filters['date_from'] ?? ''); ?>" 
                       class="filter-input-neon" title="من تاريخ">
            </div>
            
            <div class="filter-group-neon">
                <i class="fas fa-calendar-check"></i>
                <input type="date" name="date_to" value="<?php echo e($filters['date_to'] ?? ''); ?>" 
                       class="filter-input-neon" title="إلى تاريخ">
            </div>
            
            <div class="filter-group-neon">
                <input type="hidden" name="type" id="filterTypeId" value="<?php echo e($filters['type'] ?? ''); ?>">
                <button type="button" class="filter-picker-btn" onclick="openFilterTypeModal()">
                    <i class="fas fa-tags"></i>
                    <span id="filterTypeName"><?php 
                        $typeNames = ['income' => 'وارد', 'expense' => 'منصرف', 'advance' => 'سلفة'];
                        echo isset($filters['type']) && $filters['type'] ? ($typeNames[$filters['type']] ?? 'كل الأنواع') : 'كل الأنواع';
                    ?></span>
                </button>
            </div>
            
            <div class="filter-group-neon">
                <input type="hidden" name="client_id" id="filterClientId" value="<?php echo e($filters['client_id'] ?? ''); ?>">
                <button type="button" class="filter-picker-btn" onclick="openFilterClientModal()">
                    <i class="fas fa-user"></i>
                    <span id="filterClientName"><?php 
                        $selectedClientName = 'كل العملاء';
                        if (!empty($filters['client_id'])) {
                            foreach ($clients as $c) {
                                if ($c['id'] == $filters['client_id']) {
                                    $selectedClientName = e($c['name']);
                                    break;
                                }
                            }
                        }
                        echo $selectedClientName;
                    ?></span>
                </button>
            </div>
            
            <button type="submit" class="filter-btn-neon">
                <i class="fas fa-filter"></i>
                <span>فلترة</span>
            </button>
            
            <button type="button" class="filter-btn-neon reset" onclick="resetFilters()" title="إعادة تعيين">
                <i class="fas fa-redo"></i>
            </button>
        </form>
    </div>

    <!-- جدول المعاملات - تصميم Neon Glass -->
    <div class="table-card-neon">
        <!-- شريط الأدوات المحسن -->
        <div class="table-toolbar-neon">
            <div class="toolbar-info">
                <span class="result-count">
                    <i class="fas fa-list-ol"></i>
                    <strong><?php echo toArabicNum($pagination['totalRecords'] ?? $stats['count']); ?></strong> معاملة
                </span>
                <?php if (isset($pagination) && $pagination['total'] > 1): ?>
                <span class="page-indicator">صفحة <?php echo toArabicNum($pagination['current']); ?>/<?php echo toArabicNum($pagination['total']); ?></span>
                <?php endif; ?>
            </div>
            <div class="toolbar-buttons">
                <?php if (\App\Models\Setting::get('enable_bulk_delete', '0') == '1' && \App\Core\Session::hasPermission('transactions.delete') && \App\Core\Session::isManager()): ?>
                <button type="button" class="neon-tool-btn danger" id="bulkDeleteBtn" onclick="confirmBulkDelete()" style="display:none; color: #ff4d4d !important; border-color: rgba(255, 77, 77, 0.3) !important;">
                    <i class="fas fa-trash-alt"></i>
                    <span>حذف (<span id="selectedCount">0</span>)</span>
                </button>
                <?php endif; ?>
                <button type="button" class="neon-tool-btn" onclick="exportToExcel()">
                    <i class="fas fa-file-excel"></i>
                    <span>تصدير</span>
                </button>
                <button type="button" class="neon-tool-btn" onclick="printTable()">
                    <i class="fas fa-print"></i>
                    <span>طباعة</span>
                </button>
            </div>
        </div>
        
        <!-- الجدول -->
        <div class="table-responsive">
            <table class="neon-table" id="transactionsTable">
                <thead>
                    <tr>
                        <?php if (\App\Models\Setting::get('enable_bulk_delete', '0') == '1' && \App\Core\Session::hasPermission('transactions.delete') && \App\Core\Session::isManager()): ?>
                        <th style="width: 40px;"><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>
                        <?php endif; ?>
                        <th><i class="fas fa-calendar-alt"></i> التاريخ</th>
                        <th><i class="fas fa-user"></i> العميل</th>
                        <th><i class="fas fa-tag"></i> النوع</th>
                        <th><i class="fas fa-coins"></i> المبلغ</th>
                        <th><i class="fas fa-credit-card"></i> الدفع</th>
                        <th><i class="fas fa-sticky-note"></i> ملاحظات</th>
                        <th><i class="fas fa-cog"></i> إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr id="emptyRow"><td colspan="100%" class="text-center" style="padding: 40px; color: rgba(255,255,255,0.3); font-size: 1.2rem;">لا توجد معاملات لهذا العميل</td></tr>
                    <?php else: ?>
<?php 

                        
                        foreach ($transactions as $t): ?>
                        <tr data-id="<?php echo $t['id']; ?>" class="table-row-hover">
                            <?php if (\App\Models\Setting::get('enable_bulk_delete', '0') == '1' && \App\Core\Session::hasPermission('transactions.delete') && \App\Core\Session::isManager()): ?>
                            <td class="checkbox-cell" onclick="event.stopPropagation()">
                                <input type="checkbox" class="row-checkbox" value="<?php echo $t['id']; ?>" onchange="updateBulkSelect()">
                            </td>
                            <?php endif; ?>
                            <td class="date-cell">
                                <span class="date-badge"><?php echo toArabicNum(date('d-m-Y', strtotime($t['date']))); ?></span>
                            </td>
                            <td class="client-cell">
                                <a href="index.php?page=transactions&action=ledger&client_id=<?php echo $t['client_id']; ?>" class="client-link-neon">
                                    <?php echo e($t['client_name'] ?? 'غير محدد'); ?>
                                </a>
                            </td>
                            <td>
                                <?php if ($t['is_advance']): ?>
                                    <span class="type-badge advance"><i class="fas fa-hand-holding-usd"></i> سلفة</span>
                                <?php elseif ($t['type'] == 'income'): ?>
                                    <span class="type-badge income"><i class="fas fa-arrow-down"></i> وارد</span>
                                <?php else: ?>
                                    <span class="type-badge expense"><i class="fas fa-arrow-up"></i> منصرف</span>
                                <?php endif; ?>
                            </td>
                            <td class="amount-cell <?php echo $t['is_advance'] ? 'advance' : $t['type']; ?>">
                                <span class="amount-value">
                                    <?php echo $t['type'] == 'income' ? '+' : '-'; ?>
                                    <?php echo toArabicNum(number_format($t['amount'], 2)); ?>
                                </span>
                                <small>ج.م</small>
                            </td>
                            <td class="payment-cell">
                                <?php 
                                $paymentData = [
                                    'cash' => ['icon' => '💵', 'label' => 'نقدي'],
                                    'transfer' => ['icon' => '🏦', 'label' => 'تحويل'],
                                    'other' => ['icon' => '📝', 'label' => 'أخرى']
                                ];
                                $payment = $paymentData[$t['payment_method']] ?? $paymentData['other'];
                                echo '<span class="payment-badge">' . $payment['icon'] . ' ' . $payment['label'] . '</span>';
                                ?>
                            </td>
                            <td class="notes-cell"><span><?php echo e($t['notes']) ?: '-'; ?></span></td>
                            <td class="actions-cell-neon">
                                <button type="button" class="action-btn-neon edit" onclick="editTransaction(<?php echo $t['id']; ?>)" title="تعديل">
                                    <i class="fas fa-magic"></i>
                                </button>
                                <button type="button" class="action-btn-neon delete" onclick="deleteTransaction(<?php echo $t['id']; ?>)" title="حذف">
                                    <i class="fas fa-fire-alt"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
<!-- CSS moved to transactions-page.css -->
        <!-- Pagination -->
        <?php if (isset($pagination) && $pagination['total'] > 1): ?>
        <div class="pagination-wrapper">
            <div class="pagination">
                <?php 
                $queryString = http_build_query(array_filter([
                    'page' => 'transactions',
                    'search' => $filters['search'] ?? '',
                    'type' => $filters['type'] ?? '',
                    'client_id' => $filters['client_id'] ?? '',
                    'date_from' => $filters['date_from'] ?? '',
                    'date_to' => $filters['date_to'] ?? ''
                ]));
                ?>
                
                <?php if ($pagination['current'] > 1): ?>
                <a href="index.php?<?php echo $queryString; ?>&p=1" class="page-link first" title="الأولى">
                    <i class="fas fa-angle-double-right"></i>
                </a>
                <a href="index.php?<?php echo $queryString; ?>&p=<?php echo $pagination['current'] - 1; ?>" class="page-link prev" title="السابقة">
                    <i class="fas fa-angle-right"></i>
                </a>
                <?php endif; ?>
                
                <?php
                $start = max(1, $pagination['current'] - 2);
                $end = min($pagination['total'], $pagination['current'] + 2);
                for ($i = $start; $i <= $end; $i++):
                ?>
                <a href="index.php?<?php echo $queryString; ?>&p=<?php echo $i; ?>" 
                   class="page-link <?php echo $i == $pagination['current'] ? 'active' : ''; ?>">
                    <?php echo toArabicNum($i); ?>
                </a>
                <?php endfor; ?>
                
                <?php if ($pagination['current'] < $pagination['total']): ?>
                <a href="index.php?<?php echo $queryString; ?>&p=<?php echo $pagination['current'] + 1; ?>" class="page-link next" title="التالية">
                    <i class="fas fa-angle-left"></i>
                </a>
                <a href="index.php?<?php echo $queryString; ?>&p=<?php echo $pagination['total']; ?>" class="page-link last" title="الأخيرة">
                    <i class="fas fa-angle-double-left"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal إضافة/تعديل معاملة - تصميم مطابق للصور بدقة -->
<div id="transactionModal" class="trans-modal-overlay" style="display:none;">
    <div class="trans-modal income" id="transModalContainer">
        <!-- زر الإغلاق - دائرة ملونة أعلى يسار -->
        <button type="button" class="trans-close-btn" onclick="closeModal()">
            <i class="fas fa-times"></i>
        </button>
        
        <!-- الهيدر -->
        <div class="trans-modal-header">
            <div class="trans-header-right">
                <i class="fas fa-plus-circle trans-title-icon"></i>
                <span class="trans-modal-title">تسجيل معاملة جديدة</span>
            </div>
            <div class="trans-header-left">
                <span class="trans-toggle-label">حفظ وإضافة بـ Enter</span>
                <label class="trans-toggle">
                    <input type="checkbox" id="enterSaveToggle" onchange="toggleEnterSave()">
                    <span class="trans-toggle-slider"></span>
                </label>
            </div>
        </div>

        <!-- Type Tabs -->
        <div class="trans-type-tabs-wrapper">
            <div class="trans-type-tabs" id="typeSelector">
                <button type="button" class="trans-type-tab" data-type="income" onclick="setType('income')">
                    <i class="fas fa-arrow-down"></i> وارد
                </button>
                <button type="button" class="trans-type-tab" data-type="expense" onclick="setType('expense')">
                    <i class="fas fa-arrow-up"></i> منصرف
                </button>
                <button type="button" class="trans-type-tab" data-type="advance" onclick="setType('advance')">
                    <i class="fas fa-hand-holding-usd"></i> سلفة
                </button>
            </div>
        </div>
        
        <form id="transactionForm" onsubmit="return saveTransaction(event)">
            <input type="hidden" name="csrf_token" value="<?php echo \App\Core\Session::generateCsrfToken(); ?>">
            <input type="hidden" id="trans_id" name="id" value="">
            <input type="hidden" id="trans_type" name="type" value="income">
            
            <!-- Body -->
            <div class="trans-modal-body" style="padding: 25px; display: flex; flex-direction: column; gap: 15px;">
                
                <!-- Row 1: Client & Category -->
                <div style="display: flex; gap: 15px; align-items: flex-start;">
                    <!-- العميل (65%) -->
                    <div class="trans-field" style="flex: 2;">
                        <label>العميل</label>
                        <div class="trans-input-group">
                            <input type="hidden" id="modal_client" name="client_id">
                            <input type="text" id="client_name_input" name="client_name" 
                                   class="trans-input" placeholder="ابحث عن عميل..." autocomplete="off">
                            <button type="button" class="trans-grid-btn" onclick="openBrowseClientsModal()">
                                <i class="fas fa-th-large"></i>
                            </button>
                            <div id="clientSuggestions" class="suggestions-dropdown"></div>
                        </div>
                    </div>
                    
                    <!-- التصنيف (35%) -->
                    <div class="trans-field" id="categoryCard" style="flex: 1;">
                        <label>التصنيف</label>
                        <div class="trans-category-box" onclick="handleCategoryClick()">
                            <button type="button" class="trans-grid-btn">
                                <i class="fas fa-th-large"></i>
                            </button>
                            <span id="categoryNameDisplay">عام</span>
                            <i class="fas fa-lock trans-lock-icon" id="catLockIcon"></i>
                            <input type="hidden" name="category_id" id="modal_category_id" value="1">
                        </div>
                    </div>
                </div>

                <!-- Row 2: Amount, Payment, Date -->
                <div style="display: flex; gap: 15px; align-items: flex-start;">
                    <!-- المبلغ -->
                    <div class="trans-field" style="flex: 1;">
                        <label>المبلغ</label>
                        <div class="trans-amount-box">
                            <input type="number" step="0.01" id="modal_amount" name="amount" 
                                   class="trans-amount-input" placeholder=".." required>
                            <span class="trans-currency">ج.م</span>
                        </div>
                    </div>
                    
                    <!-- طريقة الدفع -->
                    <div class="trans-field" style="flex: 1.5;">
                        <label>طريقة الدفع</label>
                        <div class="trans-payment-btns">
                            <label class="trans-payment-btn">
                                <input type="radio" name="payment_method" value="cash" checked>
                                <span>نقدي</span>
                            </label>
                            <label class="trans-payment-btn">
                                <input type="radio" name="payment_method" value="bank">
                                <span>بنكي</span>
                            </label>
                            <label class="trans-payment-btn">
                                <input type="radio" name="payment_method" value="other">
                                <span>أخرى</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- التاريخ -->
                    <div class="trans-field" style="flex: 1;">
                        <label>التاريخ</label>
                        <input type="text" id="modal_date" name="date" value="<?php echo date('d-m-Y'); ?>" 
                               class="trans-input trans-date-input" required 
                               placeholder="DD-MM-YYYY"
                               onfocus="(this.type='date')" 
                               onblur="(this.type='text')">
                    </div>
                </div>

                <!-- Row 3: Notes -->
                <div class="trans-field trans-notes-field" style="width: 100%;">
                    <label>ملاحظات</label>
                    <textarea id="modal_notes" name="notes" class="trans-textarea" placeholder="أضف ملاحظات (اختياري)..." style="min-height: 80px;"></textarea>
                </div>

            </div>
            
            <!-- Footer -->
            <div class="trans-modal-footer">
                <button type="button" class="trans-footer-btn trans-btn-save-add" id="saveAddBtn" onclick="saveAndAddAnother()">
                    <i class="fas fa-plus"></i>
                    <span>حفظ وإضافة جديد</span>
                </button>
                <button type="submit" class="trans-footer-btn trans-btn-save">
                    <i class="fas fa-check"></i>
                    <span>حفظ</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Similarity Warning Modal -->
<div id="similarityModal" class="modal-overlay" style="display:none; z-index: 2200;">
    <div class="modal-container warning-modal">
        <div class="warning-content">
            <div class="warning-icon-wrapper">
                <div class="pulse-ring"></div>
                <i class="fas fa-question"></i>
            </div>
            <h3 class="warning-title">هل تقصد هذا العميل؟</h3>
            <p class="warning-desc">الاسم الذي أدخلته مشابه لأسماء مسجلة بالفعل. يرجى التأكد لعدم تكرار البيانات.</p>
            
            <div id="similarCandidatesList" class="similar-candidates-list">
                <!-- Items injected via JS -->
            </div>
            
            <div class="warning-actions">
                <button type="button" class="btn-confirm-new" onclick="confirmNewClient()">
                    <i class="fas fa-plus-circle"></i> نعم، هو عميل جديد
                </button>
                <button type="button" class="btn-cancel-warning" onclick="closeSimilarityModal()">إلغاء</button>
            </div>
        </div>
    </div>
</div>

<!-- Browse Clients Modal (Modern Wide) -->
<div id="browseClientsModal" class="modal-overlay" style="display:none; z-index: 2500;">
    <div class="modal-container browse-modal-modern">
        <div class="browse-header-modern">
            <div class="search-bar-integrated">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="browseSearchInput" placeholder="ابحث عن اسم العميل أو رقم الهاتف..." onkeyup="filterBrowseList(this.value)">
                <button class="btn-new-client-header" onclick="openQuickAddClientModal()">
                    <i class="fas fa-user-plus"></i> جديد
                </button>
                <div class="header-divider"></div>
                <button class="close-browse-simple" onclick="closeBrowseClientsModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="browse-grid-modern" id="browseClientsGrid">
            <!-- Cards injected via JS -->
        </div>
    </div>
</div>

    <!-- Category Picker Popup (Copied from Clients Page for consistency) -->
    <div id="categoryPopup" class="category-popup-overlay" style="z-index: 10002;">
        <div class="category-popup">
            <div class="category-popup-header">
                <h3><i class="fas fa-tags"></i> اختر تصنيف العميل</h3>
                <p>انقر على التصنيف المناسب</p>
            </div>
            <div class="category-grid" id="categoryGrid">
                <?php 
                $categoryIcons = [
                    'صياد' => '🎣',
                    'تاجر' => '🏪',
                    'عمال' => '👷',
                    'سُلَف' => '💰',
                    'زكاة مال' => '🕌',
                    'مصلحة' => '🏢',
                    'خزينة' => '🏦',
                    'أخرى' => '📋'
                ];
                if (isset($categories)):
                foreach ($categories as $cat): 
                    $icon = $categoryIcons[$cat['name']] ?? '📁';
                ?>
                <div class="category-card" data-id="<?php echo $cat['id']; ?>" data-name="<?php echo e($cat['name']); ?>" onclick="selectCategory(<?php echo $cat['id']; ?>, '<?php echo e($cat['name']); ?>')">
                    <div class="category-card-icon"><?php echo $icon; ?></div>
                    <div class="category-card-name"><?php echo e($cat['name']); ?></div>
                </div>
                <?php endforeach; endif; ?>
            </div>
            <button type="button" class="category-popup-close" onclick="closeCategoryPopup()">
                <i class="fas fa-times"></i> إغلاق
            </button>
        </div>
    </div>

    <!-- Pass Data to JS -->
    <script>
        window.categoriesData = <?php echo json_encode($categories ?? []); ?>;
    </script>

<!-- Category Management Modal -->
<div id="categoryModal" class="modal-overlay" style="display:none; z-index: 2300;">
    <div class="modal-container category-modal">
        <div class="browse-header">
            <h3><i class="fas fa-tags"></i> إدارة التصنيفات</h3>
            <button class="close-browse" onclick="closeCategoryModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="category-grid" id="categoryGrid">
             <!-- Cards -->
        </div>
        <div class="category-form">
             <input type="text" id="newCategoryName" placeholder="اسم تصنيف جديد...">
             <button onclick="addNewCategory()"><i class="fas fa-plus"></i> إضافة</button>
        </div>
    </div>
</div>

<!-- Transactions Page CSS -->
<!-- الملف الأصلي (معلق للاختبار) -->
<!-- <link rel="stylesheet" href="<?php echo asset('css/transactions-page.css'); ?>?v=UNIFIED_THEME_CYAN_<?php echo time(); ?>"> -->

<!-- ===== Split CSS (ACTIVE FOR TESTING) ===== -->
<link rel="stylesheet" href="<?php echo asset('css/transactions-core.css'); ?>?v=SPLIT_TEST_<?php echo time(); ?>">
<link rel="stylesheet" href="<?php echo asset('css/transactions-modals.css'); ?>?v=SPLIT_TEST_<?php echo time(); ?>">
<link rel="stylesheet" href="<?php echo asset('css/transactions-themes.css'); ?>?v=SPLIT_TEST_<?php echo time(); ?>">

<link rel="stylesheet" href="<?php echo asset('css/glass-modal.css'); ?>?v=<?php echo time(); ?>">
<!-- Transactions Page JavaScript -->
<script>
    window.csrfToken = "<?php echo \App\Core\Session::generateCsrfToken(); ?>";
    window.clientsData = <?php echo json_encode($clients ?? []); ?>;
</script>
<script src="<?php echo asset('js/transactions-page.js'); ?>?v=SMART_MODAL_V3_<?php echo time(); ?>" defer></script>

<!-- ===== Virtual Scroll Module (Phase 12) ===== -->
<!-- يُفعَّل تلقائياً فقط إذا كان عدد الصفوف > 50 -->
<script src="<?php echo asset('js/virtual-scroll.js'); ?>?v=<?php echo time(); ?>" defer></script>

<!-- Filter Type Modal -->
<div id="filterTypeModal" style="position:fixed !important; top:0 !important; left:0 !important; width:100vw !important; height:100vh !important; background:rgba(0,0,0,0.9) !important; z-index:999999 !important; display:none; justify-content:center; align-items:center;">
    <div class="glass-modal-container" style="max-width: 350px;">
        <div class="glass-modal-header">
            <div class="glass-modal-icon-wrapper" style="background: rgba(59, 130, 246, 0.2);">
                <i class="fas fa-tags" style="color: #3b82f6;"></i>
            </div>
            <div class="glass-modal-title">اختر نوع المعاملة</div>
        </div>
        <div class="glass-modal-body" style="padding: 0;">
            <div class="filter-options-list">
                <div class="filter-option" onclick="selectFilterType('', 'كل الأنواع')">
                    <i class="fas fa-list"></i>
                    <span>كل الأنواع</span>
                </div>
                <div class="filter-option income" onclick="selectFilterType('income', 'وارد')">
                    <i class="fas fa-arrow-down"></i>
                    <span>وارد</span>
                </div>
                <div class="filter-option expense" onclick="selectFilterType('expense', 'منصرف')">
                    <i class="fas fa-arrow-up"></i>
                    <span>منصرف</span>
                </div>
                <div class="filter-option advance" onclick="selectFilterType('advance', 'سلفة')">
                    <i class="fas fa-hand-holding-usd"></i>
                    <span>سلفة</span>
                </div>
            </div>
        </div>
        <div class="glass-modal-footer">
            <button class="glass-btn cancel" onclick="closeFilterTypeModal()">
                <i class="fas fa-times"></i> إغلاق
            </button>
        </div>
    </div>
</div>

<!-- Filter Client Modal -->
<div id="filterClientModal" style="position:fixed !important; top:0 !important; left:0 !important; width:100vw !important; height:100vh !important; background:rgba(0,0,0,0.9) !important; z-index:999999 !important; display:none; justify-content:center; align-items:center;">
    <div class="glass-modal-container" style="max-width: 400px; max-height: 70vh;">
        <div class="glass-modal-header">
            <div class="glass-modal-icon-wrapper" style="background: rgba(168, 85, 247, 0.2);">
                <i class="fas fa-user" style="color: #a855f7;"></i>
            </div>
            <div class="glass-modal-title">اختر العميل</div>
        </div>
        <div class="glass-modal-body" style="padding: 10px;">
            <input type="text" class="filter-search-input" placeholder="بحث عن عميل..." oninput="searchFilterClients(this.value)">
            <div id="filterClientsList" class="filter-options-list" style="max-height: 300px; overflow-y: auto;">
                <!-- سيتم ملؤها من JavaScript -->
            </div>
        </div>
        <div class="glass-modal-footer">
            <button class="glass-btn cancel" onclick="closeFilterClientModal()">
                <i class="fas fa-times"></i> إغلاق
            </button>
        </div>
    </div>
</div>

<!-- System Confirm Modal -->
<div id="systemConfirmModal" class="glass-modal-overlay">
    <div class="glass-modal-container">
        <div class="glass-modal-header">
            <div class="glass-modal-icon-wrapper" id="sysModalIcon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="glass-modal-title" id="sysModalTitle">تأكيد الإجراء</div>
        </div>
        <div class="glass-modal-body" id="sysModalBody">
            هل أنت متأكد من إتمام هذا الإجراء؟
        </div>
        <div class="glass-modal-footer">
            <button class="glass-btn cancel" onclick="closeSystemConfirm()">
                <i class="fas fa-times"></i> إلغاء
            </button>
            <button class="glass-btn confirm" id="sysModalActionBtn">
                <i class="fas fa-check"></i> نعم، تنفيذ
            </button>
        </div>
    </div>
</div>

<!-- Client Modal (Smart Glass Design) - Unified with Clients Page -->
<div id="quickAddClientModal" class="glass-modal-overlay" style="z-index: 5000;">
    <div class="glass-modal-container" style="max-width: 600px;">
        <div class="glass-modal-header">
            <div class="glass-modal-icon-wrapper" style="background: rgba(168, 85, 247, 0.2); border-color: rgba(168, 85, 247, 0.3);">
                <i class="fas fa-user-plus" style="color: #a855f7;"></i>
            </div>
            <div class="glass-modal-title">
                <span id="modalTitle">إضافة عميل جديد</span>
            </div>
            
            <!-- Smart Mode Toggle -->
            <div class="smart-toggle-wrapper" style="margin-right: auto; display: flex; align-items: center; gap: 10px;">
                <span style="color: rgba(255,255,255,0.7); font-size: 0.9rem;">حفظ تلقائي (Enter)</span>
                <label class="switch" style="position: relative; display: inline-block; width: 46px; height: 24px;"> <!-- Updated size -->
                    <input type="checkbox" id="smartAutoSaveToggle">
                    <span class="slider round"></span>
                </label>
            </div>

            <button type="button" class="glass-modal-close" onclick="closeQuickAddClientModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="quickClientForm" onsubmit="return false;">
            <input type="hidden" name="client_id" id="clientId" value="">
            <input type="hidden" name="csrf_token" value="<?php echo \App\Core\Session::generateCsrfToken(); ?>">
            
            <div class="glass-modal-body">
                <div class="form-group-neon">
                    <label>اسم العميل <span class="required" style="color: #a855f7">*</span></label>
                    <input type="text" name="name" id="quickClientName" class="glass-input" placeholder="اكتب اسم العميل كاملاً" required>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                    <div class="form-group-neon">
                        <label>التصنيف <span class="required" style="color: #a855f7">*</span></label>
                        <input type="hidden" name="category_id" id="quickClientCategory" required>
                        <button type="button" class="glass-input" id="categoryPickerBtn" onclick="openCategoryPopup()" style="text-align: right; display: flex; justify-content: space-between; align-items: center; color: rgba(255,255,255,0.8); cursor: pointer;">
                            <span id="selectedCategoryText">اختر التصنيف...</span>
                        </button>
                    </div>
                    <div class="form-group-neon">
                        <label>رقم الهاتف</label>
                        <input type="tel" name="phone" id="quickClientPhone" class="glass-input" placeholder="01xxxxxxxxx">
                    </div>
                </div>
                
                <div class="form-group-neon" id="customCategoryGroup" style="display: none; margin-top: 15px;">
                    <label style="color: #a855f7;">تفاصيل التصنيف (أخرى) <span class="required">*</span></label>
                    <input type="text" name="category_custom" id="clientCategoryCustom" class="glass-input" placeholder="يرجى التوضيح..." style="border-color: #a855f7;">
                </div>
                
                <div class="form-group-neon" style="margin-top: 15px;">
                    <label>العنوان</label>
                    <input type="text" name="address" id="clientAddress" class="glass-input" placeholder="عنوان العميل بالتفصيل">
                </div>
            </div>
            
            <div class="glass-modal-footer">
                <button type="button" class="glass-btn cancel" onclick="closeQuickAddClientModal()">
                    <i class="fas fa-times"></i> إلغاء
                </button>
                <button type="button" class="glass-btn confirm" id="saveQuickClientBtn" onclick="saveQuickClient()">
                    <i class="fas fa-save"></i> <span>حفظ البيانات</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- System Alert Modal (For Errors/Success) -->
<div id="systemAlertModal" class="glass-modal-overlay" style="z-index: 10000;">
    <div class="glass-modal-container">
        <div class="glass-modal-header">
            <div class="glass-modal-icon-wrapper" id="sysAlertIcon">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="glass-modal-title" id="sysAlertTitle">تنبيه</div>
        </div>
        <div class="glass-modal-body" id="sysAlertBody">
            نص التنبيه هنا
        </div>
        <div class="glass-modal-footer">
            <button class="glass-btn confirm" onclick="closeSystemAlert()">
                <i class="fas fa-check"></i> موافق
            </button>
        </div>
    </div>
</div>

<style>
/* Filter Modal Styles - Mini Cards Design */
.glass-modal-container {
    max-width: 650px !important; /* Wider modal for better grid layout */
    width: 90% !important;
}

/* Specific override for Filter Client Modal to be even wider if needed */
#filterClientModal .glass-modal-container {
    max-width: 750px !important;
}

.filter-options-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); /* Slightly wider cards */
    gap: 15px;
    padding: 20px;
    max-height: 50vh; /* Responsive height */
    overflow-y: auto;
    align-content: start; /* Prevent stretching if few items */
}

.filter-option {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 15px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    color: rgba(255, 255, 255, 0.7);
    aspect-ratio: 1/1; /* Square cards */
    text-align: center;
}

.filter-option:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.3);
    color: #fff;
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

/* Selected State Style (Optional if we add .active class later) */
.filter-option:active {
    transform: scale(0.95);
}

.filter-option i {
    font-size: 2rem;
    margin-bottom: 5px;
    width: auto;
    transition: transform 0.3s ease;
}

/* Specific Colors for Icons with Glow */
.filter-option.income i { color: #10b981; filter: drop-shadow(0 0 8px rgba(16, 185, 129, 0.2)); }
.filter-option.expense i { color: #ef4444; filter: drop-shadow(0 0 8px rgba(239, 68, 68, 0.2)); }
.filter-option.advance i { color: #f59e0b; filter: drop-shadow(0 0 8px rgba(245, 158, 11, 0.2)); }

.filter-option:hover.income { border-color: #10b981; background: rgba(16, 185, 129, 0.1); }
.filter-option:hover.expense { border-color: #ef4444; background: rgba(239, 68, 68, 0.1); }
.filter-option:hover.advance { border-color: #f59e0b; background: rgba(245, 158, 11, 0.1); }

.filter-option:hover i {
    transform: scale(1.15);
}

.filter-search-input {
    width: calc(100% - 40px);
    margin: 20px 20px 0 20px;
    padding: 12px 15px;
    background: rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    color: #fff;
    font-family: 'Cairo', sans-serif;
    font-size: 0.95rem;
}
.filter-search-input:focus {
    outline: none;
    border-color: #a855f7;
    box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.1);
}
.filter-search-input::placeholder {
    color: rgba(255, 255, 255, 0.4);
</body>
</html>
