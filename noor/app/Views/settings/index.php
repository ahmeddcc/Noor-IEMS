<link rel="stylesheet" href="<?php echo asset('css/settings.css'); ?>?v=<?php echo time(); ?>">

<!-- Page Header -->
<div class="neon-settings-header">
    <h1><i class="fas fa-cogs"></i> الإعدادات المتقدمة</h1>
</div>

<div class="settings-container">
    <!-- Sidebar Navigation -->
    <nav class="settings-sidebar">
        <?php if(\App\Core\Session::hasPermission('settings.general')): ?>
        <a href="#company" class="sidebar-item active" data-section="company" onclick="showSection('company')">
            <i class="fas fa-building"></i>
            <span>المؤسسة</span>
        </a>
        <?php endif; ?>

        <?php if(\App\Core\Session::hasPermission('settings.categories')): ?>
        <a href="#categories" class="sidebar-item" data-section="categories" onclick="showSection('categories')">
            <i class="fas fa-list-ul"></i>
            <span>التصنيفات</span>
        </a>
        <?php endif; ?>

        <?php if(\App\Core\Session::hasPermission('settings.telegram')): ?>
        <a href="#telegram" class="sidebar-item" data-section="telegram" onclick="showSection('telegram')">
            <i class="fab fa-telegram"></i>
            <span>تيليجرام</span>
        </a>
        <?php endif; ?>

        <?php if(\App\Core\Session::hasPermission('settings.backup')): ?>
        <a href="#backup" class="sidebar-item" data-section="backup" onclick="showSection('backup')">
            <i class="fas fa-cloud"></i>
            <span>النسخ الاحتياطي</span>
        </a>
        <?php endif; ?>
    </nav>

    <!-- Main Content Area -->
    <div class="settings-content">
        
        <?php if(\App\Core\Session::hasPermission('settings.general')): ?>
        <div id="company" class="settings-section active">
            <div class="settings-card compact">
                <h2>إعدادات المؤسسة</h2>
                
                <form action="index.php?page=settings&action=save" method="POST">
                    <?php echo \App\Core\Session::csrfField(); ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>اسم المؤسسة</label>
                            <input type="text" name="company_name" value="<?php echo e($settings['company_name'] ?? ''); ?>" placeholder="اسم المؤسسة">
                        </div>
                        <div class="form-group">
                            <label>العنوان</label>
                            <input type="text" name="company_address" value="<?php echo e($settings['company_address'] ?? ''); ?>" placeholder="عنوان المؤسسة">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>الحد الأقصى لمحاولات الدخول</label>
                            <input type="number" name="max_login_attempts" value="<?php echo e($settings['max_login_attempts'] ?? 5); ?>" min="1" max="10">
                        </div>
                        <div class="form-group">
                            <label>مدة انتهاء الجلسة (بالدقائق)</label>
                            <input type="number" name="session_timeout" value="<?php echo e($settings['session_timeout'] ?? 30); ?>" min="5" max="120">
                        </div>
                    </div>

                    <!-- Security Settings -->
                    <div class="toggle-group" style="margin-top: 25px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
                        <h3>إعدادات الأمان</h3>
                        <div class="toggle-row">
                            <div class="toggle-item">
                                <span>تفعيل الحذف الجماعي</span>
                                <input type="hidden" name="enable_bulk_delete" id="enable_bulk_delete" value="<?php echo ($settings['enable_bulk_delete'] ?? '0'); ?>">
                                <div class="toggle-switch <?php echo ($settings['enable_bulk_delete'] ?? '0') == '1' ? 'active' : ''; ?>" onclick="toggleSwitch(this, 'enable_bulk_delete')"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-neon primary">
                            <i class="fas fa-save"></i> حفظ الإعدادات
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <?php if(\App\Core\Session::hasPermission('settings.categories')): ?>
        <!-- Section: Categories -->
        <div id="categories" class="settings-section">
            <div class="settings-card">
                <h2>إدارة التصنيفات</h2>
                
                <!-- إضافة تصنيف جديد -->
                <form action="index.php?page=settings&action=addCategory" method="POST" class="add-category-form">
                    <?php echo \App\Core\Session::csrfField(); ?>
                    <input type="text" name="category_name" placeholder="اكتب اسم التصنيف الجديد..." required>
                    <button type="submit" class="btn-neon success">
                        <i class="fas fa-plus"></i> إضافة تصنيف
                    </button>
                </form>

                <!-- شبكة بطاقات التصنيفات -->
                <div class="categories-grid" id="categoriesGrid">
                    <?php foreach ($categories as $cat): ?>
                    <?php $token = \App\Core\Session::generateCsrfToken(); ?>
                    <div class="category-card <?php echo $cat['is_active'] ? 'active' : 'disabled'; ?>" 
                         data-id="<?php echo $cat['id']; ?>" draggable="true">
                        <div class="drag-handle" title="اسحب لإعادة الترتيب">
                            <i class="fas fa-grip-vertical"></i>
                        </div>
                        <div class="category-icon">
                            <i class="fas fa-tag"></i>
                        </div>
                        <div class="category-name"><?php echo e($cat['name']); ?></div>
                        <div class="category-status">
                            <?php if ($cat['is_mandatory']): ?>
                                <span class="badge-protected"><i class="fas fa-shield-alt"></i> محمي</span>
                            <?php else: ?>
                                <span class="badge-<?php echo $cat['is_active'] ? 'active' : 'disabled'; ?>">
                                    <?php echo $cat['is_active'] ? 'نشط' : 'معطل'; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="category-actions-group">
                            <?php if (!$cat['is_mandatory']): ?>
                                <!-- زر التعديل -->
                                <button type="button" class="btn-card-action edit" 
                                        onclick="openEditModal(<?php echo $cat['id']; ?>, '<?php echo e($cat['name']); ?>')" 
                                        title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <!-- زر التفعيل/التعطيل -->
                                <?php if ($cat['is_active']): ?>
                                    <a href="index.php?page=settings&action=toggleCategory&id=<?php echo $cat['id']; ?>&status=0&token=<?php echo $token; ?>" 
                                       class="btn-card-action warning" title="تعطيل">
                                        <i class="fas fa-pause"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="index.php?page=settings&action=toggleCategory&id=<?php echo $cat['id']; ?>&status=1&token=<?php echo $token; ?>" 
                                       class="btn-card-action success" title="تفعيل">
                                        <i class="fas fa-play"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <!-- زر الحذف -->
                                <a href="#" onclick="confirmCategoryDelete(event, <?php echo $cat['id']; ?>, '<?php echo e($cat['name']); ?>', '<?php echo $token; ?>')" 
                                   class="btn-card-action danger" title="حذف">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if(\App\Core\Session::hasPermission('settings.telegram')): ?>
        <!-- Section: Telegram -->
        <div id="telegram" class="settings-section">
            <div class="settings-card">
                <h2>ربط تيليجرام</h2>
                
                <!-- Connection Status -->
                <div class="status-row">
                    <span>حالة الاتصال</span>
                    <?php if (!empty($settings['telegram_bot_token']) && !empty($settings['telegram_chat_id'])): ?>
                        <span class="status-badge-connected"><i class="fas fa-wifi"></i> متصل</span>
                    <?php else: ?>
                        <span class="status-badge-disconnected">غير متصل</span>
                    <?php endif; ?>
                </div>
                
                <form action="index.php?page=settings&action=saveTelegram" method="POST">
                    <?php echo \App\Core\Session::csrfField(); ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>توكن البوت</label>
                            <input type="text" name="telegram_bot_token" 
                                   value="<?php echo e($settings['telegram_bot_token'] ?? ''); ?>"
                                   placeholder="أدخل توكن البوت">
                        </div>
                        <div class="form-group">
                            <label>معرف المحادثة</label>
                            <input type="text" name="telegram_chat_id" 
                                   value="<?php echo e($settings['telegram_chat_id'] ?? ''); ?>"
                                   placeholder="أدخل معرف المحادثة">
                        </div>
                    </div>
                    
                    <!-- Toggle Switches -->
                    <div class="toggle-group">
                        <h3>الإشعارات</h3>
                        <div class="toggle-row">
                            <div class="toggle-item">
                                <span>إشعارات تسجيل الدخول</span>
                                <input type="hidden" name="telegram_notify_login" id="notify_login" value="<?php echo ($settings['telegram_notify_login'] ?? '1'); ?>">
                                <div class="toggle-switch <?php echo ($settings['telegram_notify_login'] ?? '1') == '1' ? 'active' : ''; ?>" onclick="toggleSwitch(this, 'notify_login')"></div>
                            </div>
                            <div class="toggle-item">
                                <span>إشعارات الأخطاء التلقائية</span>
                                <input type="hidden" name="telegram_notify_errors" id="notify_errors" value="<?php echo ($settings['telegram_notify_errors'] ?? '1'); ?>">
                                <div class="toggle-switch <?php echo ($settings['telegram_notify_errors'] ?? '1') == '1' ? 'active' : ''; ?>" onclick="toggleSwitch(this, 'notify_errors')"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-neon primary">
                            <i class="fas fa-save"></i> حفظ الإعدادات
                        </button>
                        <button type="button" class="btn-neon secondary" onclick="testTelegram(this)">
                            <i class="fas fa-paper-plane"></i> اختبار الاتصال
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <?php if(\App\Core\Session::hasPermission('settings.backup')): ?>
        <!-- Section: Backup -->
        <div id="backup" class="settings-section">
            <div class="settings-card">
                <h2>إدارة النسخ الاحتياطي</h2>
                
                <div class="backup-info">
                    <p>
                        <i class="fas fa-info-circle"></i>
                        يتم حفظ النسخ الاحتياطية في مجلد <code>/backups</code> بصيغة SQLite.
                        يُنصح بإنشاء نسخة احتياطية بشكل دوري للحفاظ على بياناتك.
                    </p>
                </div>

                <!-- أدوات إدارة النسخ -->
                <div class="backup-actions-row" style="display: flex; gap: 15px; margin-bottom: 25px; flex-wrap: wrap;">
                    <!-- زر إنشاء نسخة -->
                    <a href="index.php?page=settings&action=backup" class="btn-neon primary" style="flex: 1; text-align: center;">
                        <i class="fas fa-plus-circle"></i> إنشاء نسخة احتياطية جديدة
                    </a>
                    
                    <!-- فورم رفع نسخة -->
                    <form action="index.php?page=settings&action=uploadBackup" method="POST" enctype="multipart/form-data" style="flex: 1; display: flex; gap: 10px;">
                        <?php echo \App\Core\Session::csrfField(); ?>
                        <input type="file" name="backup_file" accept=".sqlite" required style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 10px; border-radius: 8px; flex: 1;">
                        <button type="submit" class="btn-neon secondary">
                            <i class="fas fa-upload"></i> رفع واستعادة
                        </button>
                    </form>
                </div>
                
                <!-- جدول النسخ الاحتياطية -->
                <div class="table-container" style="overflow-x: auto;">
                    <table class="neon-data-table">
                        <thead>
                            <tr>
                                <th>اسم الملف</th>
                                <th>تاريخ الإنشاء</th>
                                <th>الحجم</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($backups)): ?>
                                <?php foreach ($backups as $backup): ?>
                                <tr>
                                    <td style="direction: ltr; text-align: right;"><?php echo $backup['name']; ?></td>
                                    <td><?php echo $backup['date']; ?></td>
                                    <td><?php echo $backup['size']; ?></td>
                                    <td>
                                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                            <!-- تحميل -->
                                            <a href="index.php?page=settings&action=downloadBackup&file=<?php echo $backup['name']; ?>" class="btn-sm btn-info" title="تحميل">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            
                                            <!-- استعادة -->
                                            <?php $token = \App\Core\Session::generateCsrfToken(); ?>
                                            <a href="#" onclick="confirmAction(event, 'index.php?page=settings&action=restoreBackup&file=<?php echo $backup['name']; ?>&token=<?php echo $token; ?>', 'restore')" class="btn-sm btn-warning" title="استعادة">
                                                <i class="fas fa-history"></i>
                                            </a>
                                            
                                            <!-- حذف -->
                                            <a href="#" onclick="confirmAction(event, 'index.php?page=settings&action=deleteBackup&file=<?php echo $backup['name']; ?>&token=<?php echo $token; ?>', 'delete')" class="btn-sm" style="background: rgba(244, 63, 94, 0.1); color: #f43f5e; border-color: rgba(244, 63, 94, 0.3);" title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 30px; color: rgba(255,255,255,0.5);">
                                        لا توجد نسخ احتياطية محفوظة حالياً
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
        <?php endif; ?>
        
    </div>
