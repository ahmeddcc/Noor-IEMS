<link rel="stylesheet" href="<?php echo 'assets/css/reports.css'; ?>">

<div class="neon-hero-header">
    <div class="neon-title-group">
        <i class="fas fa-chart-pie neon-title-icon"></i>
        <div class="neon-title-text">
            التقارير المالية
            <small>استعراض وتحليل الأداء المالي والعمليات</small>
        </div>
    </div>
</div>

<div class="stats-grid">
    <!-- تقرير يومي -->
    <div class="stat-card income">
        <div class="stat-icon-wrapper">
            <i class="fas fa-calendar-day"></i>
        </div>
        <div class="stat-info" style="flex: 1;">
            <strong>تقرير يومي</strong>
            <span>ملخص شامل لمعاملات يوم محدد</span>
            
            <form action="index.php" method="GET" style="margin-top: 15px; display: flex; gap: 10px;">
                <input type="hidden" name="page" value="reports">
                <input type="hidden" name="action" value="daily">
                <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" class="neon-input" required style="flex: 1;">
                <button type="submit" class="neon-btn"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>

    <!-- تقرير فترة -->
    <div class="stat-card expense">
        <div class="stat-icon-wrapper">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="stat-info" style="flex: 1;">
            <strong>تقرير فترة زمنية</strong>
            <span>تحليل مالي لفترة محددة</span>
            
            <form action="index.php" method="GET" style="margin-top: 15px; display: flex; flex-direction: column; gap: 10px;">
                <input type="hidden" name="page" value="reports">
                <input type="hidden" name="action" value="range">
                <div style="display: flex; gap: 10px;">
                    <input type="date" name="from" value="<?php echo date('Y-m-01'); ?>" class="neon-input" required style="flex: 1;">
                    <input type="date" name="to" value="<?php echo date('Y-m-d'); ?>" class="neon-input" required style="flex: 1;">
                </div>
                <button type="submit" class="neon-btn" style="justify-content: center;">عرض التقرير</button>
            </form>
        </div>
    </div>

    <!-- تقرير السلف -->
    <div class="stat-card advances">
        <div class="stat-icon-wrapper">
            <i class="fas fa-hand-holding-usd"></i>
        </div>
        <div class="stat-info" style="flex: 1;">
            <strong>تقرير السلف</strong>
            <span>متابعة الديون المستحقة</span>
            <div style="margin-top: 20px;">
                <a href="index.php?page=reports&action=advances" class="neon-btn" style="width: 100%; justify-content: center;">
                    عرض التقرير
                </a>
            </div>
        </div>
    </div>
</div>
