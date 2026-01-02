<div class="page-header">
    <h1><?php 
        if ($type == 'income') echo 'تسجيل وارد (دفع)'; 
        elseif ($type == 'expense') echo 'تسجيل منصرف';
        elseif ($type == 'advance') echo 'تسجيل سلفة نقدية';
    ?></h1>
    <a href="index.php?page=dashboard" class="btn-secondary"><i class="fas fa-times"></i> إلغاء</a>
</div>

<div class="card form-container">
    <form action="" method="POST">
        <?php echo \App\Core\Session::csrfField(); ?>
        
        <div class="form-grid">
            <div class="form-group">
                <label for="date">التاريخ <span class="required">*</span></label>
                <input type="date" name="date" id="date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group">
                <label for="amount">المبلغ (ج.م) <span class="required">*</span></label>
                <input type="number" step="0.01" name="amount" id="amount" required autofocus>
            </div>

            <div class="form-group">
                <label for="client_id">العميل <span class="required">*</span></label>
                <select name="client_id" id="client_id" required class="select-search">
                    <option value="">اختر العميل...</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?php echo $client['id']; ?>">
                            <?php echo e($client['name']); ?> 
                            (<?php echo e($client['category_name']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($clients)): ?>
                    <small style="color:red">لا يوجد عملاء. <a href="index.php?page=clients&action=add">أضف عميل أولاً</a></small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="payment_method">طريقة الدفع</label>
                <select name="payment_method" id="payment_method">
                    <option value="cash">نقدي (Cash)</option>
                    <option value="transfer">تحويل (Transfer)</option>
                    <option value="other">أخرى</option>
                </select>
            </div>

            <div class="form-group full-width">
                <label for="notes">ملاحظات / بيان</label>
                <textarea name="notes" id="notes" rows="3"></textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary <?php echo $type == 'income' ? 'btn-success' : 'btn-danger'; ?>">
                <i class="fas fa-check"></i> حفظ العملية
            </button>
        </div>
    </form>
</div>

<style>
.full-width { grid-column: 1 / -1; }
.btn-success { background-color: var(--success-color); }
</style>
