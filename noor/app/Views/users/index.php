<!-- Users Page CSS -->
<link rel="stylesheet" href="<?php echo asset('css/users-page.css'); ?>">
<link rel="stylesheet" href="<?php echo asset('css/glass-modal.css'); ?>">

<div class="neon-hero-header">
    <div class="neon-title-group">
        <i class="fas fa-users-cog neon-title-icon"></i>
        <div class="neon-title-text">
            إدارة المستخدمين
            <small>إضافة وتعديل المستخدمين والصلاحيات</small>
        </div>
    </div>
    <?php if(\App\Core\Session::hasPermission('users.manage')): ?>
    <div class="neon-header-actions">
        <?php if (\App\Models\Setting::get('enable_bulk_delete', '0') == '1' && \App\Core\Session::hasPermission('users.manage') && \App\Core\Session::isManager()): ?>
        <button type="button" class="btn-neon-delete-bulk" id="bulkDeleteBtn" onclick="confirmBulkDelete()" style="display:none; background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #ef4444; margin-left: 10px;">
            <i class="fas fa-trash-alt"></i> <span>حذف المحدد (<span id="selectedCount">0</span>)</span>
        </button>
        <?php endif; ?>
        <button type="button" class="btn-neon-add" id="btnAddUser">
            <i class="fas fa-user-plus"></i> <span>إضافة مستخدم</span>
        </button>
    </div>
    <?php endif; ?>
</div>

<!-- Users Table -->
<div class="neon-surface">
    <table class="neon-table" id="usersTable">
        <thead>
            <tr>
                <?php if (\App\Models\Setting::get('enable_bulk_delete', '0') == '1' && \App\Core\Session::hasPermission('users.manage') && \App\Core\Session::isManager()): ?>
                <th style="width: 40px;"><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>
                <?php endif; ?>
                <th>#</th>
                <th>اسم المستخدم</th>
                <th>الدور</th>
                <th>الحالة</th>
                <th>آخر دخول</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <?php if (\App\Models\Setting::get('enable_bulk_delete', '0') == '1' && \App\Core\Session::hasPermission('users.manage') && \App\Core\Session::isManager()): ?>
                <td class="checkbox-cell" onclick="event.stopPropagation()">
                    <input type="checkbox" class="row-checkbox" value="<?php echo $user['id']; ?>" onchange="updateBulkSelect()">
                </td>
                <?php endif; ?>
                <td data-label="#"><?php echo $user['id']; ?></td>
                <td data-label="اسم المستخدم">
                    <div class="user-cell">
                        <i class="fas fa-user-circle user-avatar"></i>
                        <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                    </div>
                </td>
                <td data-label="الدور">
                    <?php
                    $roleClass = match($user['role']) {
                        'admin' => 'role-admin',
                        'manager' => 'role-manager',
                        default => 'role-user'
                    };
                    $roleLabel = match($user['role']) {
                        'admin' => 'مدير',
                        'manager' => 'مشرف',
                        default => 'مستخدم'
                    };
                    ?>
                    <span class="role-badge <?php echo $roleClass; ?>"><?php echo $roleLabel; ?></span>
                </td>
                <td data-label="الحالة">
                    <span class="status-badge <?php echo $user['status']; ?>">
                        <?php echo $user['status'] === 'active' ? 'نشط' : 'موقوف'; ?>
                    </span>
                </td>
                <td data-label="آخر دخول">
                    <?php echo $user['last_login'] ? date('Y/m/d H:i', strtotime($user['last_login'])) : 'لم يسجل دخول'; ?>
                </td>
                <td data-label="الإجراءات">
                    <?php if(\App\Core\Session::hasPermission('users.manage')): ?>
                        <button type="button" class="btn-action btn-edit" data-id="<?php echo $user['id']; ?>" title="تعديل"><i class="fas fa-edit"></i></button>
                        <?php if($user['id'] != \App\Core\Session::get('user_id')): ?>
                        <button type="button" class="btn-action btn-delete" data-id="<?php echo $user['id']; ?>" data-username="<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>" title="حذف"><i class="fas fa-trash"></i></button>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?>
                <tr id="emptyRow"><td colspan="100%" class="text-center" style="padding: 40px; color: rgba(255,255,255,0.3); font-size: 1.2rem;">لا يوجد مستخدمين مضافين حالياً</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- User Modal -->
