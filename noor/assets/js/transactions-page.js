/* ===================================================
 * Transactions Page JavaScript
 * تم استخراج هذا الملف تلقائياً بواسطة extract_css_js.py
 * =================================================== */

// Helper to get formatted date DD-MM-YYYY
function getFormattedDate(date) {
    const d = date || new Date();
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}-${month}-${year}`;
}
window.currentDate = window.currentDate || getFormattedDate();

/**
 * Transactions Page Logical Script
 * تم استعادة هذا الملف ليكون مضمن داخل الصفحة لضمان الاستقرار
 */

// Global State
let draggedItem = null;
let transactions = [];
let sysConfirmCallback = null; // Callback global for system confirm

// ===== System Modal Helpers =====
function showSystemConfirm(title, message, callback, isDanger = false) {
    const modal = document.getElementById('systemConfirmModal');
    const titleEl = document.getElementById('sysModalTitle');
    const bodyEl = document.getElementById('sysModalBody');
    const iconEl = document.getElementById('sysModalIcon');
    const btn = document.getElementById('sysModalActionBtn');

    if (!modal) return false;

    titleEl.textContent = title;
    bodyEl.innerHTML = message; // Allow HTML for line breaks
    sysConfirmCallback = callback;

    // Style Adjustments
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

    // One-time event listener for confirm button using the global callback
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
    const titleEl = document.getElementById('sysAlertTitle');
    const bodyEl = document.getElementById('sysAlertBody');
    const iconEl = document.getElementById('sysAlertIcon');

    if (!modal) {
        alert(message); // Fallback
        return;
    }

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

let state = {
    transactions: [],
    clients: [],
    categories: [],
    filters: {
        search: '',
        type: '',
        client_id: '',
        date_from: '',
        date_to: ''
    },
    pagination: {
        current: 1,
        total: 1,
        limit: 50
    }
};

document.addEventListener('DOMContentLoaded', () => {
    // Initialize State from Server Data (Optional if we want reactive feel, but for now PHP rendering is primary)
    // Setup Event Listeners
    setupEventListeners();

    // Setup Date Inputs Defaults
    document.querySelectorAll('input[type="date"]').forEach(input => {
        if (!input.value) input.value = window.currentDate;
    });

    // Initialize Select2 equivalents or custom dropdowns if any
});

function setupEventListeners() {
    // Search Input Debounce (Table Filter)
    const searchInput = document.getElementById('instantSearch');
    if (searchInput) {
        searchInput.addEventListener('input', debounce((e) => {
            filterLocalTable(e.target.value);
        }, 200)); // Phase 11: Faster for local search
    }

    // Smart Client Input (Modal)
    setupClientAutosuggest();
}

// ===== SMART CLIENT AUTOSUGGEST =====
function setupClientAutosuggest() {
    const input = document.getElementById('client_name_input');
    const suggestionsBox = document.getElementById('clientSuggestions'); // Use existing element

    input.addEventListener('input', debounce((e) => { // Phase 11: 400ms for AJAX
        const query = e.target.value.trim();
        const hiddenId = document.getElementById('modal_client');

        // If user clears input, reset
        if (query.length === 0) {
            hiddenId.value = '';
            suggestionsBox.style.display = 'none';
            unlockCategory(); // Unlock for manual selection if empty
            return;
        }

        // If user is typing, we assume it's a new or search intent, so tentatively clear ID until selected
        // But we keep the text. 
        // Backend logic: if ID is empty but Name is provided, it's a NEW client.
        hiddenId.value = '';
        unlockCategory(); // Unlock because it might be a new client requiring a category

        // Fetch Matches
        fetch(`index.php?page=transactions&action=ajaxClientSearch&q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                suggestionsBox.innerHTML = '';
                if (data && data.length > 0) {
                    data.forEach(client => {
                        const item = document.createElement('div');
                        item.className = 'suggestion-item';

                        const nameSpan = document.createElement('span');
                        nameSpan.textContent = client.name;

                        const categorySpan = document.createElement('span');
                        categorySpan.style.opacity = '0.6';
                        categorySpan.style.fontSize = '0.8em';
                        categorySpan.textContent = client.category_name || '';

                        item.appendChild(nameSpan);
                        item.appendChild(document.createTextNode(' '));
                        item.appendChild(categorySpan);

                        item.onclick = () => selectSmartClient(client);
                        suggestionsBox.appendChild(item);
                    });
                    suggestionsBox.style.display = 'block';
                } else {
                    // No match -> It's a new client
                    suggestionsBox.style.display = 'none';
                    // We already unlocked category above, so user can pick
                    handleNewClientInternal();
                }
            })
            .catch(err => console.error(err));
    }, 400)); // Phase 11: 400ms to reduce AJAX calls

    // Hide suggestions on click outside
    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !suggestionsBox.contains(e.target)) {
            suggestionsBox.style.display = 'none';
        }
    });
}

function selectSmartClient(client) {
    document.getElementById('client_name_input').value = client.name;
    document.getElementById('modal_client').value = client.id;
    document.getElementById('clientSuggestions').style.display = 'none';

    // Logic: Existing client -> Lock Category to their existing one
    lockCategory(client.category_id, client.category_name);
}

function handleNewClientInternal() {
    // Logic: New Client -> Unlock Category so user can click on it manually
    // لا نفتح النافذة تلقائياً - فقط نفعّل الحقل
    unlockCategory();

    // تحديث النص ليوضح للمستخدم أنه يجب اختيار تصنيف
    const categoryDisplay = document.getElementById('categoryNameDisplay');
    if (categoryDisplay && categoryDisplay.textContent === 'تصنيف تلقائي') {
        categoryDisplay.textContent = 'اضغط لاختيار التصنيف';
    }
}