</div>

<!-- Custom Confirmation Modal -->
<div id="customModal" class="custom-modal">
    <div class="modal-content">
        <div class="modal-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3 class="modal-title" id="modalTitle">تأكيد الإجراء</h3>
        <p class="modal-message" id="modalMessage">هل أنت متأكد من إتمام هذا الإجراء؟</p>
        <div class="modal-actions">
            <button id="modalConfirmBtn" class="btn-modal confirm">نعم، متأكد</button>
            <button onclick="closeModal()" class="btn-modal cancel">إلغاء</button>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editCategoryModal" class="custom-modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-icon" style="color: var(--neon-cyan);">
            <i class="fas fa-edit"></i>
        </div>
        <h3 class="modal-title">تعديل التصنيف</h3>
        <form action="index.php?page=settings&action=editCategory" method="POST" style="margin-top: 20px;">
            <?php echo \App\Core\Session::csrfField(); ?>
            <input type="hidden" name="category_id" id="editCategoryId">
            <div class="form-group" style="margin-bottom: 20px;">
                <input type="text" name="category_name" id="editCategoryName" 
                       placeholder="اسم التصنيف" required
                       style="width: 100%; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); 
                              color: #fff; padding: 12px 16px; border-radius: 10px; font-size: 1rem; text-align: center;">
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn-modal confirm" style="background: linear-gradient(135deg, var(--neon-cyan), #0ea5e9);">
                    <i class="fas fa-save"></i> حفظ التعديلات
                </button>
                <button type="button" onclick="closeEditModal()" class="btn-modal cancel">إلغاء</button>
            </div>
        </form>
    </div>
</div>

<script>
// Custom Modal Logic
let currentRedirectUrl = '';

function confirmAction(event, url, type) {
    event.preventDefault();
    currentRedirectUrl = url;
    
    const modal = document.getElementById('customModal');
    const title = document.getElementById('modalTitle');
    const message = document.getElementById('modalMessage');
    const icon = document.querySelector('.modal-icon i');
    const confirmBtn = document.getElementById('modalConfirmBtn');
    
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('show'), 10);
    
    if (type === 'delete') {
        title.innerText = 'حذف النسخة الاحتياطية';
        message.innerText = 'هل أنت متأكد من رغبتك في حذف ملف النسخة الاحتياطية هذا؟ لا يمكن التراجع عن هذا الإجراء.';
        icon.className = 'fas fa-trash-alt';
        confirmBtn.className = 'btn-modal confirm';
        confirmBtn.style.background = 'linear-gradient(135deg, #f43f5e, #e11d48)';
        confirmBtn.innerText = 'نعم، احذف';
    } else if (type === 'restore') {
        title.innerText = 'استعادة النظام';
        message.innerText = 'تحذير: سيتم استبدال قاعدة البيانات الحالية تماماً بهذه النسخة. سيتم فقدان أي بيانات تم إدخالها بعد إنشاء هذه النسخة. هل تريد المتابعة؟';
        icon.className = 'fas fa-history';
        confirmBtn.className = 'btn-modal confirm';
        confirmBtn.style.background = 'linear-gradient(135deg, #f59e0b, #d97706)';
        confirmBtn.innerText = 'نعم، استعد';
    }
}

