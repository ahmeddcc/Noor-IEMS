<!-- Clients Page CSS -->
<link rel="stylesheet" href="<?php echo asset('css/clients-page.css'); ?>?v=FIX_ZINDEX_<?php echo time(); ?>">
<link rel="stylesheet" href="<?php echo asset('css/glass-modal.css'); ?>?v=FIX_ZINDEX_<?php echo time(); ?>">

<div class="neon-hero-header" style="border: 1px solid #3b82f6 !important;">
    <div class="neon-title-group">
        <i class="fas fa-users neon-title-icon" style="color: #a855f7; filter: drop-shadow(0 0 10px #a855f7);"></i>
        <div class="neon-title-text">
            إدارة العملاء
            <small>إدارة ومتابعة بيانات العملاء</small>
        </div>
    </div>
    <div class="neon-header-actions">
        <?php if (\App\Models\Setting::get('enable_bulk_delete', '0') == '1' && \App\Core\Session::hasPermission('clients.delete') && \App\Core\Session::isManager()): ?>
        <button type="button" class="btn-neon-delete-bulk" id="bulkDeleteBtn" onclick="confirmBulkDelete()" style="display:none;">
            <i class="fas fa-trash-alt"></i> <span>حذف المحدد (<span id="selectedCount">0</span>)</span>
        </button>
        <?php endif; ?>
        <button type="button" class="btn-neon-add" onclick="openClientModal()">
            <i class="fas fa-plus-circle"></i> <span>إضافة عميل جديد</span>
        </button>
    </div>
</div>

<div class="neon-surface">
    <table class="neon-table">
        <thead>
            <tr>
                <?php if (\App\Models\Setting::get('enable_bulk_delete', '0') == '1' && \App\Core\Session::hasPermission('clients.delete') && \App\Core\Session::isManager()): ?>
                <th style="width: 40px;"><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>
                <?php endif; ?>
                <th>الاسم</th>
                <th>التصنيف</th>
                <th>رقم الهاتف</th>
                <th>العنوان</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody id="clientsTableBody">
            <?php foreach ($clients as $client): ?>
            <tr data-id="<?php echo $client['id']; ?>">
                <?php if (\App\Models\Setting::get('enable_bulk_delete', '0') == '1' && \App\Core\Session::hasPermission('clients.delete') && \App\Core\Session::isManager()): ?>
                <td class="checkbox-cell" onclick="event.stopPropagation()">
                    <input type="checkbox" class="row-checkbox" value="<?php echo $client['id']; ?>" onchange="updateBulkSelect()">
                </td>
                <?php endif; ?>
                <td data-label="الاسم"><strong><?php echo e($client['name']); ?></strong></td>
                <td data-label="التصنيف">
                    <?php 
                        echo e($client['category_name']); 
                        if ($client['category_custom']) {
                            echo ' <span style="font-size:0.8rem; opacity:0.7; color: var(--neon-cyan);">(' . e($client['category_custom']) . ')</span>';
                        }
                    ?>
                </td>
                <td data-label="رقم الهاتف" style="font-family: 'Cairo';"><?php echo toArabicNum($client['phone']); ?></td>
                <td data-label="العنوان"><?php echo e($client['address']); ?></td>
                <td data-label="الحالة">
                    <span class="status-badge <?php echo $client['status']; ?>">
                        <?php echo $client['status'] == 'active' ? 'نشط' : 'موقوف'; ?>
                    </span>
                </td>
                <td data-label="الإجراءات">
                    <a href="index.php?page=transactions&action=ledger&client_id=<?php echo $client['id']; ?>" class="btn-action btn-view" title="كشف حساب"><i class="fas fa-file-invoice-dollar"></i></a>
                    <button type="button" class="btn-action btn-edit" onclick="editClient(<?php echo $client['id']; ?>)" title="تعديل"><i class="fas fa-edit"></i></button>
                    <button type="button" class="btn-action btn-delete" onclick="deleteClient(<?php echo $client['id']; ?>)" title="حذف"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($clients)): ?>
                <tr id="emptyRow"><td colspan="100%" class="text-center" style="padding: 40px; color: rgba(255,255,255,0.3); font-size: 1.2rem;">لا يوجد عملاء مضافين حالياً</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
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

<!-- System Alert Modal (For Errors/Success) -->
<div id="systemAlertModal" class="glass-modal-overlay" style="z-index: 1000001;"> <!-- Boosted Z-Index -->
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

<!-- Client Modal (Smart Glass Design) -->
<div id="clientModal" class="glass-modal-overlay" style="z-index: 5000;">
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

            <button type="button" class="glass-modal-close" onclick="closeClientModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="clientForm" onsubmit="return false;">
            <input type="hidden" name="client_id" id="clientId" value="">
            <input type="hidden" name="csrf_token" value="<?php echo \App\Core\Session::generateCsrfToken(); ?>"> <!-- Added CSRF Token -->
            
            <div class="glass-modal-body">
                <div class="form-group-neon">
                    <label>اسم العميل <span class="required" style="color: #a855f7">*</span></label>
                    <input type="text" name="name" id="clientName" class="glass-input" placeholder="اكتب اسم العميل كاملاً" required>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                    <div class="form-group-neon">
                        <label>التصنيف <span class="required" style="color: #a855f7">*</span></label>
                        <input type="hidden" name="category_id" id="clientCategory" required>
                        <button type="button" class="glass-input" id="categoryPickerBtn" onclick="openCategoryPopup()" style="text-align: right; display: flex; justify-content: space-between; align-items: center; color: rgba(255,255,255,0.8); cursor: pointer;">
                            <span id="selectedCategoryText">اختر التصنيف...</span>
                        </button>
                    </div>
                    <div class="form-group-neon">
                        <label>رقم الهاتف</label>
                        <input type="tel" name="phone" id="clientPhone" class="glass-input" placeholder="01xxxxxxxxx">
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
                
                <div class="form-group-neon" id="statusGroup" style="display: none; margin-top: 15px;">
                    <label>حالة العميل</label>
                    <select name="status" id="clientStatus" class="glass-input">
                        <option value="active">نشط</option>
                        <option value="suspended">موقوف</option>
                    </select>
                </div>
            </div>
            
            <div class="glass-modal-footer">
                <button type="button" class="glass-btn cancel" onclick="closeClientModal()">
                    <i class="fas fa-times"></i> إلغاء
                </button>
                <button type="button" class="glass-btn confirm" id="saveBtn" onclick="saveClient(event)">
                    <i class="fas fa-save"></i> <span>حفظ البيانات</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Category Picker Popup -->
<div id="categoryPopup" class="category-popup-overlay">
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
            <div class="category-card" data-id="<?php echo $cat['id']; ?>" data-name="<?php echo e($cat['name']); ?>" data-mandatory="<?php echo $cat['is_mandatory']; ?>" onclick="selectCategory(<?php echo $cat['id']; ?>, '<?php echo e($cat['name']); ?>', <?php echo $cat['is_mandatory']; ?>)">
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
    window.csrfToken = "<?php echo \App\Core\Session::generateCsrfToken(); ?>";
</script>

<!-- Clients Page JavaScript -->
<script src="<?php echo asset('js/clients-page.js'); ?>?v=GLASS_MODAL_FIX_<?php echo time(); ?>"></script>