function lockCategory(catId, catName) {
    document.getElementById('modal_category_id').value = catId;
    document.getElementById('categoryNameDisplay').textContent = catName || 'عام';

    const catField = document.getElementById('categoryCard');
    const lockIcon = document.getElementById('catLockIcon');
    if (catField) {
        catField.classList.remove('unlocked');
        catField.classList.add('locked');
    }
    if (lockIcon) {
        lockIcon.style.display = '';
    }
}

function unlockCategory() {
    const catField = document.getElementById('categoryCard');
    const lockIcon = document.getElementById('catLockIcon');
    if (catField) {
        catField.classList.remove('locked');
        catField.classList.add('unlocked');
    }
    if (lockIcon) {
        lockIcon.style.display = 'none';
    }
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function filterLocalTable(query) {
    query = query.toLowerCase().trim();
    const rows = document.querySelectorAll('#transactionsTable tbody tr');
    let found = false;

    rows.forEach(row => {
        if (row.querySelector('.empty-state')) return;

        const text = row.innerText.toLowerCase();
        if (text.includes(query)) {
            row.style.display = '';
            if (query.length > 1) row.classList.add('search-match');
            found = true;
        } else {
            row.style.display = 'none';
            row.classList.remove('search-match');
        }
    });

    if (query.length === 0) {
        rows.forEach(row => row.classList.remove('search-match'));
    }
}

// ===== MODAL LOGIC =====

function openModal(type = 'income') {
    const modal = document.getElementById('transactionModal');
    const form = document.getElementById('transactionForm');

    // Reset Form
    form.reset();
    document.getElementById('trans_id').value = '';
    document.getElementById('modal_date').value = window.currentDate; // Use server date
    document.getElementById('modal_client').value = '';
    document.getElementById('modal_category_id').value = '1';
    document.getElementById('categoryNameDisplay').innerText = 'عام';

    // Lock category by default (will unlock when typing new client)
    lockCategory(1, 'عام');

    // Set Type
    setType(type);

    // Show Modal
    modal.style.display = 'flex';
}

function closeModal() {
    const modal = document.getElementById('transactionModal');
    modal.style.display = 'none';
}

function setType(type) {
    // Update hidden input
    document.getElementById('trans_type').value = type;

    // Update Modal Container class
    const modalContainer = document.getElementById('transModalContainer');
    if (modalContainer) {
        modalContainer.classList.remove('income', 'expense', 'advance');
        modalContainer.classList.add(type);
    }

    // Update Type Tabs
    document.querySelectorAll('.trans-type-tab').forEach(tab => {
        tab.classList.remove('active');
        if (tab.dataset.type === type) {
            tab.classList.add('active');
        }
    });
}

// ===== CRUD OPERATIONS =====

// متغير لحفظ بيانات النموذج مؤقتاً
let pendingTransactionData = null;

async function saveTransaction(event) {
    event.preventDefault();

    const btn = event.target.querySelector('button[type="submit"]');
    const originalText = btn ? btn.innerHTML : '';

    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData.entries());

    // Basic Validation
    if (!data.client_id && !data.client_name.trim()) {
        alert('برجاء اختيار عميل أو إدخال اسم عميل جديد');
        return false;
    }

    if (!data.client_id && !data.category_id) {
        alert('برجاء اختيار تصنيف للعميل الجديد');
        openCategoryPopup();
        return false;
    }

    if (!data.amount || parseFloat(data.amount) <= 0) {
        alert('برجاء إدخال مبلغ صحيح');
        return false;
    }

    // السداد التلقائي يتم في Backend - لا حاجة للتحقق هنا
    await executeTransactionSave(formData, btn, originalText, false);
    return false;
}

/**
 * تنفيذ حفظ المعاملة فعلياً
 */
async function executeTransactionSave(formData, btn, originalText, shouldDeduct) {
    if (btn) {
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...';
        btn.disabled = true;
    }

    try {
        const response = await fetch('index.php?page=transactions&action=ajaxAdd', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams(formData)
        });

        const result = await response.json();

        if (result.success) {
            const message = shouldDeduct ? 'تم الحفظ وخصم الدين بنجاح' : 'تم الحفظ بنجاح';
            showToast('bi-check-circle-fill', message, 'success');
            closeModal();
            setTimeout(() => window.location.reload(), 500);
        } else {
            alert(result.message || 'حدث خطأ أثناء الحفظ');
        }

    } catch (error) {
        console.error(error);
        alert('حدث خطأ غير متوقع');
    } finally {
        if (btn) {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }
}

function editTransaction(id) {
    // Fetch transaction data
    fetch(`index.php?page=transactions&action=ajaxGet&id=${id}`)
        .then(res => res.json())
        .then(result => {
            if (result.success && result.data) {
                const data = result.data;
                openModal(data.type);

                // Fill Data
                document.getElementById('trans_id').value = data.id;
                document.getElementById('modal_client').value = data.client_id;
                document.getElementById('client_name_input').value = data.client_name; // Requires JOIN in ajaxGet

                // Format Date to DD-MM-YYYY
                if (data.date) {
                    const [y, m, d] = data.date.split('-');
                    document.getElementById('modal_date').value = `${d}-${m}-${y}`;
                } else {
                    document.getElementById('modal_date').value = window.currentDate;
                }

                document.getElementById('modal_amount').value = data.amount;
                document.getElementById('modal_notes').value = data.notes;

                // Category setup
                // Since this is an existing transaction/client, we should probably lock it or allow edit?
                // For edit mode, usually we just show it.
                // Assuming data has category info (we added joins in controller)
                if (data.category_id) {
                    lockCategory(data.category_id, data.category_name);
                } else {
                    unlockCategory();
                }

                // Payment Method
                const radio = document.querySelector(`input[name="payment_method"][value="${data.payment_method}"]`);
                if (radio) radio.checked = true;

                document.getElementById('modalTitle').innerText = 'تعديل المعاملة';
            } else {
                alert('فشل في جلب بيانات المعاملة');
            }
        })
        .catch(err => console.error(err));
}