function closeModal() {
    const modal = document.getElementById('customModal');
    modal.classList.remove('show');
    setTimeout(() => modal.style.display = 'none', 300);
}

// Edit Category Modal Functions
function openEditModal(id, name) {
    document.getElementById('editCategoryId').value = id;
    document.getElementById('editCategoryName').value = name;
    
    const modal = document.getElementById('editCategoryModal');
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('show'), 10);
    
    // Focus on the input
    setTimeout(() => document.getElementById('editCategoryName').focus(), 300);
}

function closeEditModal() {
    const modal = document.getElementById('editCategoryModal');
    modal.classList.remove('show');
    setTimeout(() => modal.style.display = 'none', 300);
}

// Delete Category Confirmation
function confirmCategoryDelete(event, id, name, token) {
    event.preventDefault();
    currentRedirectUrl = `index.php?page=settings&action=deleteCategory&id=${id}&token=${token}`;
    
    const modal = document.getElementById('customModal');
    const title = document.getElementById('modalTitle');
    const message = document.getElementById('modalMessage');
    const icon = document.querySelector('#customModal .modal-icon i');
    const confirmBtn = document.getElementById('modalConfirmBtn');
    
    title.innerText = 'حذف التصنيف';
    message.innerText = `هل أنت متأكد من حذف التصنيف "${name}"؟ لا يمكن التراجع عن هذا الإجراء.`;
    icon.className = 'fas fa-trash-alt';
    confirmBtn.style.background = 'linear-gradient(135deg, #f43f5e, #e11d48)';
    confirmBtn.innerText = 'نعم، احذف';
    
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('show'), 10);
}

