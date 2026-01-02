<?php
// صفحة كشف حساب العميل - تصميم مخصص حسب الطلب
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
        background: #fff !important; /* فرض خلفية بيضاء لكامل الصفحة */
        direction: rtl; 
    }
    
    /* تنسيق الترويسة */
    .header-container { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
    
    .system-info h1 { 
        margin: 0; 
        font-size: 22px; 
        font-weight: bold; 
        color: #000 !important; /* Force Black */
        text-shadow: none !important;
    }
    .system-info p { 
        margin: 5px 0; 
        font-size: 14px; 
        color: #333 !important; 
        text-shadow: none !important;
    }
    
    .report-title { 
        border: 2px solid #000; 
        padding: 10px 30px; 
        border-radius: 8px; 
        align-self: center; 
        color: #000 !important;
        background: #fff !important;
    }
    .report-title h2 { 
        margin: 0; 
        font-size: 24px; 
        font-weight: bold; 
        color: #000 !important; 
        text-shadow: none !important;
    }

    .client-info { text-align: right; }
    .client-info p { margin: 4px 0; font-size: 15px; color: #000 !important; }
    .client-info strong { font-size: 18px; font-weight: bold; color: #000 !important; }

    hr { border: 1px solid #000; margin: 20px 0; }

    /* أزرار التحكم - لا تظهر في الطباعة */
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

    /* مربعات ملخص الحساب */
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
    table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
    
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
        /* ... rest of print styles ... */

    
    .ledger-wrapper {
        background: #fff;
        min-height: 100vh;
    }
    /* استعادة تنسيق الترويسة الأصلي */
    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid #000;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }

    .system-info { text-align: right; }
    .system-info h1 { margin: 0; font-size: 24px; font-weight: bold; color: #000 !important; }
    .system-info p { margin: 5px 0 0; font-size: 14px; color: #333 !important; }

    .report-title {
        text-align: center;
        border: 2px solid #000;
        padding: 10px 30px;
        border-radius: 10px;
    }
    .report-title h2 { margin: 0; font-size: 24px; font-weight: bold; color: #000 !important; }

    .client-info { text-align: left; }
    .client-info p { margin: 4px 0; font-size: 15px; color: #000 !important; }
    .client-info strong { font-size: 18px; font-weight: bold; color: #000 !important; }

    hr { border: 1px solid #000; margin: 20px 0; display: block; }

</style>
<div class="ledger-wrapper">

    <div class="actions-bar no-print">
        <button class="btn-print" onclick="window.print()">طباعة الكشف</button>
        <a href="index.php?page=transactions" class="btn-back">عودة للنظام</a>
    </div>

    <!-- استعادة هيكل الترويسة الأصلي -->
    <div class="header-container">
        <div class="system-info">
            <h1><?php echo \App\Models\Setting::get('company_name', 'الشركة'); ?></h1>
            <p>الإدارة المالية والحسابات</p>
        </div>
        
        <div class="report-title">
            <h2>كشف حساب عميل</h2>
        </div>

        <div class="client-info">
            <p>اسم العميل: <strong><?php echo $client['name']; ?></strong></p>
            <?php if (!empty($client['phone'])): ?>
            <p>رقم الهاتف: <?php echo toArabicNum($client['phone']); ?></p>
            <?php endif; ?>
            <p>التاريخ: <?php echo toArabicNum(date('Y/m/d')); ?></p>
        </div>
    </div>

    <hr>

    <hr>

    <div class="summary-boxes">
        <div class="box">
            <div class="box-title">إجمالي المدين (عليه)</div>
            <div class="box-value"><?php echo toArabicNum(number_format($summary['total_expense'], 2)); ?> ج.م</div>
        </div>
        <div class="box">
            <div class="box-title">إجمالي الدائن (له)</div>
            <div class="box-value"><?php echo toArabicNum(number_format($summary['total_income'], 2)); ?> ج.م</div>
        </div>
        <div class="box">
            <div class="box-title">إجمالي السلف</div>
            <div class="box-value"><?php echo toArabicNum(number_format($summary['total_advances'], 2)); ?> ج.م</div>
        </div>
        <div class="box">
            <div class="box-title">الرصيد الصافي</div>
            <div class="box-value">
                <?php echo toArabicNum(number_format(abs($summary['net_balance']), 2)); ?> ج.م 
                (<?php echo $summary['net_balance'] >= 0 ? 'عليه' : 'له'; ?>)
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 15%;">التاريخ</th>
                <th style="width: 35%;">البيان / التفاصيل</th>
                <th style="width: 10%;">النوع</th>
                <th style="width: 10%;">مدين</th>
                <th style="width: 10%;">دائن</th>
                <th style="width: 15%;">الرصيد</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($transactions)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">لا توجد معاملات مسجلة</td>
                </tr>
            <?php else: ?>
                <?php 
                $counter = 1;
                foreach ($transactions as $t): 
                    // تحديد نوع الحركة للعرض
                    /*
                    ['type' => 'وارد', 'm' => 0, 'd' => 5000, 'bal' => '5,000 له'],
                    */
                    $typeLabel = '';
                    if ($t['is_advance']) $typeLabel = 'سلفة';
                    elseif ($t['type'] == 'expense') $typeLabel = 'منصرف';
                    else $typeLabel = 'وارد';
                    
                    $debit = ($t['type'] == 'expense') ? number_format($t['amount'], 2) : '-';
                    $credit = ($t['type'] == 'income' || $t['is_advance']) ? number_format($t['amount'], 2) : '-'; 
                    // ملاحظة: في الجدول القديم السلفة تعتبر وارد للدائن؟ لا، السلفة تزيد الدين 
                    // حسب التصميم: مدين (عليه) | دائن (له)
                    // expense = عليه = مدين
                    // income = له = دائن
                    
                    $m_val = ($t['type'] == 'expense') ? $t['amount'] : 0;
                    $d_val = ($t['type'] == 'income') ? $t['amount'] : 0;
                    
                    // الرصيد
                    $balVal = abs($t['running_balance']);
                    $balStr = toArabicNum(number_format($balVal, 2));
                    $balSuffix = $t['running_balance'] >= 0 ? 'عليه' : 'له';
                ?>
                <tr>
                    <td><?php echo toArabicNum($counter++); ?></td>
                    <td><?php echo toArabicNum(date('Y/m/d', strtotime($t['date']))); ?></td>
                    <td style="text-align:right"><?php echo !empty($t['notes']) ? $t['notes'] : '-'; ?></td>
                    <td><?php echo $typeLabel; ?></td>
                    <td><?php echo ($m_val > 0) ? toArabicNum(number_format($m_val, 2)) : '-'; ?></td>
                    <td><?php echo ($d_val > 0) ? toArabicNum(number_format($d_val, 2)) : '-'; ?></td>
                    <td style="font-weight: bold;"><?php echo $balStr . ' ' . $balSuffix; ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4">الإجماليات</td>
                <td><?php echo toArabicNum(number_format($summary['total_expense'], 2)); ?></td>
                <td><?php echo toArabicNum(number_format($summary['total_income'], 2)); ?></td>
                <td>
                    <?php echo toArabicNum(number_format(abs($summary['net_balance']), 2)); ?>
                </td>
            </tr>
        </tfoot>
    </table>

</div>

<?php 
// إخفاء الـ Footer الأساسي للنظام
echo '<style>footer, .footer-container { display: none !important; }</style>';
?>
