/* assets/js/audit.js */

/**
 * Open Audit Details Modal
 * @param {Object} logData - The full log object from PHP
 */
let currentLogId = null;

/**
 * Open Audit Details Modal
 * @param {Object} logData - The full log object from PHP
 */
function openAuditModal(logData) {
    currentLogId = logData.id;
    const modal = document.getElementById('auditModal');
    const modalBody = document.getElementById('auditModalBody');

    // Construct Modal Content
    let content = `
        <div class="modal-detail-row">
            <span class="detail-label">التاريخ والوقت:</span>
            <span class="detail-value text-cyan">${logData.timestamp}</span>
        </div>
        <div class="modal-detail-row">
            <span class="detail-label">المستخدم:</span>
            <span class="detail-value">${logData.user_name || 'System'}</span>
        </div>
        <div class="modal-detail-row">
            <span class="detail-label">نوع العملية:</span>
            <span class="detail-value action-badge ${logData.action.toLowerCase()}">${logData.action}</span>
        </div>
        <div class="modal-detail-row">
            <span class="detail-label">الهدف:</span>
            <span class="detail-value text-white">${logData.target}</span>
        </div>
        
        <div class="modal-divider"></div>
        
        <div class="modal-detail-section">
            <h4 class="section-title">التفاصيل الكاملة:</h4>
            <div class="details-box">
                ${logData.details}
            </div>
        </div>
    `;

    modalBody.innerHTML = content;

    // Show Modal
    modal.classList.add('active');
    document.body.style.overflow = 'hidden'; // Prevent scrolling
}

/**
 * Close Audit Modal
 */
function closeAuditModal() {
    const modal = document.getElementById('auditModal');
    modal.classList.remove('active');
    document.body.style.overflow = ''; // Restore scrolling
}

/**
 * Show Custom Confirm Modal
 * @param {string} message - The confirmation message
 * @param {Function} onConfirm - Callback function if confirmed
 */
function showConfirmDialog(message, onConfirm) {
    const modal = document.getElementById('confirmModal');
    const msgEl = document.getElementById('confirmMessage');
    const btnYes = document.getElementById('btnConfirmYes');

    // Set message
    msgEl.textContent = message;

    // Reset button event
    let newBtn = btnYes.cloneNode(true);
    btnYes.parentNode.replaceChild(newBtn, btnYes);

    // Bind click
    newBtn.addEventListener('click', function () {
        onConfirm();
        closeConfirmModal();
    });

    // Show Modal
    modal.classList.add('active');
}

/**
 * Close Confirm Modal
 */
function closeConfirmModal() {
    const modal = document.getElementById('confirmModal');
    modal.classList.remove('active');
}

/**
 * Delete Log Logic
 */
/**
 * Show Custom Toast Notification
 * @param {string} message - Message to display
 * @param {string} type - 'success', 'error', 'info'
 */
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    if (!container) return; // Guard clause

    // Create Toast Element
    const toast = document.createElement('div');
    toast.className = `neon-toast ${type}`;

    // Icons based on type
    let icon = 'fa-check-circle';
    if (type === 'error') icon = 'fa-times-circle';
    if (type === 'info') icon = 'fa-info-circle';

    toast.innerHTML = `
        <i class="fas ${icon}"></i>
        <div class="toast-message">${message}</div>
    `;

    // Append to container
    container.appendChild(toast);

    // Trigger animation
    setTimeout(() => toast.classList.add('show'), 10);

    // Remove after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/**
 * Delete Log Logic
 */
function deleteAuditLog() {
    if (!currentLogId) return;

    showConfirmDialog('هل أنت متأكد من حذف هذا السجل نهائياً؟', function () {
        const btn = document.querySelector('.btn-danger-glass');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الحذف...';
        btn.disabled = true;

        fetch('index.php?page=audit&action=delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: currentLogId })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('تم حذف السجل بنجاح', 'success');
                    closeAuditModal();
                    // Reload to reflect changes
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(data.message || 'حدث خطأ أثناء الحذف', 'error');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('حدث خطأ في الاتصال', 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
    });
}

// Close modal when clicking outside
window.onclick = function (event) {
    const modal = document.getElementById('auditModal');
    if (event.target == modal) {
        closeAuditModal();
    }
}

/**
 * Clear All Logs Logic
 */
function clearAllLogs() {
    showConfirmDialog('⚠️ تحذير: هذا الإجراء سيحذف جميع السجلات نهائياً! هل أنت متأكد؟', function () {
        const btn = document.querySelector('button[onclick="clearAllLogs()"]');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الحذف...';
        btn.disabled = true;

        fetch('index.php?page=audit&action=clear', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('تم تفريغ السجل بالكامل بنجاح', 'success');
                    // Reload after delay
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(data.message || 'حدث خطأ أثناء التفريغ', 'error');
                    btn.innerHTML = originalHTML;
                    btn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('حدث خطأ في الاتصال', 'error');
                btn.innerHTML = originalHTML;
                btn.disabled = false;
            });
    });
}