// Close modals when clicking outside
document.getElementById('editCategoryModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});

document.getElementById('modalConfirmBtn').addEventListener('click', function() {
    if (currentRedirectUrl) {
        window.location.href = currentRedirectUrl;
    }
});

// Close modal when clicking outside
document.getElementById('customModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Navigation between sections
function showSection(sectionId) {
    // إيقاف السلوك الافتراضي للروابط (فقط إذا كان الحدث موجوداً)
    if (typeof event !== 'undefined' && event) {
        event.preventDefault();
    }

    document.querySelectorAll('.settings-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.sidebar-item').forEach(i => i.classList.remove('active'));
    
    document.getElementById(sectionId).classList.add('active');
    document.querySelector(`[data-section="${sectionId}"]`).classList.add('active');
    
    // تحديث الرابط دون القفز لأعلى الصفحة
    history.replaceState(null, null, `#${sectionId}`);
}

// Handle URL hash or Query Param on load
document.addEventListener('DOMContentLoaded', function() {
    // Check for query param 'tab' first (Preferred for no-scroll)
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    
    // Disable scroll restoration to prevent browser jumping
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }

    if (tab && document.getElementById(tab)) {
        showSection(tab);
        setTimeout(() => {
            window.scrollTo({ top: 0, behavior: 'instant' });
        }, 10);
    } else {
        // Fallback to hash (Legacy)
        const hash = window.location.hash.replace('#', '');
        if (hash && document.getElementById(hash)) {
            showSection(hash);
            // منع القفز التلقائي للعنصر - إعادة التمرير لأعلى
            setTimeout(() => {
                window.scrollTo({ top: 0, behavior: 'instant' });
            }, 10);
        } else {
            // منطق جديد: إذا لم يكن هناك تبويب محدد، نختار أول واحد متاح في القائمة الجانبية
            const firstAvailable = document.querySelector('.sidebar-item');
            if (firstAvailable) {
                const sectionId = firstAvailable.getAttribute('data-section');
                showSection(sectionId);
                // تأكيد التمرير للأعلى عند التحميل الافتراضي أيضاً
                setTimeout(() => {
                    window.scrollTo({ top: 0, behavior: 'instant' });
                }, 10);
            }
        }
    }
});

// Telegram Test
function testTelegram(btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الاختبار...';
    
    fetch('index.php?page=settings&action=testTelegram')
        .then(response => response.json())
        .then(data => {
            showToast(data.success ? 'success' : 'error', data.message);
        })
        .catch(() => {
            showToast('error', 'فشل الاتصال');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> اختبار الاتصال';
        });
}

// Toast Notification
function showToast(type, message) {
    const existing = document.querySelector('.toast-notification');
    if (existing) existing.remove();
    
    const toast = document.createElement('div');
    toast.className = 'toast-notification ' + type;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'times-circle'}"></i>
        <span>${message}</span>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Toggle Switch Handler
function toggleSwitch(element, inputId) {
    element.classList.toggle('active');
    const input = document.getElementById(inputId);
    input.value = element.classList.contains('active') ? '1' : '0';
}

// Convert PHP Alerts to Toasts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        // Skip if empty
        if (!alert.innerText.trim()) return;
        
        const message = alert.innerText;
        const type = alert.classList.contains('alert-success') ? 'success' : 'error';
        
        // Show as Toast
        showToast(type, message);
        
        // Hide original
        alert.style.display = 'none';
    });
});

