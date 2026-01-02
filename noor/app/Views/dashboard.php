<!-- Dashboard View - Enhanced (Neon Glass Edition) -->
<link rel="stylesheet" href="<?php echo asset('css/dashboard.css'); ?>?v=<?php echo time(); ?>">
<style>
/* Inline Mobile Fix for Dashboard */
@media (max-width: 900px) {
    .neon-hero-header { flex-direction: column !important; gap: 15px !important; padding: 15px !important; }
    .head-center-section { flex-direction: column !important; width: 100% !important; gap: 15px !important; }
    .actions-pill { flex-wrap: wrap !important; justify-content: center !important; gap: 8px !important; padding: 10px !important; width: 100% !important; }
    .stats-group { flex-wrap: wrap !important; justify-content: center !important; gap: 15px !important; }
    .header-v-separator { display: none !important; }
}
@media (max-width: 600px) {
    .dashboard-stats-compact { grid-template-columns: 1fr !important; gap: 10px !important; }
    .stat-card-mini { padding: 12px !important; width: 100% !important; }
    .dash-head-btn { padding: 6px 10px !important; }
    .dash-head-btn span { display: none !important; }
}
</style>

<div class="neon-hero-header">
    <!-- Right: Title -->
    <div class="neon-title-group">
        <i class="fas fa-chart-line neon-title-icon"></i>
        <div class="neon-title-text">
            لوحة التحكم
            <small>ملخص الأداء والعمليات اليومية</small>
        </div>
    </div>

    <!-- Center: Actions & Stats -->
    <div class="head-center-section">
        
        <!-- Actions -->
        <div class="actions-pill">
            <a href="index.php?page=transactions&openModal=income" class="dash-head-btn income" title="تسجيل وارد">
                <i class="fas fa-plus-circle"></i> <span>وارد</span>
            </a>
            <a href="index.php?page=transactions&openModal=expense" class="dash-head-btn expense" title="تسجيل منصرف">
                <i class="fas fa-minus-circle"></i> <span>منصرف</span>
            </a>
            <a href="index.php?page=transactions&openModal=advance" class="dash-head-btn advance" title="تسجيل سلفة">
                <i class="fas fa-hand-holding-usd"></i> <span>سلفة</span>
            </a>
            <a href="index.php?page=clients&openModal=1" class="dash-head-btn client" title="إضافة عميل">
                <i class="fas fa-user-plus"></i> <span>عميل</span>
            </a>
            <?php if(\App\Core\Session::isAdmin()): ?>
            <!-- Backup button moved to header -->
            <?php endif; ?>
        </div>

        <!-- Vertical Separator -->
        <div class="header-v-separator"></div>

        <!-- Smart Mini Stats -->
        <div class="stats-group">
            <div class="mini-stat ms-clients" data-label="عميل نشط">
                <i class="fas fa-users mini-stat-icon"></i>
                <div class="mini-stat-val"><?php echo toArabicNum($quickStats['total_clients']); ?></div>
            </div>
            <div class="mini-stat ms-trans" data-label="معاملات اليوم">
                <i class="fas fa-exchange-alt mini-stat-icon"></i>
                <div class="mini-stat-val"><?php echo toArabicNum($quickStats['today_transactions']); ?></div>
            </div>
            <div class="mini-stat ms-advances" data-label="سلف مفتوحة">
                <i class="fas fa-hand-holding-usd mini-stat-icon"></i>
                <div class="mini-stat-val"><?php echo toArabicNum($quickStats['open_advances_count']); ?></div>
            </div>
        </div>
    </div>

    <!-- Left: Date -->
    <div class="head-date-section">
        <div class="head-date-text"><?php echo toArabicNum(date('Y/m/d')); ?></div>
        <i class="fas fa-calendar-alt head-date-icon"></i>
    </div>
</div>

<!-- New Layout Container -->
<div class="dashboard-new-grid">
    
    <!-- Left Column: Stats + Chart -->
    <div class="layout-left">
        <!-- Compact Stats (Neon Styled) -->
        <div class="dashboard-stats-compact">
            <div class="stat-card-mini income">
                <div class="mini-icon-wrapper">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div class="mini-info">
                    <span>الوارد</span>
                    <h4 class="mini-amount"><?php echo toArabicNum(number_format($stats['income'], 0)); ?> <small>ج.م</small></h4>
                </div>
                <div class="mini-glow"></div>
            </div>
            <div class="stat-card-mini expense">
                <div class="mini-icon-wrapper">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div class="mini-info">
                    <span>المنصرف</span>
                    <h4 class="mini-amount"><?php echo toArabicNum(number_format($stats['expense'], 0)); ?> <small>ج.م</small></h4>
                </div>
                <div class="mini-glow"></div>
            </div>
            <div class="stat-card-mini advances">
                <div class="mini-icon-wrapper">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <div class="mini-info">
                    <span>السلف</span>
                    <h4 class="mini-amount"><?php echo toArabicNum(number_format($stats['advances'], 0)); ?> <small>ج.م</small></h4>
                </div>
                <div class="mini-glow"></div>
            </div>
            <div class="stat-card-mini balance <?php echo $stats['net_balance'] >= 0 ? 'positive' : 'negative'; ?>">
                <div class="mini-icon-wrapper">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="mini-info">
                    <span>الخزينة</span>
                    <h4 class="mini-amount"><?php echo toArabicNum(number_format($stats['net_balance'], 0)); ?> <small>ج.م</small></h4>
                </div>
                <div class="mini-glow"></div>
            </div>
        </div>

        <!-- Chart -->
        <div class="card chart-card">
            <div class="card-header">
                <h3><i class="fas fa-chart-area"></i> تطور الخزينة (٧ أيام)</h3>
            </div>
            <div class="chart-container" style="height: 320px;">
                <canvas id="weeklyChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Right Column: Transactions -->
    <div class="layout-right">
        <div class="card transactions-card-tall">
            <div class="card-header">
                <h3><i class="fas fa-history"></i> آخر المعاملات</h3>
                <a href="index.php?page=transactions" class="btn-link">الكل</a>
            </div>
            <div class="transactions-list full-height">
                <?php if (empty($latestTransactions)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>لا توجد معاملات</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($latestTransactions as $trans): ?>
                    <div class="transaction-item compact">
                        <div class="trans-icon <?php echo $trans['type']; ?>">
                            <i class="fas fa-<?php echo $trans['type'] == 'income' ? 'arrow-down' : 'arrow-up'; ?>"></i>
                        </div>
                        <div class="trans-details">
                            <span class="trans-client"><?php echo e($trans['client_name'] ?? '..'); ?></span>
                        </div>
                        <div class="trans-amount <?php echo $trans['type']; ?>">
                            <?php echo toArabicNum(number_format($trans['amount'], 0)); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Pass PHP Data to JS
    const chartData = <?php echo json_encode($chartData); ?>;
</script>
<script src="<?php echo asset('js/dashboard.js'); ?>"></script>




