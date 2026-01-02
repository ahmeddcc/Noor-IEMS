<?php if (empty($transactions)): ?>
    <tr>
        <td colspan="7" class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-inbox"></i>
            </div>
            <p>لا توجد معاملات مسجلة</p>
        </td>
    </tr>
<?php else: ?>
    <?php
    $paymentData = [
        'cash' => ['icon' => '💵', 'label' => 'نقدي'],
        'transfer' => ['icon' => '🏦', 'label' => 'تحويل'],
        'bank' => ['icon' => '🏦', 'label' => 'بنكي'],
        'other' => ['icon' => '📝', 'label' => 'أخرى']
    ];
    ?>
    <?php foreach ($transactions as $t): ?>
        <?php
        $typeClass = $t['is_advance'] ? 'advance' : $t['type'];
        $typeIcon = $t['is_advance'] ? 'fas fa-hand-holding-usd' : ($t['type'] == 'income' ? 'fas fa-arrow-down' : 'fas fa-arrow-up');
        $typeName = $t['is_advance'] ? 'سلفة' : ($t['type'] == 'income' ? 'وارد' : 'منصرف');
        $payment = $paymentData[$t['payment_method']] ?? $paymentData['other'];
        $amountSign = $t['type'] == 'income' ? '+' : '-';
        $clientName = htmlspecialchars($t['client_name'] ?? 'غير محدد', ENT_QUOTES, 'UTF-8');
        $notes = htmlspecialchars($t['notes'] ?? '', ENT_QUOTES, 'UTF-8') ?: '-';
        ?>
        <tr data-id="<?php echo $t['id']; ?>" class="table-row-hover">
            <?php if (\App\Models\Setting::get('enable_bulk_delete', '0') == '1' && \App\Core\Session::hasPermission('transactions.delete') && \App\Core\Session::isManager()): ?>
            <td class="checkbox-cell" onclick="event.stopPropagation()">
                <input type="checkbox" class="row-checkbox" value="<?php echo $t['id']; ?>" onchange="updateBulkSelect()">
            </td>
            <?php endif; ?>
            <td class="date-cell">
                <span class="date-badge"><?php echo date('d/m/Y', strtotime($t['date'])); ?></span>
            </td>
            <td class="client-cell">
                <a href="index.php?page=transactions&action=ledger&client_id=<?php echo $t['client_id']; ?>" class="client-link-neon">
                    <?php echo $clientName; ?>
                </a>
            </td>
            <td>
                <span class="type-badge <?php echo $typeClass; ?>">
                    <i class="<?php echo $typeIcon; ?>"></i> <?php echo $typeName; ?>
                </span>
            </td>
            <td class="amount-cell <?php echo $t['type']; ?>">
                <span class="amount-value"><?php echo $amountSign . number_format($t['amount'], 0); ?></span> 
                <small>ج.م</small>
            </td>
            <td class="payment-cell">
                <span class="payment-badge"><?php echo $payment['icon'] . ' ' . $payment['label']; ?></span>
            </td>
            <td class="notes-cell">
                <span><?php echo $notes; ?></span>
            </td>
            <td class="actions-cell-neon">
                <button type="button" class="action-btn-neon edit" onclick="editTransaction(<?php echo $t['id']; ?>)" title="تعديل">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="action-btn-neon delete" onclick="deleteTransaction(<?php echo $t['id']; ?>)" title="حذف">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>