// ========================
// Drag and Drop Categories
// ========================
let draggedElement = null;
let draggedOverElement = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeDragDrop();
});

function initializeDragDrop() {
    const grid = document.getElementById('categoriesGrid');
    if (!grid) return;
    
    const cards = grid.querySelectorAll('.category-card');
    
    cards.forEach(card => {
        // Drag Start
        card.addEventListener('dragstart', function(e) {
            draggedElement = this;
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', this.innerHTML);
            
            // Hide after a small delay for better visual
            setTimeout(() => {
                this.style.opacity = '0.4';
            }, 0);
        });
        
        // Drag End
        card.addEventListener('dragend', function(e) {
            this.classList.remove('dragging');
            this.style.opacity = '1';
            
            // Remove all drag-over classes
            document.querySelectorAll('.category-card').forEach(c => {
                c.classList.remove('drag-over');
            });
            
            draggedElement = null;
            draggedOverElement = null;
        });
        
        // Drag Over
        card.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            
            if (this !== draggedElement) {
                this.classList.add('drag-over');
                draggedOverElement = this;
            }
        });
        
        // Drag Leave
        card.addEventListener('dragleave', function(e) {
            this.classList.remove('drag-over');
        });
        
        // Drop
        card.addEventListener('drop', function(e) {
            e.preventDefault();
            
            if (draggedElement && this !== draggedElement) {
                // Get all cards
                const allCards = Array.from(grid.querySelectorAll('.category-card'));
                const draggedIndex = allCards.indexOf(draggedElement);
                const dropIndex = allCards.indexOf(this);
                
                // Swap positions in DOM
                if (draggedIndex < dropIndex) {
                    this.parentNode.insertBefore(draggedElement, this.nextSibling);
                } else {
                    this.parentNode.insertBefore(draggedElement, this);
                }
                
                // Save new order
                saveNewOrder();
            }
            
            this.classList.remove('drag-over');
        });
    });
}

function saveNewOrder() {
    const grid = document.getElementById('categoriesGrid');
    const cards = grid.querySelectorAll('.category-card');
    const order = Array.from(cards).map(card => parseInt(card.dataset.id));
    
    // Show saving indicator
    showToast('success', 'جاري حفظ الترتيب...');
    
    fetch('index.php?page=settings&action=reorderCategories', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ order: order })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'تم حفظ الترتيب الجديد');
        } else {
            showToast('error', data.message || 'فشل حفظ الترتيب');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'حدث خطأ أثناء الحفظ');
    });
}
</script>
