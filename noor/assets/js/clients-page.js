/* ===================================================
 * Clients Page JavaScript
 * تم استخراج هذا الملف تلقائياً بواسطة extract_clients_css_js.py
 * =================================================== */

// Global state
let sysConfirmCallback = null;
let shouldReloadOnClose = false; // Flag to reload on close

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

/* =================================================== */

// Categories data for reference
// Categories data is passed from the view via window.categoriesData

// checkCategoryModal removed (deprecated)

async function loadClientData(clientId) {
    try {
        const response = await fetch(`index.php?page=clients&action=ajaxGet&id=${clientId}`);
        const data = await response.json();

        if (data.success) {
            const client = data.client;
            document.getElementById('clientId').value = client.id;
            document.getElementById('clientName').value = client.name;
            document.getElementById('clientCategory').value = client.category_id;
            document.getElementById('clientPhone').value = client.phone || '';
            document.getElementById('clientAddress').value = client.address || '';
            document.getElementById('clientStatus').value = client.status;
            document.getElementById('clientCategoryCustom').value = client.category_custom || '';

            // Update category picker button
            const categoryCard = document.querySelector(`.category-card[data-id="${client.category_id}"]`);
            if (categoryCard) {
                document.getElementById('selectedCategoryText').textContent = categoryCard.dataset.name;
                document.getElementById('categoryPickerBtn').classList.add('selected');
                categoryCard.classList.add('selected');

                // Check if custom field should show
                if (categoryCard.dataset.mandatory == '1') {
                    document.getElementById('customCategoryGroup').style.display = 'block';
                    document.getElementById('clientCategoryCustom').required = true;
                }
            }
        } else {
            alert('حدث خطأ في تحميل بيانات العميل');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('حدث خطأ في الاتصال بالخادم');
    }
}

async function saveClient(event) {
    if (event) event.preventDefault();

    const form = document.getElementById('clientForm');
    const saveBtn = document.getElementById('saveBtn');
    const formData = new FormData(form);

    // Explicitly append CSRF token for security
    if (window.csrfToken) {
        formData.append('csrf_token', window.csrfToken);
    }

    const isSmart = document.getElementById('smartAutoSaveToggle')?.checked;

    // Validate Name
    const name = document.getElementById('clientName').value.trim();
    if (!name) {
        showSystemAlert('error', 'يرجى كتابة اسم العميل');
        return false;
    }

    // Disable button
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...';

    try {
        const response = await fetch('index.php?page=clients&action=ajaxSave', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            shouldReloadOnClose = true; // Mark for reload
            const returnUrl = sessionStorage.getItem('clientReturnUrl');

            if (isSmart && !returnUrl) { // Only smart mode if NOT returning elsewhere
                showSystemAlert('success', 'تم إضافة العميل بنجاح');

                // Clear inputs for next entry
                document.getElementById('clientId').value = '';
                document.getElementById('clientName').value = '';
                document.getElementById('clientPhone').value = '';
                document.getElementById('clientAddress').value = '';
                // Keep category selected for speed

                document.getElementById('clientName').focus();
            } else {
                closeClientModal();
                if (returnUrl) {
                    sessionStorage.removeItem('clientReturnUrl');
                    window.location.href = returnUrl;
                } else {
                    window.location.reload();
                }
            }
        } else {
            showSystemAlert('error', data.message || 'حدث خطأ أثناء الحفظ');
        }
    } catch (error) {
        console.error('Error:', error);
        showSystemAlert('error', 'حدث خطأ في الاتصال بالخادم');
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-save"></i> حفظ البيانات';
    }

    return false;
}

// Add Enter Key Listener for Smart Save
document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('keydown', function (e) {
        const modal = document.getElementById('clientModal');
        if (e.key === 'Enter' && modal && modal.style.display === 'flex') {
            // FIX: Don't trigger if an alert or confirm modal is open
            const alertModal = document.getElementById('systemAlertModal');
            const confirmModal = document.getElementById('systemConfirmModal');
            if ((alertModal && alertModal.style.display === 'flex' && alertModal.classList.contains('show')) ||
                (confirmModal && confirmModal.style.display === 'flex')) {
                return;
            }

            const isSmart = document.getElementById('smartAutoSaveToggle')?.checked;
            if (isSmart) {
                e.preventDefault();
                if (typeof saveClient === 'function') saveClient(e);
            }
        }
    });

    // Close modal on overlay click (Consolidated)
    const modal = document.getElementById('clientModal');
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === this) {
                closeClientModal();
            }
        });
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function (e) {
        const modal = document.getElementById('clientModal');
        if (e.key === 'Escape' && modal && modal.style.display === 'flex') {
            closeClientModal();
        }
    });
});

function editClient(clientId) {
    openClientModal(clientId);
}