function deleteTransaction(id) {
    showSystemConfirm(
        'تأكيد الحذف',
        'هل أنت متأكد من حذف هذه المعاملة؟<br>لا يمكن التراجع عن هذا الإجراء.',
        function () {
            // باستخدام ajaxDelete بدلاً من delete
            fetch('index.php?page=transactions&action=ajaxDelete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}&csrf_token=${window.csrfToken}`
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showSystemAlert('success', 'تم الحذف بنجاح');
                        // إزالة الصف من الجدول بدون إعادة تحميل الصفحة
                        const row = document.querySelector(`tr[data-id="${id}"]`);
                        if (row) {
                            row.style.transition = 'all 0.3s';
                            row.style.transform = 'translateX(-100%)';
                            row.style.opacity = '0';
                            setTimeout(() => row.remove(), 300);
                        }
                    } else {
                        showSystemAlert('error', 'فشل الحذف: ' + (data.message || 'خطأ غير معروف'));
                    }
                })
                .catch(err => {
                    console.error(err);
                    showSystemAlert('error', 'حدث خطأ أثناء الحذف');
                });
        },
        true // isDanger
    );
}

// ===== BROWSE CLIENTS LOGIC =====

function openBrowseClientsModal() {
    const modal = document.getElementById('browseClientsModal');
    modal.style.display = 'flex';
    loadBrowseClients();
}

function closeBrowseClientsModal() {
    document.getElementById('browseClientsModal').style.display = 'none';
}

function loadBrowseClients() {
    const grid = document.getElementById('browseClientsGrid');
    grid.innerHTML = '<div style="color:white;text-align:center;width:100%;">جاري التحميل...</div>';

    // Correct URL to use ajaxClientSearch
    fetch('index.php?page=transactions&action=ajaxClientSearch&q=')
        .then(res => res.json())
        .then(data => {
            grid.innerHTML = '';
            if (data && data.length > 0) {
                data.forEach(client => {
                    const card = createClientCard(client);
                    grid.appendChild(card);
                });
            } else {
                grid.innerHTML = '<div style="color:white;text-align:center;width:100%;">لا يوجد عملاء مطابقين</div>';
            }
        })
        .catch(err => {
            console.error(err);
            grid.innerHTML = '<div style="color:red;text-align:center;">فشل التحميل</div>';
        });
}

// ===== CATEGORY LOGIC =====

function handleCategoryClick() {
    // Only allow changing category if we are creating a new client/transaction or if explicitly allowed
    // Check if locked - تصحيح المعرف من categoryField إلى categoryCard
    const catField = document.getElementById('categoryCard');
    if (catField && catField.classList.contains('locked')) {
        // Optional: show tooltip "Category fixed for this client"
        return;
    }
    openCategoryPopup();
}

function openCategoryPopup() {
    const popup = document.getElementById('categoryPopup');
    if (popup) popup.style.display = 'flex';
}

function closeCategoryPopup() {
    const popup = document.getElementById('categoryPopup');
    if (popup) popup.style.display = 'none';
}

function selectCategory(id, name) {
    document.getElementById('modal_category_id').value = id;
    document.getElementById('categoryNameDisplay').textContent = name;
    closeCategoryPopup();
}

function createClientCard(client) {
    const div = document.createElement('div');
    div.className = 'client-card-modern';
    div.onclick = () => selectClientFromBrowse(client);

    const avatar = document.createElement('div');
    avatar.className = `client-avatar av-${(client.id % 5) + 1}`;
    avatar.textContent = client.name.charAt(0);

    const info = document.createElement('div');
    info.className = 'client-info-modern';

    const nameDiv = document.createElement('div');
    nameDiv.className = 'client-name-modern';
    nameDiv.textContent = client.name;

    const phoneDiv = document.createElement('div');
    phoneDiv.className = 'client-phone-modern';
    phoneDiv.textContent = client.phone || '';

    info.appendChild(nameDiv);
    info.appendChild(phoneDiv);

    div.appendChild(avatar);
    div.appendChild(info);
    return div;
}

function selectClientFromBrowse(client) {
    // Reuse the smart selection logic
    selectSmartClient(client);
    closeBrowseClientsModal();
}

