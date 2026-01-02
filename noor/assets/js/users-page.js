// Users Page JavaScript - Clean Robust Version
console.log('Users Page JS: Initializing Robust Handler');

// Global state for confirm
let sysConfirmCallback = null;

// ===== System Modal Helpers =====
function showSystemConfirm(title, message, callback, isDanger = false) {
    const modal = document.getElementById('systemConfirmModal');
    const titleEl = document.getElementById('sysModalTitle');
    const bodyEl = document.getElementById('sysModalBody');
    const iconEl = document.getElementById('sysModalIcon');
    const btn = document.getElementById('sysModalActionBtn');

    if (!modal) return false;

    titleEl.textContent = title;
    bodyEl.innerHTML = message;
    sysConfirmCallback = callback;

    if (isDanger) {
        iconEl.classList.add('danger');
        btn.classList.add('danger');
        btn.innerHTML = '<i class="fas fa-trash-alt"></i> نعم، حذف';
    } else {
        iconEl.classList.remove('danger');
        btn.classList.remove('danger');
        btn.innerHTML = '<i class="fas fa-check"></i> نعم، تنفيذ';
    }

    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('show'), 10);

    btn.onclick = function () {
        if (sysConfirmCallback) sysConfirmCallback();
        closeSystemConfirm();
    };
}

function closeSystemConfirm() {
    const modal = document.getElementById('systemConfirmModal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => modal.style.display = 'none', 300);
    }
}

function showSystemAlert(type, message) {
    const modal = document.getElementById('systemAlertModal');
    const bodyEl = document.getElementById('sysAlertBody');
    const titleEl = document.getElementById('sysAlertTitle');
    const iconEl = document.getElementById('sysAlertIcon');

    if (!modal) { alert(message); return; }

    bodyEl.textContent = message;

    if (type === 'success') {
        titleEl.textContent = 'تم بنجاح';
        titleEl.style.color = '#2ecc71';
        iconEl.innerHTML = '<i class="fas fa-check-circle"></i>';
        iconEl.style.color = '#2ecc71';
        iconEl.style.borderColor = 'rgba(46, 204, 113, 0.3)';
        iconEl.style.background = 'rgba(46, 204, 113, 0.1)';
    } else if (type === 'error') {
        titleEl.textContent = 'خطأ';
        titleEl.style.color = '#ef4444';
        iconEl.innerHTML = '<i class="fas fa-times-circle"></i>';
        iconEl.style.color = '#ef4444';
        iconEl.style.borderColor = 'rgba(239, 68, 68, 0.3)';
        iconEl.style.background = 'rgba(239, 68, 68, 0.1)';
    } else {
        titleEl.textContent = 'تنبيه';
        titleEl.style.color = '#00d4ff';
        iconEl.innerHTML = '<i class="fas fa-info-circle"></i>';
    }

    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('show'), 10);
}

function closeSystemAlert() {
    const modal = document.getElementById('systemAlertModal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => modal.style.display = 'none', 300);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    initUsersPage();
});

