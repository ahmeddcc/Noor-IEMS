<?php
// صفحة تقرير السلف المفتوحة - تصميم طباعة مطابق لكشف الحساب
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
    
    .advances-wrapper {
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

    .report-date { text-align: left; }
    .report-date p { margin: 4px 0; font-size: 15px; color: #000 !important; }
    .report-date strong { font-size: 18px; font-weight: bold; color: #000 !important; }

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

    /* إعدادات الطباعة */
    @media print {
        .no-print, .actions-bar { display: none !important; }
        body { padding: 10px !important; }
        .header-container { margin-bottom: 15px; }
        .summary-boxes { margin-bottom: 20px; }
    }
</style>

<div class="advances-wrapper">

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
            <h2>تقرير السلف المفتوحة</h2>
        </div>

        <div class="report-date">
            <p>تاريخ التقرير: <strong><?php echo date('d-m-Y'); ?></strong></p>
            <p>عدد العملاء: <strong><?php echo count($advances); ?></strong></p>
        </div>
    </div>

    <!-- مربعات الملخص -->
    <div class="summary-boxes">
        <div class="box">
            <div class="box-title">عدد العملاء المدينين</div>
            <div class="box-value"><?php echo count($advances); ?> عميل</div>
        </div>
        <div class="box">
            <div class="box-title">إجمالي السلف</div>
            <div class="box-value"><?php echo number_format(array_sum(array_column($advances, 'total_advances')), 2); ?> ج.م</div>
        </div>
        <div class="box">
            <div class="box-title">إجمالي المسدد</div>
            <div class="box-value"><?php echo number_format(array_sum(array_column($advances, 'total_repaid')), 2); ?> ج.م</div>
        </div>
        <div class="box">
            <div class="box-title">إجمالي المديونية</div>
            <div class="box-value" style="color: #c00 !important;"><?php echo number_format($totalAdvances, 2); ?> ج.م</div>
        </div>
    </div>

    <!-- الجدول -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>اسم العميل</th>
                <th>رقم الهاتف</th>
                <th>عدد السلف</th>
                <th>إجمالي السلف</th>
                <th>إجمالي المسدد</th>
                <th>المتبقي (المديونية)</th>
                <th>آخر حركة</th>
            </tr>
        </thead>
        <tbody>
            <?php $counter = 1; foreach ($advances as $client): ?>
            <tr>
                <td><?php echo $counter++; ?></td>
                <td style="text-align: right; font-weight: bold;"><?php echo e($client['client_name']); ?></td>
                <td><?php echo $client['phone'] ? $client['phone'] : '-'; ?></td>
                <td><?php echo $client['advances_count']; ?></td>
                <td><?php echo number_format($client['total_advances'], 2); ?></td>
                <td><?php echo number_format($client['total_repaid'], 2); ?></td>
                <td style="font-weight: bold; color: #c00 !important;"><?php echo number_format($client['remaining_debt'], 2); ?></td>
                <td><?php echo $client['last_transaction_date']; ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($advances)): ?>
                <tr><td colspan="8" style="padding: 30px; color: #999;">لا توجد ديون مستحقة حالياً</td></tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" style="text-align: left;">الإجمالي الكلي:</td>
                <td><?php echo number_format(array_sum(array_column($advances, 'total_advances')), 2); ?></td>
                <td><?php echo number_format(array_sum(array_column($advances, 'total_repaid')), 2); ?></td>
                <td style="font-size: 16px; color: #c00 !important;"><?php echo number_format($totalAdvances, 2); ?> ج.م</td>
                <td>-</td>
            </tr>
        </tfoot>
    </table>

</div>