function filterBrowseList(query) {
    query = query.toLowerCase();
    const cards = document.querySelectorAll('#browseClientsGrid .client-card-modern');
    cards.forEach(card => {
        const name = card.querySelector('.client-name-modern').innerText.toLowerCase();
        const phone = card.querySelector('.client-phone-modern').innerText.toLowerCase();
        if (name.includes(query) || phone.includes(query)) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

// ===== FILTER PICKER LOGIC =====

// Filter Type
function openFilterTypeModal() {
    document.getElementById('filterTypeModal').style.display = 'flex';
}

function closeFilterTypeModal() {
    document.getElementById('filterTypeModal').style.display = 'none';
}

function selectFilterType(type, label) {
    document.getElementById('filterTypeId').value = type;
    document.getElementById('filterTypeName').innerText = label;
    closeFilterTypeModal();
    // Auto submit?
    // document.querySelector('.filter-form-neon').submit();
}

// Filter Client
function openFilterClientModal() {
    document.getElementById('filterClientModal').style.display = 'flex';
}

function closeFilterClientModal() {
    document.getElementById('filterClientModal').style.display = 'none';
}

function selectFilterClient(id, name) {
    document.getElementById('filterClientId').value = id ? id : '';
    document.getElementById('filterClientName').innerText = name;
    closeFilterClientModal();
}

function filterClientCards(query) {
    query = query.toLowerCase();
    const cards = document.querySelectorAll('#filterClientGrid .client-card-modern');
    cards.forEach(card => {
        const text = card.getAttribute('data-name').toLowerCase();
        if (text.includes(query)) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

function resetFilters() {
    window.location.href = 'index.php?page=transactions';
}

// ===== QUICK ADD CLIENT (SMART MODAL) =====
function openAddClientPage() {
    // Override old redirect behavior
    openQuickAddClientModal();
}

function openQuickAddClientModal() {
    const modal = document.getElementById('quickAddClientModal');
    if (modal) {
        // Move to body to avoid stacking issues
        document.body.appendChild(modal);
        modal.style.display = 'flex';
        modal.classList.add('show');

        // Reset Form
        document.getElementById('quickClientName').value = '';
        document.getElementById('quickClientPhone').value = '';
        document.getElementById('quickClientCategory').value = '';
        document.querySelectorAll('.category-pill').forEach(p => {
            p.style.background = 'rgba(255,255,255,0.05)';
            p.style.borderColor = 'rgba(255,255,255,0.1)';
            p.style.color = 'rgba(255,255,255,0.7)';
        });

        // Focus Name Input
        setTimeout(() => document.getElementById('quickClientName').focus(), 100);

        // Setup Enter Key Listeners
        setupQuickAddListeners();
    }
}

function closeQuickAddClientModal() {
    const modal = document.getElementById('quickAddClientModal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => modal.style.display = 'none', 300);
    }
}

function selectQuickCategory(el, id) {
    document.getElementById('quickClientCategory').value = id;
    // Reset all pills
    document.querySelectorAll('.category-pill').forEach(p => {
        p.style.background = 'rgba(255,255,255,0.05)';
        p.style.borderColor = 'rgba(255,255,255,0.1)';
        p.style.color = 'rgba(255,255,255,0.7)';
    });
    // Activate selected
    el.style.background = 'rgba(16, 185, 129, 0.2)';
    el.style.borderColor = '#10b981';
    el.style.color = '#10b981';
}

function setupQuickAddListeners() {
    const inputs = ['quickClientName', 'quickClientPhone'];
    inputs.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            // Remove old listeners to avoid duplicates if opened multiple times
            const newEl = el.cloneNode(true);
            el.parentNode.replaceChild(newEl, el);

            newEl.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    const isSmart = document.getElementById('smartAutoSaveToggle').checked;
                    if (isSmart) {
                        e.preventDefault();
                        saveQuickClient();
                    }
                }
            });
        }
    });
}