async function deleteClient(clientId) {
    showSystemConfirm(
        'تأكيد حذف عميل',
        'هل أنت متأكد من حذف هذا العميل؟<br>سيتم حذف جميع البيانات المرتبطة به ولا يمكن التراجع.',
        async function () {
            try {
                const response = await fetch(`index.php?page=clients&action=ajaxDelete&id=${clientId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `csrf_token=${window.csrfToken}`
                });
                const data = await response.json();

                if (data.success) {
                    showSystemAlert('success', data.message || 'تم الحذف بنجاح');
                    // Remove row from table
                    const row = document.querySelector(`tr[data-id="${clientId}"]`);
                    if (row) {
                        row.animate([
                            { opacity: 1, transform: 'translateX(0)' },
                            { opacity: 0, transform: 'translateX(-50px)' }
                        ], { duration: 300, fill: 'forwards' }).onfinish = () => row.remove();
                    }
                } else {
                    showSystemAlert('error', data.message || 'حدث خطأ أثناء الحذف');
                }
            } catch (error) {
                console.error('Error:', error);
                showSystemAlert('error', 'حدث خطأ في الاتصال بالخادم');
            }
        },
        true // isDanger
    );
}

// Close modal on overlay click
// Consolidated inside DOMContentLoaded above

// ===== Auto-open modal from URL parameter =====
document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);

    // Check if we should auto-open the modal for adding
    if (urlParams.get('openModal') === '1') {
        // Store return URL if provided
        const returnUrl = urlParams.get('returnUrl');
        if (returnUrl) {
            sessionStorage.setItem('clientReturnUrl', decodeURIComponent(returnUrl));
        }

        // Open the modal after a short delay
        setTimeout(() => {
            if (typeof openClientModal === 'function') {
                openClientModal();
            }
        }, 300);

        // Clean URL
        urlParams.delete('openModal');
        urlParams.delete('returnUrl');
        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.history.replaceState({}, '', newUrl);
    }

    // Check if we should auto-open the modal for editing
    const editId = urlParams.get('editModal');
    if (editId) {
        // Open the modal in edit mode after a short delay
        setTimeout(() => {
            if (typeof openClientModal === 'function') {
                openClientModal(parseInt(editId));
            }
        }, 300);

        // Clean URL
        urlParams.delete('editModal');
        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.history.replaceState({}, '', newUrl);
    }
});

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

    showSystemConfirm(
        'تأكيد الحذف الجماعي',
        'هل أنت متأكد من حذف ' + ids.length + ' عملاء؟<br>سيتم حذف جميع البيانات المرتبطة بهم نهائياً.',
        function () {
            const btn = document.getElementById('bulkDeleteBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class=\'fas fa-spinner fa-spin\'></i> جاري الحذف...';
            btn.disabled = true;

            fetch('index.php?page=clients&action=bulkDelete', {
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
        },
        true // isDanger
    );
}

function openClientModal(clientId = null) {
    // ===== INIT PAGE =====
    const modal = document.getElementById('clientModal');
    const form = document.getElementById('clientForm');
    const title = document.getElementById('modalTitle');
    // FIX: Updated selector for new Glass Design
    const titleIcon = modal.querySelector('.glass-modal-icon-wrapper i');
    const statusGroup = document.getElementById('statusGroup');

    // Reset form
    form.reset();
    document.getElementById('clientId').value = '';
    document.getElementById('clientCategory').value = '';
    document.getElementById('selectedCategoryText').textContent = 'اختر التصنيف...';
    document.getElementById('categoryPickerBtn').classList.remove('selected');
    document.getElementById('customCategoryGroup').style.display = 'none';

    // Reset selected state in popup cards
    document.querySelectorAll('.category-card').forEach(c => c.classList.remove('selected'));

    if (clientId) {
        // Edit mode
        title.textContent = 'تعديل بيانات عميل';
        if (titleIcon) titleIcon.className = 'fas fa-user-edit';
        if (statusGroup) statusGroup.style.display = 'block';
        loadClientData(clientId);
    } else {
        // Add mode
        title.textContent = 'إضافة عميل جديد';
        if (titleIcon) titleIcon.className = 'fas fa-user-plus';
        if (statusGroup) statusGroup.style.display = 'none';
    }

    modal.style.display = 'flex';
    // Add show class for opacity transition
    setTimeout(() => modal.classList.add('show'), 10);
    document.body.style.overflow = 'hidden';

    // Focus on name field
    setTimeout(() => {
        const nameField = document.getElementById('clientName');
        if (nameField) nameField.focus();
    }, 100);
}

function closeClientModal() {
    const modal = document.getElementById('clientModal');
    if (!modal) return;

    // FIX: Reload if data was changed
    if (shouldReloadOnClose) {
        window.location.reload();
        return;
    }

    modal.classList.remove('show');
    // Wait for transition before hiding
    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }, 300);
}

// ===== Category Popup Functions =====

function openCategoryPopup() {
    const popup = document.getElementById('categoryPopup');
    popup.style.display = 'flex';
}

function closeCategoryPopup() {
    const popup = document.getElementById('categoryPopup');
    popup.style.display = 'none';
}

function selectCategory(id, name, isMandatory) {
    // Update hidden input
    document.getElementById('clientCategory').value = id;

    // Update button text
    document.getElementById('selectedCategoryText').textContent = name;
    document.getElementById('categoryPickerBtn').classList.add('selected');

    // Update selected state in cards
    document.querySelectorAll('.category-card').forEach(c => c.classList.remove('selected'));
    document.querySelector(`.category-card[data-id="${id}"]`)?.classList.add('selected');

    // Handle custom category field
    const customGroup = document.getElementById('customCategoryGroup');
    const customInput = document.getElementById('clientCategoryCustom');

    if (isMandatory == 1) {
        customGroup.style.display = 'block';
        customInput.required = true;
        customGroup.animate([
            { opacity: 0, transform: 'translateY(-10px)' },
            { opacity: 1, transform: 'translateY(0)' }
        ], { duration: 300, fill: 'forwards' });
    } else {
        customGroup.style.display = 'none';
        customInput.required = false;
        customInput.value = '';
    }

    // Close popup
    closeCategoryPopup();
}

// Close popup on overlay click
const catPopup = document.getElementById('categoryPopup');
if (catPopup) {
    catPopup.addEventListener('click', function (e) {
        if (e.target === this) {
            closeCategoryPopup();
        }
    });
}