<div id="userModal" class="user-modal-overlay">
    <div class="user-modal">
        <div class="user-modal-header">
            <div class="user-modal-title">
                <i class="fas fa-user-plus"></i>
                <span id="modalTitle">إضافة مستخدم جديد</span>
            </div>
            <button type="button" class="user-modal-close" id="btnCloseUserModal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="userForm">
            <?php echo \App\Core\Session::csrfField(); ?>
            <input type="hidden" name="id" id="userId" value="">
            <input type="hidden" name="role" id="roleValue" value="user">
            <input type="hidden" name="status" id="statusValue" value="active">
            
            <div class="user-modal-body">
                <div class="user-form-row-4">
                    <div class="user-form-group">
                        <label>اسم المستخدم <span class="required">*</span></label>
                        <input type="text" name="username" id="username" class="user-form-input" placeholder="أدخل اسم المستخدم" required>
                    </div>
                    <div class="user-form-group">
                        <label>كلمة المرور <span id="passHint" class="required">(مطلوبة)</span></label>
                        <input type="password" name="password" id="password" class="user-form-input" placeholder="أدخل كلمة المرور">
                    </div>
                    <div class="user-form-group">
                        <label>الدور</label>
                        <button type="button" class="picker-btn" id="rolePickerBtn">
                            <span id="selectedRoleText">👤 مستخدم</span>
                        </button>
                    </div>
                    <div class="user-form-group">
                        <label>الحالة</label>
                        <button type="button" class="picker-btn" id="statusPickerBtn">
                            <span id="selectedStatusText">✅ نشط</span>
                        </button>
                    </div>
                </div>
                
                <!-- Permissions Section -->
                <div class="permissions-section" id="permissionsSection">
                    <h4><i class="fas fa-key"></i> الصلاحيات</h4>
                    <div class="permissions-grid">
                        <?php foreach ($permissions as $category => $perms): ?>
                        <div class="permission-category">
                            <div class="category-header">
                                <label class="category-toggle">
                                    <input type="checkbox" class="cat-toggle" data-category="<?php echo $category; ?>">
                                    <span><?php echo $categoryLabels[$category] ?? $category; ?></span>
                                </label>
                            </div>
                            <div class="category-permissions">
                                <?php foreach ($perms as $perm): ?>
                                <label class="permission-item">
                                    <input type="checkbox" name="permissions[]" value="<?php echo $perm['id']; ?>" class="perm-check perm-<?php echo $category; ?>" data-name="<?php echo $perm['name']; ?>">
                                    <span><?php echo htmlspecialchars($perm['description']); ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="user-modal-footer">
                <button type="button" class="btn-user-cancel" id="btnCancelUserModal">إلغاء</button>
                <button type="submit" class="btn-user-save">
                    <i class="fas fa-save"></i>
                    <span>حفظ البيانات</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Role Picker Popup -->
<div id="rolePickerPopup" class="picker-popup-overlay">
    <div class="picker-popup">
        <div class="picker-popup-header">
            <h3><i class="fas fa-user-tag"></i> اختر دور المستخدم</h3>
        </div>
        <div class="picker-grid">
            <div class="picker-card" data-value="user" data-text="👤 مستخدم">
                <div class="picker-card-icon">👤</div>
                <div class="picker-card-name">مستخدم</div>
                <div class="picker-card-desc">صلاحيات محدودة</div>
            </div>
            <div class="picker-card" data-value="manager" data-text="👔 مشرف">
                <div class="picker-card-icon">👔</div>
                <div class="picker-card-name">مشرف</div>
                <div class="picker-card-desc">صلاحيات متوسطة</div>
            </div>
            <div class="picker-card" data-value="admin" data-text="👑 مدير">
                <div class="picker-card-icon">👑</div>
                <div class="picker-card-name">مدير</div>
                <div class="picker-card-desc">كل الصلاحيات</div>
            </div>
        </div>
        <button type="button" class="picker-popup-close" id="btnCloseRolePicker">
            <i class="fas fa-times"></i> إغلاق
        </button>
    </div>
</div>

<!-- Status Picker Popup -->
<div id="statusPickerPopup" class="picker-popup-overlay">
    <div class="picker-popup">
        <div class="picker-popup-header">
            <h3><i class="fas fa-toggle-on"></i> اختر حالة المستخدم</h3>
        </div>
        <div class="picker-grid picker-grid-2">
            <div class="picker-card status-active" data-value="active" data-text="✅ نشط">
                <div class="picker-card-icon">✅</div>
                <div class="picker-card-name">نشط</div>
                <div class="picker-card-desc">يمكنه تسجيل الدخول</div>
            </div>
            <div class="picker-card status-inactive" data-value="inactive" data-text="🚫 موقوف">
                <div class="picker-card-icon">🚫</div>
                <div class="picker-card-name">موقوف</div>
                <div class="picker-card-desc">لا يمكنه تسجيل الدخول</div>
            </div>
        </div>
        <button type="button" class="picker-popup-close" id="btnCloseStatusPicker">
            <i class="fas fa-times"></i> إغلاق
        </button>
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

<!-- Users Page JavaScript -->
<script>
    window.csrfToken = "<?php echo \App\Core\Session::generateCsrfToken(); ?>";
</script>
<script src="<?php echo asset('js/users-page.js'); ?>?v=<?php echo time(); ?>"></script>
