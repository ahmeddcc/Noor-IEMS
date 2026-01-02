<?php
// صفحة التقرير اليومي - تصميم طباعة مطابق لكشف الحساب
?>
<style>
    /* إخفاء عناصر النظام الأساسية */
    .cyber-header-container, 
    .glass-dock-container, 
    .header-action-buttons-neon, 
    .neon-hero-header, 
    .btn, 
    .sidebar, 
    .navbar,
    .footer { 
        display: none !important; 
    }

    /* إعادة ضبط المتغيرات الأساسية */
    :root { --primary-color: #000; --bg-gray: #f9f9f9; }
    
    body { 
        font-family: 'Segoe UI', Tahoma, sans-serif !important; 
        margin: 0 !important; 
        padding: 20px !important; 
        color: #333 !important; 
        background: #fff !important;
        direction: rtl; 
    }
    
    .daily-wrapper {
        background: #fff;
        min-height: 100vh;
    }

    /* تنسيق الترويسة */
    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid #000;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }

    .system-info { text-align: right; }
    .system-info h1 { 
        margin: 0; 
        font-size: 24px; 
        font-weight: bold; 
        color: #000 !important;
        text-shadow: none !important;
    }
    .system-info p { 
        margin: 5px 0 0; 
        font-size: 14px; 
        color: #333 !important;
        text-shadow: none !important;
    }
    
    .report-title {
        text-align: center;
        border: 2px solid #000;
        padding: 10px 30px;
        border-radius: 10px;
        background: #fff !important;
    }
    .report-title h2 { 
        margin: 0; 
        font-size: 24px; 
        font-weight: bold; 
        color: #000 !important;
        text-shadow: none !important;
    }
    .report-title p {
        margin: 5px 0 0;
        font-size: 16px;
        font-weight: bold;
        color: #333 !important;
    }

    .report-info { text-align: left; }
    .report-info p { margin: 4px 0; font-size: 15px; color: #000 !important; }
    .report-info strong { font-size: 16px; font-weight: bold; color: #000 !important; }

    /* أزرار التحكم */
    .actions-bar {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .btn-print, .btn-back { 
        padding: 10px 25px; 
        border: none; 
        border-radius: 5px; 
        cursor: pointer; 
        font-family: inherit;
        font-weight: bold;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .btn-print { background: #333; color: #fff; }
    .btn-print:hover { background: #000; }
    
    .btn-back { background: #e0e0e0; color: #333; }
    .btn-back:hover { background: #d0d0d0; }

    /* مربعات ملخص التقرير */
    .summary-boxes { display: flex; gap: 15px; margin-bottom: 30px; }
    
    .box { 
        flex: 1; 
        border: 2px solid #000 !important; 
        text-align: center; 
        border-radius: 5px; 
        overflow: hidden; 
        background: #fff !important; 
    }
    
    .box-title { 
        background: #fff !important; 
        padding: 8px; 
        font-size: 13px; 
        border-bottom: 2px solid #000 !important; 
        color: #000 !important; 
        font-weight: bold; 
    }
    
    .box-value { 
        background: #f9f9f9 !important; 
        padding: 12px; 
        font-weight: bold; 
        font-size: 16px; 
        color: #000 !important; 
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    .box-value.income { color: #060 !important; }
    .box-value.expense { color: #c00 !important; }
    .box-value.advance { color: #f60 !important; }

    /* الجدول */
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    
    th, td { 
        border: 1px solid #000 !important; 
        padding: 10px; 
        text-align: center; 
        font-size: 14px; 
        color: #000 !important; 
    }
    
    th { background-color: #f2f2f2 !important; font-weight: bold; -webkit-print-color-adjust: exact !important; }
    .total-row { background-color: #f2f2f2 !important; font-weight: bold; -webkit-print-color-adjust: exact !important; }
    
    .badge { 
        padding: 3px 10px; 
        border-radius: 15px; 
        font-size: 12px; 
        font-weight: bold;
    }
    .badge-income { background: #dfd !important; color: #060 !important; -webkit-print-color-adjust: exact !important; }
    .badge-expense { background: #fdd !important; color: #c00 !important; -webkit-print-color-adjust: exact !important; }
    .badge-advance { background: #fed !important; color: #f60 !important; -webkit-print-color-adjust: exact !important; }

    /* إعدادات الطباعة */
    @media print {
        .no-print, .actions-bar { display: none !important; }
        body { padding: 10px !important; }
        .header-container { margin-bottom: 15px; }
        .summary-boxes { margin-bottom: 20px; }
    }
</style>

<div class="daily-wrapper">

    <div class="actions-bar no-print">
        <button class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> طباعة التقرير</button>
        <a href="index.php?page=reports" class="btn-back"><i class="fas fa-arrow-right"></i> عودة للتقارير</a>
    </div>

    <!-- الترويسة -->
    <div class="header-container">
        <div class="system-info">
            <h1><?php echo \App\Models\Setting::get('company_name', 'الشركة'); ?></h1>
            <p>الإدارة المالية والحسابات</p>
        </div>
        
        <div class="report-title">
            <h2>التقرير اليومي</h2>
            <p><?php echo date('Y/m/d', strtotime($date)); ?></p>
        </div>

        <div class="report-info">
            <p>تاريخ الطباعة: <strong><?php echo date('d-m-Y H:i'); ?></strong></p>
            <p>عدد المعاملات: <strong><?php echo count($transactions); ?></strong></p>
        </div>
    </div>

    <!-- مربعات الملخص -->
    <div class="summary-boxes">
        <div class="box">
            <div class="box-title">إجمالي الوارد</div>
            <div class="box-value income"><?php echo number_format($stats['income'], 2); ?> ج.م</div>
        </div>
        <div class="box">
            <div class="box-title">إجمالي المنصرف</div>
            <div class="box-value expense"><?php echo number_format($stats['expense'], 2); ?> ج.م</div>
        </div>
        <div class="box">
            <div class="box-title">إجمالي السلف</div>
            <div class="box-value advance"><?php echo number_format($stats['advances'], 2); ?> ج.م</div>
        </div>
        <div class="box">
            <div class="box-title">صافي اليوم</div>
            <div class="box-value" style="color: <?php echo $stats['net_balance'] >= 0 ? '#060' : '#c00'; ?> !important;">
                <?php echo number_format($stats['net_balance'], 2); ?> ج.م
            </div>
        </div>
    </div>

    <!-- الجدول -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>العميل</th>
                <th>التصنيف</th>
                <th>النوع</th>
                <th>المبلغ</th>
                <th>طريقة الدفع</th>
                <th>ملاحظات</th>
            </tr>
        </thead>
        <tbody>
            <?php $counter = 1; foreach ($transactions as $t): ?>
            <tr>
                <td><?php echo $counter++; ?></td>
                <td style="text-align: right; font-weight: bold;"><?php echo e($t['client_name']); ?></td>
                <td><?php echo e($t['category_name']); ?></td>
                <td>
                    <?php 
                    if ($t['is_advance']) echo '<span class="badge badge-advance">سلفة</span>';
                    elseif ($t['type'] == 'income') echo '<span class="badge badge-income">وارد</span>';
                    else echo '<span class="badge badge-expense">منصرف</span>';
                    ?>
                </td>
                <td style="font-weight: bold;"><?php echo number_format($t['amount'], 2); ?></td>
                <td><?php echo $t['payment_method']; ?></td>
                <td style="font-size: 12px; color: #666 !important;"><?php echo e($t['notes']); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($transactions)): ?>
                <tr><td colspan="7" style="padding: 30px; color: #999;">لا توجد معاملات في هذا التاريخ</td></tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" style="text-align: left;">إجمالي اليوم:</td>
                <td colspan="3">
                    وارد: <?php echo number_format($stats['income'], 2); ?> | 
                    منصرف: <?php echo number_format($stats['expense'], 2); ?> | 
                    صافي: <?php echo number_format($stats['net_balance'], 2); ?>
                </td>
            </tr>
        </tfoot>
    </table>

</div>