function initUsersPage() {
    // Standard Elements
    const usersTable = document.getElementById('usersTable');
    const userForm = document.getElementById('userForm');
    const btnAddUser = document.getElementById('btnAddUser');

    // --- 1. Global Click Handler (Event Delegation) ---
    document.addEventListener('click', function (e) {
        // Edit Button
        const editBtn = e.target.closest('.btn-edit');
        if (editBtn) {
            const id = editBtn.dataset.id;
            handleEditUser(id);
            return;
        }

        // Delete Button
        const deleteBtn = e.target.closest('.btn-delete');
        if (deleteBtn) {
            const id = deleteBtn.dataset.id;
            const username = deleteBtn.dataset.username;
            handleDeleteUser(id, username);
            return;
        }

        // Add User Button
        if (e.target.closest('#btnAddUser')) {
            resetUserForm();
            openModal('userModal');
            return;
        }

        // Close Buttons
        if (e.target.closest('#btnCloseUserModal') || e.target.closest('#btnCancelUserModal')) {
            closeModal('userModal');
            return;
        }
        if (e.target.closest('#btnCloseRolePicker')) {
            closeModal('rolePickerPopup');
            return;
        }
        if (e.target.closest('#btnCloseStatusPicker')) {
            closeModal('statusPickerPopup');
            return;
        }
        if (e.target.closest('#btnCancelConfirm') || e.target.classList.contains('glass-modal-overlay')) {
            closeSystemConfirm();
            closeSystemAlert();
            return;
        }

        // Picker Buttons
        if (e.target.closest('#rolePickerBtn')) {
            openRolePicker();
            return;
        }
        if (e.target.closest('#statusPickerBtn')) {
            openStatusPicker();
            return;
        }

        // Picker Cards
        const pickerCard = e.target.closest('.picker-card');
        if (pickerCard) {
            const popup = pickerCard.closest('.picker-popup-overlay');
            const value = pickerCard.dataset.value;
            const text = pickerCard.dataset.text;

            if (popup.id === 'rolePickerPopup') {
                selectRole(value, text);
            } else if (popup.id === 'statusPickerPopup') {
                selectStatus(value, text);
            }
            return;
        }

        // Outside click close
        if (e.target.classList.contains('picker-popup-overlay') || e.target.classList.contains('user-modal-overlay')) {
            closeModal(e.target.id);
        }
    });

    // --- 2. Form Submission ---
    if (userForm) {
        userForm.addEventListener('submit', function (e) {
            e.preventDefault();
            saveUserProcess(this);
        });
    }

    // --- 3. Category Toggles ---
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('cat-toggle')) {
            const cat = e.target.dataset.category;
            const checked = e.target.checked;
            document.querySelectorAll('.perm-' + cat).forEach(cb => cb.checked = checked);
        }
    });

    // Keyboard support
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            ['userModal', 'rolePickerPopup', 'statusPickerPopup', 'confirmModal', 'alertModal'].forEach(id => closeModal(id));
        }
    });
}

// ===== Core Functions =====

function handleEditUser(id) {
    console.log('Edit User ID:', id);
    fetch('index.php?page=users&action=ajaxGet&id=' + id)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                resetUserForm(); // Start clean
                fillFormData(data.user);
                openModal('userModal');
                document.getElementById('modalTitle').textContent = 'تعديل مستخدم';
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(err => showAlert('error', 'خطأ في الاتصال بالسيرفر'));
}

function handleDeleteUser(id, username) {
    showSystemConfirm('تأكيد الحذف', 'هل أنت متأكد من حذف المستخدم "' + username + '"؟ <br> لا يمكن التراجع عن هذا الإجراء.', function () {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('csrf_token', window.csrfToken);

        fetch('index.php?page=users&action=ajaxDelete', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showSystemAlert('success', data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showSystemAlert('error', data.message);
                }
            });
    }, true);
}

function saveUserProcess(form) {
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const userId = document.getElementById('userId').value;
    const msg = userId ? 'هل أنت متأكد من حفظ التعديلات؟' : 'هل أنت متأكد من إضافة هذا المستخدم؟';
    const title = userId ? 'تأكيد التعديل' : 'تأكيد الإضافة';

    showSystemConfirm(title, msg, function () {
        fetch('index.php?page=users&action=ajaxSave', {
            method: 'POST',
            body: new FormData(form)
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showSystemAlert('success', data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showSystemAlert('error', data.message);
                }
            })
            .catch(err => showSystemAlert('error', 'فشل الاتصال بالسيرفر'));
    });
}

// ===== Helpers =====

function fillFormData(user) {
    document.getElementById('userId').value = user.id;
    document.getElementById('username').value = user.username;
    document.getElementById('password').value = '';
    document.getElementById('passHint').textContent = '(اتركها فارغة للإبقاء على القديمة)';
    document.getElementById('password').required = false;

    setRole(user.role, getRoleLabel(user.role));
    setStatus(user.status, getStatusLabel(user.status));

    if (user.permissions && Array.isArray(user.permissions)) {
        user.permissions.forEach(p => {
            const cb = document.querySelector('.perm-check[data-name="' + p + '"]');
            if (cb) cb.checked = true;
        });
    }
    togglePermissionsSection(user.role);
}