function saveQuickClient() {
    const name = document.getElementById('quickClientName').value.trim();
    const phone = document.getElementById('quickClientPhone').value.trim();
    const categoryId = document.getElementById('quickClientCategory').value;

    if (!name) {
        showToast('exclamation-circle', 'اسم العميل مطلوب', 'error');
        document.getElementById('quickClientName').focus();
        return;
    }

    const formData = new FormData();
    formData.append('csrf_token', window.csrfToken);
    formData.append('name', name);
    formData.append('phone', phone);
    formData.append('category_id', categoryId);

    // Show Loading...
    const btn = document.querySelector('#quickAddClientModal .glass-btn.confirm');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...';
    btn.disabled = true;

    fetch('index.php?page=clients&action=ajaxSave', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            btn.innerHTML = originalText;
            btn.disabled = false;

            if (data.success) {
                const isSmart = document.getElementById('smartAutoSaveToggle').checked;

                // Show Success
                showToast('check-circle', 'تم إضافة العميل بنجاح', 'success');

                // Update Local Data
                const newClient = {
                    id: data.id || 0,
                    name: name,
                    phone: phone,
                    category_id: categoryId
                };
                if (window.clientsData) window.clientsData.push(newClient);

                if (isSmart) {
                    // Smart Mode: Clear and Focus
                    document.getElementById('quickClientName').value = '';
                    document.getElementById('quickClientPhone').value = '';
                    document.getElementById('quickClientName').focus();
                } else {
                    // Normal Mode: Select & Close
                    closeQuickAddClientModal();
                    if (data.id) {
                        const hiddenInput = document.getElementById('modal_client');
                        const displayInput = document.getElementById('client_name_input');
                        if (hiddenInput) hiddenInput.value = data.id;
                        if (displayInput) displayInput.value = name;
                    } else {
                        setTimeout(() => window.location.reload(), 500);
                    }
                }
            } else {
                showToast('times-circle', data.message || 'حدث خطأ', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            btn.innerHTML = originalText;
            btn.disabled = false;
            showToast('times-circle', 'خطأ في الاتصال', 'error');
        });
}

// Toast Helper
function showToast(icon, msg, type) {
    // Simple alert fallback if no toast system
    // Or create a quick toast element
    const toast = document.createElement('div');
    toast.className = `export-toast ${type}`;
    toast.style.position = 'fixed';
    toast.style.bottom = '20px';
    toast.style.left = '20px';
    toast.style.padding = '15px 25px';
    toast.style.background = '#333';
    toast.style.color = 'white';
    toast.style.borderRadius = '8px';
    toast.style.zIndex = '3000';
    toast.style.border = type === 'success' ? '1px solid #2ecc71' : '1px solid #e74c3c';
    // Safe DOM construction
    const iconEl = document.createElement('i');
    iconEl.className = `bi ${icon}`;

    const msgNode = document.createTextNode(` ${msg}`);

    toast.appendChild(iconEl);
    toast.appendChild(msgNode);

    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Export & Print
function exportToExcel() {
    const table = document.getElementById('transactionsTable');
    if (!table) return;

    let html = `
            <html dir="rtl">
            <head>
                <meta charset="UTF-8">
                <style>
                    table { border-collapse: collapse; direction: rtl; }
                    th, td { border: 1px solid #000; padding: 8px; text-align: right; }
                    th { background-color: #1a4d6e; color: white; font-weight: bold; }
                </style>
            </head>
            <body>
                <table>
                    <tr>
                        <th>التاريخ</th>
                        <th>العميل</th>
                        <th>النوع</th>
                        <th>المبلغ</th>
                        <th>طريقة الدفع</th>
                        <th>ملاحظات</th>
                    </tr>
        `;

    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const cols = row.querySelectorAll('td');
        if (cols.length === 0) return;

        html += '<tr>';
        cols.forEach((col, index) => {
            if (index < cols.length - 1) {
                let text = col.innerText.trim().replace(/</g, '&lt;').replace(/>/g, '&gt;');
                html += '<td>' + text + '</td>';
            }
        });
        html += '</tr>';
    });

    html += '</table></body></html>';

    const blob = new Blob(['\ufeff' + html], { type: 'application/vnd.ms-excel' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'سجل_المعاملات_' + new Date().toISOString().slice(0, 10) + '.xls';
    link.click();

    showToast('check', 'تم تصدير البيانات بنجاح!', 'success');
}

function printTable() {
    const table = document.getElementById('transactionsTable');
    if (!table) {
        alert('لا توجد بيانات للطباعة');
        return;
    }

    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
            <!DOCTYPE html>
            <html dir="rtl">
            <head>
                <title>سجل المعاملات - طباعة</title>
                <style>
                    body { font-family: 'Cairo', Arial, sans-serif; padding: 20px; }
                    h1 { text-align: center; color: #1a4d6e; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { border: 1px solid #ddd; padding: 10px; text-align: right; }
                    th { background: #1a4d6e; color: white; }
                    tr:nth-child(even) { background: #f9f9f9; }
                    .print-date { text-align: center; color: #777; margin-bottom: 20px; }
                    @media print { button { display: none !important; } }
                </style>
            </head>
            <body>
                <h1>سجل المعاملات</h1>
                <p class="print-date">تاريخ الطباعة: ${new Date().toLocaleDateString('ar-EG')}</p>
                ${table.outerHTML.replace(/<th[^>]*>إجراءات<\/th>/, '').replace(/<td class="actions-cell-neon">[\s\S]*?<\/td>/g, '')}
                <button onclick="window.print()" style="margin: 20px auto; display: block; padding: 10px 30px; font-size: 16px; cursor: pointer;">طباعة</button>
            </body>
            </html>
        `);
    printWindow.document.close();
}

// ===== MISSING FUNCTIONS RESTORED =====

// حفظ وإضافة معاملة جديدة
async function saveAndAddAnother() {
    const form = document.getElementById('transactionForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    // التحقق من البيانات
    if (!data.client_id && !data.client_name.trim()) {
        alert('برجاء اختيار عميل أو إدخال اسم عميل جديد');
        return;
    }

    if (!data.client_id && !data.category_id) {
        alert('برجاء اختيار تصنيف للعميل الجديد');
        openCategoryPopup();
        return;
    }

    if (!data.amount || parseFloat(data.amount) <= 0) {
        alert('برجاء إدخال مبلغ صحيح');
        return;
    }

    const btn = document.getElementById('saveAddBtn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...';
    btn.disabled = true;

    try {
        const response = await fetch('index.php?page=transactions&action=ajaxAdd', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams(formData)
        });

        const result = await response.json();

        if (result.success) {
            showToast('bi-check-circle-fill', 'تم الحفظ بنجاح', 'success');

            // إعادة تعيين النموذج مع الحفاظ على النوع
            const currentType = document.getElementById('trans_type').value;
            form.reset();
            document.getElementById('trans_id').value = '';
            document.getElementById('modal_date').value = window.currentDate;
            document.getElementById('modal_client').value = '';
            document.getElementById('modal_category_id').value = '';
            document.getElementById('categoryNameDisplay').innerText = 'اضغط للاختيار';
            unlockCategory();
            setType(currentType);

            // التركيز على حقل العميل
            document.getElementById('client_name_input').focus();
        } else {
            alert(result.message || 'حدث خطأ أثناء الحفظ');
        }
    } catch (error) {
        console.error(error);
        alert('حدث خطأ غير متوقع');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

// تبديل وضع الحفظ بـ Enter
let enterSaveEnabled = false;
function toggleEnterSave() {
    enterSaveEnabled = document.getElementById('enterSaveToggle').checked;

    if (enterSaveEnabled) {
        document.getElementById('transactionForm').addEventListener('keydown', handleEnterSave);
    } else {
        document.getElementById('transactionForm').removeEventListener('keydown', handleEnterSave);
    }
}

function handleEnterSave(e) {
    if (e.key === 'Enter' && !e.shiftKey && e.target.tagName !== 'TEXTAREA') {
        e.preventDefault();
        saveAndAddAnother();
    }
}

// إغلاق نافذة التشابه
function closeSimilarityModal() {
    const modal = document.getElementById('similarityModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// فتح نافذة التشابه
function openSimilarityModal(candidates) {
    const modal = document.getElementById('similarityModal');
    const list = document.getElementById('similarCandidatesList');

    if (modal && list) {
        list.innerHTML = '';
        candidates.forEach(c => {
            const item = document.createElement('div');
            item.className = 'similar-candidate-item';
            item.innerHTML = `
                <span class="candidate-name">${c.name}</span>
                <button type="button" class="btn-select-candidate" onclick="selectSimilarClient(${c.id}, '${c.name}')">
                    <i class="fas fa-check"></i> اختيار
                </button>
            `;
            list.appendChild(item);
        });
        modal.style.display = 'flex';
    }
}

// اختيار عميل مشابه
function selectSimilarClient(id, name) {
    document.getElementById('modal_client').value = id;
    document.getElementById('client_name_input').value = name;
    closeSimilarityModal();
}

// تأكيد إضافة عميل جديد
function confirmNewClient() {
    closeSimilarityModal();
    // إبقاء الاسم كما هو والمتابعة مع التصنيف
    unlockCategory();
    openCategoryPopup();
}

// إغلاق نافذة إدارة التصنيفات
function closeCategoryModal() {
    const modal = document.getElementById('categoryModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// فتح نافذة إدارة التصنيفات
function openCategoryModal() {
    const modal = document.getElementById('categoryModal');
    if (modal) {
        modal.style.display = 'flex';
        loadCategoryGrid();
    }
}

// تحميل شبكة التصنيفات في نافذة الإدارة
function loadCategoryGrid() {
    const grid = document.getElementById('categoryGrid');
    if (!grid) return;

    fetch('index.php?page=transactions&action=ajaxGetCategories')
        .then(res => res.json())
        .then(result => {
            if (result.success && result.data) {
                grid.innerHTML = '';
                result.data.forEach(cat => {
                    const card = document.createElement('div');
                    card.className = 'category-card';
                    // Safe DOM Construction
                    const icon = document.createElement('i');
                    icon.className = 'fas fa-tag';

                    const span = document.createElement('span');
                    span.textContent = cat.name;

                    const btn = document.createElement('button');
                    btn.className = 'delete-cat-btn';
                    btn.innerHTML = '<i class="fas fa-trash"></i>'; // innerHTML here is safe as it's static
                    btn.onclick = (e) => {
                        e.stopPropagation();
                        deleteCategoryItem(cat.id);
                    };

                    card.appendChild(icon);
                    card.appendChild(span);
                    card.appendChild(btn);
                    card.onclick = () => selectCategory(cat.id, cat.name);
                    grid.appendChild(card);
                });
            }
        })
        .catch(err => console.error(err));
}

// إضافة تصنيف جديد
function addNewCategory() {
    const input = document.getElementById('newCategoryName');
    const name = input.value.trim();

    if (!name) {
        alert('برجاء إدخال اسم التصنيف');
        return;
    }

    fetch('index.php?page=transactions&action=ajaxSaveCategory', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `name=${encodeURIComponent(name)}&csrf_token=${window.csrfToken}`
    })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                showToast('bi-check-circle-fill', 'تم إضافة التصنيف بنجاح', 'success');
                input.value = '';
                loadCategoryGrid();
            } else {
                alert(result.message || 'فشل في إضافة التصنيف');
            }
        })
        .catch(err => console.error(err));
}

// حذف تصنيف
function deleteCategoryItem(id) {
    if (!confirm('هل أنت متأكد من حذف هذا التصنيف؟')) return;

    fetch('index.php?page=transactions&action=ajaxDeleteCategory', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}&csrf_token=${window.csrfToken}`
    })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                showToast('bi-trash', 'تم حذف التصنيف', 'danger');
                loadCategoryGrid();
            } else {
                alert(result.message || 'فشل في حذف التصنيف');
            }
        })
        .catch(err => console.error(err));
}

// ===== QUICK REPAY ADVANCE =====

/**
 * فتح نافذة السداد السريع للسلفة
 */
function quickRepayAdvance(advanceId, advanceAmount, clientName, clientId) {
    const modal = document.getElementById('quickRepayModal');
    if (!modal) {
        // إذا لم توجد النافذة، استخدم prompt بسيط
        const amount = prompt(`سداد سلفة ${clientName}\nالمبلغ المتبقي: ${advanceAmount.toLocaleString('ar-EG')} ج.م\n\nأدخل مبلغ السداد:`, advanceAmount);
        if (amount && parseFloat(amount) > 0) {
            executeQuickRepay(clientId, parseFloat(amount), advanceId);
        }
        return;
    }

    // ملء بيانات النافذة
    document.getElementById('quickRepayClientName').textContent = clientName;
    document.getElementById('quickRepayAdvanceAmount').textContent = advanceAmount.toLocaleString('ar-EG', { minimumFractionDigits: 2 });
    document.getElementById('quickRepayAmount').value = advanceAmount;
    document.getElementById('quickRepayAmount').max = advanceAmount;
    document.getElementById('quickRepayAdvanceId').value = advanceId;
    document.getElementById('quickRepayClientId').value = clientId;

    modal.style.display = 'flex';
}

function closeQuickRepayModal() {
    const modal = document.getElementById('quickRepayModal');
    if (modal) modal.style.display = 'none';
}

function submitQuickRepay() {
    const advanceId = document.getElementById('quickRepayAdvanceId')?.value;
    const clientId = document.getElementById('quickRepayClientId')?.value;
    const amount = parseFloat(document.getElementById('quickRepayAmount')?.value || 0);

    if (amount <= 0) {
        alert('برجاء إدخال مبلغ صحيح');
        return;
    }

    executeQuickRepay(clientId, amount, advanceId);
}

async function executeQuickRepay(clientId, amount, advanceId) {
    const formData = new URLSearchParams({
        client_id: clientId,
        type: 'income',
        amount: amount,
        date: window.currentDate || new Date().toISOString().slice(0, 10),
        payment_method: 'cash',
        notes: 'سداد سلف',
        deduct_debt: '1',
        client_debt: amount,
        csrf_token: document.querySelector('input[name="csrf_token"]')?.value || ''
    });

    try {
        const response = await fetch('index.php?page=transactions&action=ajaxAdd', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showToast('bi-check-circle-fill', 'تم سداد السلفة بنجاح', 'success');
            closeQuickRepayModal();
            setTimeout(() => window.location.reload(), 500);
        } else {
            alert(result.message || 'حدث خطأ أثناء السداد');
        }
    } catch (error) {
        console.error(error);
        alert('حدث خطأ غير متوقع');
    }
}

// إضافة مستمع للإغلاق بـ Escape
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeModal();
        closeBrowseClientsModal();
        closeFilterClientModal();
        closeFilterTypeModal();
        closeCategoryPopup();
        closeCategoryModal();
        closeSimilarityModal();
        closeQuickRepayModal();
        const debtModal = document.getElementById('debtConfirmModal');
        if (debtModal) debtModal.style.display = 'none';
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

    // Update Select All checkbox state
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
        'هل أنت متأكد من حذف ' + ids.length + ' معاملات؟<br>لا يمكن التراجع عن هذا الإجراء.',
        function () {
            const btn = document.getElementById('bulkDeleteBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class=\'fas fa-spinner fa-spin\'></i> جاري الحذف...';
            btn.disabled = true;

            fetch('index.php?page=transactions&action=bulkDelete', {
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

// ===== FILTER MODALS =====

// Move modals to body to avoid z-index/stacking context issues
document.addEventListener('DOMContentLoaded', () => {
    const modalsToMove = ['filterTypeModal', 'filterClientModal'];
    modalsToMove.forEach(id => {
        const modal = document.getElementById(id);
        if (modal) {
            document.body.appendChild(modal);
            console.log(`Moved ${id} to body`);
        }
    });
});

function openFilterTypeModal() {
    console.log('openFilterTypeModal called');
    const modal = document.getElementById('filterTypeModal');
    console.log('Modal element:', modal);
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('show');
    } else {
        console.error('filterTypeModal not found!');
    }
}

function closeFilterTypeModal() {
    const modal = document.getElementById('filterTypeModal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => modal.style.display = 'none', 300);
    }
}

function selectFilterType(type, name) {
    document.getElementById('filterTypeId').value = type;
    document.getElementById('filterTypeName').textContent = name;
    closeFilterTypeModal();
}

function openFilterClientModal() {
    console.log('openFilterClientModal called');
    const modal = document.getElementById('filterClientModal');
    console.log('Client Modal element:', modal);
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('show');
        populateFilterClients();
    } else {
        console.error('filterClientModal not found!');
    }
}

function closeFilterClientModal() {
    const modal = document.getElementById('filterClientModal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => modal.style.display = 'none', 300);
    }
}

const clientColors = ['#f97316', '#8b5cf6', '#ef4444', '#22c55e', '#3b82f6', '#eab308', '#ec4899', '#06b6d4'];

function getClientColor(id) {
    return clientColors[id % clientColors.length];
}

function getInitials(name) {
    return name.charAt(0);
}

function populateFilterClients() {
    const container = document.getElementById('filterClientsList');
    if (!container || !window.clientsData) return;

    let html = `<div class="client-card new-client" onclick="openQuickAddClientModal()">
        <div class="client-avatar new-icon">
             <i class="fas fa-user-plus"></i>
        </div>
        <div class="client-name">عميل جديد</div>
    </div>`;

    window.clientsData.forEach(client => {
        const color = getClientColor(client.id);
        const initial = getInitials(client.name);
        html += `<div class="client-card" onclick="selectFilterClient('${client.id}', '${client.name}')">
            <div class="client-avatar" style="background-color: ${color};">
                ${initial}
            </div>
            <div class="client-name">${client.name}</div>
        </div>`;
    });

    container.innerHTML = html;
}

function selectFilterClient(id, name) {
    document.getElementById('filterClientId').value = id;
    document.getElementById('filterClientName').textContent = name;
    closeFilterClientModal();
}

function searchFilterClients(query) {
    const container = document.getElementById('filterClientsList');
    if (!container || !window.clientsData) return;

    // Filter Logic
    const filtered = window.clientsData.filter(c =>
        c.name.toLowerCase().includes(query.toLowerCase()) ||
        (c.phone && c.phone.includes(query))
    );

    let html = '';
    // Always show "New Client" if query doesn't match perfectly? Or keep it always? User image just has cards.
    // Let's keep "New Client" only if explicitly desired, but user image shows "Add New" button in header (blue).
    // I will stick mainly to the filtered results to match the "Browse" list.

    if (filtered.length === 0) {
        container.innerHTML = '<div class="no-results">لا توجد نتائج</div>';
        return;
    }

    filtered.forEach(client => {
        const color = getClientColor(client.id);
        const initial = getInitials(client.name);
        html += `<div class="client-card" onclick="selectFilterClient('${client.id}', '${client.name}')">
            <div class="client-avatar" style="background-color: ${color};">
                ${initial}
            </div>
            <div class="client-name">${client.name}</div>
        </div>`;
    });

    container.innerHTML = html;
}

// ===== CATEGORY POPUP LOGIC (Unified with Context) =====
let categoryPopupContext = 'main'; // 'main' or 'quick_add'

function openCategoryPopup(context = 'quick_add') { // Default to quick_add if called from button inside modal
    categoryPopupContext = context;
    const popup = document.getElementById('categoryPopup');
    if (popup) {
        // Ensure it's in body for correct Z-Index
        if (popup.parentElement !== document.body) {
            document.body.appendChild(popup);
        }

        popup.style.display = 'flex';
        // Force reflow
        void popup.offsetWidth;
        popup.classList.add('show');
    }
}

// Update handleCategoryClick for Main Form
function handleCategoryClick() {
    const catField = document.getElementById('categoryCard');
    if (catField && catField.classList.contains('locked')) {
        return;
    }
    openCategoryPopup('main');
}

function closeCategoryPopup() {
    const popup = document.getElementById('categoryPopup');
    if (popup) {
        popup.classList.remove('show');
        setTimeout(() => {
            popup.style.display = 'none';
        }, 300);
    }
}

function selectCategory(id, name) {
    if (categoryPopupContext === 'main') {
        // Update Main Transaction Form
        document.getElementById('modal_category_id').value = id;
        document.getElementById('categoryNameDisplay').textContent = name;
    } else {
        // Update Quick Add Client Modal
        const input = document.getElementById('quickClientCategory');
        const textDisplay = document.getElementById('selectedCategoryText');

        if (input) input.value = id;
        if (textDisplay) {
            textDisplay.textContent = name;
            textDisplay.style.color = '#fff';
        }

        // Show/Hide Custom Field in Quick Add
        const customGroup = document.getElementById('customCategoryGroup');
        const customInput = document.getElementById('clientCategoryCustom');

        if (customGroup && customInput) {
            if (name === 'أخرى') {
                customGroup.style.display = 'block';
                customInput.required = true;
            } else {
                customGroup.style.display = 'none';
                customInput.required = false;
                customInput.value = '';
            }
        }
    }

    closeCategoryPopup();
}

// Updated Save Quick Client
async function saveQuickClient() {
    const name = document.getElementById('quickClientName').value.trim();
    const phone = document.getElementById('quickClientPhone').value.trim();
    const category_id = document.getElementById('quickClientCategory').value;
    const category_custom = document.getElementById('clientCategoryCustom').value.trim();
    const address = document.getElementById('clientAddress').value.trim();

    if (!name) {
        alert('يرجى إدخال اسم العميل');
        return;
    }

    if (!category_id) {
        alert('يرجى اختيار التصنيف');
        return;
    }

    const formData = new FormData();
    formData.append('name', name);
    formData.append('phone', phone);
    formData.append('category_id', category_id);
    formData.append('category_custom', category_custom);
    formData.append('address', address);
    formData.append('csrf_token', window.csrfToken);

    try {
        const response = await fetch('index.php?page=clients&action=ajaxSave', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showSystemAlert('success', 'تم إضافة العميل بنجاح');
            closeQuickAddClientModal();

            // If called from the main transaction modal, auto-select this new client
            const clientNameInput = document.getElementById('client_name_input');
            const clientIdInput = document.getElementById('modal_client');

            if (clientNameInput && clientIdInput) {
                clientNameInput.value = name;
                clientIdInput.value = result.id || '';

                // Hide browse modal if open
                closeBrowseClientsModal();

                // Lock Category to the one just selected to prevent mismatch
                // This ensures existing category visual logic runs
                // Use fallback for category name if element unavailable, though it should be set
                const catName = document.getElementById('selectedCategoryText') ? document.getElementById('selectedCategoryText').textContent : 'عام';
                lockCategory(category_id, catName);
            } else {
                // Only reload if we are NOT in the main transaction modal context (fallback)
                setTimeout(() => window.location.reload(), 1000);
            }
        } else {
            showSystemAlert('error', result.message || 'فشل الحفظ');
        }
    } catch (error) {
        console.error(error);
        showSystemAlert('error', 'حدث خطأ في الاتصال');
    }
}
// Check for URL params to open modals
document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);

    // Existing logic for openModal (transactions)
    const openModalType = urlParams.get('openModal');
    if (openModalType && ['income', 'expense', 'advance'].includes(openModalType)) {
        openModal(openModalType);
        // Clean URL to prevent reopen on refresh
        const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + "?page=transactions";
        window.history.replaceState({ path: newUrl }, '', newUrl);
    }

    // NEW: Open Quick Add Client Modal
    if (urlParams.get('openQuickAdd') === '1') {
        // Short delay to ensure DOM is ready and styles loaded
        setTimeout(() => {
            if (typeof openQuickAddClientModal === 'function') {
                openQuickAddClientModal();
                // Clean URL
                const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + "?page=transactions";
                window.history.replaceState({ path: newUrl }, '', newUrl);
            } else {
                console.error('openQuickAddClientModal not defined');
            }
        }, 300);
    }
});