function resetUserForm() {
    const form = document.getElementById('userForm');
    if (form) form.reset();
    document.getElementById('userId').value = '';
    document.getElementById('modalTitle').textContent = 'إضافة مستخدم جديد';
    document.getElementById('passHint').textContent = '(مطلوبة)';
    document.getElementById('password').required = true;

    setRole('user', '👤 مستخدم');
    setStatus('active', '✅ نشط');
    document.querySelectorAll('.perm-check, .cat-toggle').forEach(c => c.checked = false);
    togglePermissionsSection('user');
}

function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'flex';
}

function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
}

function openRolePicker() {
    openModal('rolePickerPopup');
    const current = document.getElementById('roleValue').value;
    document.querySelectorAll('#rolePickerPopup .picker-card').forEach(c => {
        c.classList.toggle('selected', c.dataset.value === current);
    });
}

function openStatusPicker() {
    openModal('statusPickerPopup');
    const current = document.getElementById('statusValue').value;
    document.querySelectorAll('#statusPickerPopup .picker-card').forEach(c => {
        c.classList.toggle('selected', c.dataset.value === current);
    });
}

function selectRole(val, text) {
    setRole(val, text);
    closeModal('rolePickerPopup');
    togglePermissionsSection(val);
}

function selectStatus(val, text) {
    setStatus(val, text);
    closeModal('statusPickerPopup');
}

function setRole(val, text) {
    document.getElementById('roleValue').value = val;
    document.getElementById('selectedRoleText').textContent = text;
}

function setStatus(val, text) {
    document.getElementById('statusValue').value = val;
    document.getElementById('selectedStatusText').textContent = text;
}

function togglePermissionsSection(role) {
    const sec = document.getElementById('permissionsSection');
    // السماح للجميع برؤية الصلاحيات لضمان الوضوح التام
    if (sec) sec.style.display = 'block';
}

function getRoleLabel(role) {
    return { 'admin': '👑 مدير', 'manager': '👔 مشرف', 'user': '👤 مستخدم' }[role] || '👤 مستخدم';
}

function getStatusLabel(status) {
    return { 'active': '✅ نشط', 'inactive': '🚫 موقوف' }[status] || '✅ نشط';
}

// Old showAlert/showConfirm removed


// ===== BULK DELETE LOGIC =====

function toggleSelectAll() {
    const isChecked = document.getElementById('selectAll').checked;
    document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.checked = isChecked;
    });
    updateBulkSelect();
}

function updateBulkSelect() {
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    const checkedCount = checkboxes.length;
    const btn = document.getElementById('bulkDeleteBtn');

    // Check if Select All should be checked
    const allCheckboxes = document.querySelectorAll('.row-checkbox');
    const selectAllCb = document.getElementById('selectAll');
    if (selectAllCb && allCheckboxes.length > 0) {
        selectAllCb.checked = (checkedCount === allCheckboxes.length);
    }

    if (checkedCount > 0) {
        if (btn) {
            btn.style.display = 'inline-flex';
            document.getElementById('selectedCount').textContent = checkedCount;
        }
    } else {
        if (btn) btn.style.display = 'none';
    }
}

function confirmBulkDelete() {
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    const ids = Array.from(checkboxes).map(cb => cb.value);

    if (ids.length === 0) return;

    showSystemConfirm('تأكيد الحذف الجماعي', 'هل أنت متأكد من حذف ' + ids.length + ' مستخدمين؟ <br> لا يمكن التراجع عن هذا الإجراء.', function () {
        const btn = document.getElementById('bulkDeleteBtn');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class=\'fas fa-spinner fa-spin\'></i> جاري الحذف...';
        btn.disabled = true;

        fetch('index.php?page=users&action=bulkDelete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                ids: ids,
                csrf_token: window.csrfToken
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showSystemAlert('success', data.message);
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showSystemAlert('error', 'فشل الحذف: ' + data.message);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
                showSystemAlert('error', 'حدث خطأ أثناء الاتصال بالخادم');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
    }, true);
}
