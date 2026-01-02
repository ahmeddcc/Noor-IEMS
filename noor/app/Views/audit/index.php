<?php
// app/Views/audit/index.php
?>
<link rel="stylesheet" href="<?php echo asset('css/audit.css'); ?>?v=<?php echo time(); ?>">

<div class="neon-hero-header page-header" style="border: 1px solid #3b82f6 !important;">
    <div class="neon-title-group">
        <i class="fas fa-history neon-title-icon"></i>
        <div class="neon-title-text">
            سجل العمليات
            <small>مراقبة وتتبع جميع الحركات والإجراءات في النظام</small>
        </div>
    </div>
    
    <div class="neon-header-actions">
        <button type="button" onclick="clearAllLogs()" class="btn-smart-filter" style="background: linear-gradient(135deg, var(--neon-red), #991b1b); border-color: var(--neon-red); box-shadow: 0 0 15px rgba(244, 63, 94, 0.3);">
            <i class="fas fa-trash-alt"></i> تفريغ السجل
        </button>
    </div>
</div>

<div class="audit-container">
    <!-- Filters Bar -->
    <!-- Smart Filter Bar (Glass Bar Horizontal) -->
    <div class="smart-audit-bar">
        <form method="GET" action="index.php" class="smart-audit-form">
            <input type="hidden" name="page" value="audit">
            
            <div class="smart-input-group">
                <i class="fas fa-user input-icon"></i>
                <select name="user_id" class="smart-select">
                    <option value="">كل المستخدمين</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?php echo $u['id']; ?>" <?php echo ($filters['user_id'] == $u['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($u['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="smart-divider"></div>

            <div class="smart-input-group date-group">
                <span class="date-label">من:</span>
                <input type="date" name="from" class="smart-date" value="<?php echo $filters['date_from']; ?>">
            </div>

            <div class="smart-input-group date-group">
                <span class="date-label">إلى:</span>
                <input type="date" name="to" class="smart-date" value="<?php echo $filters['date_to']; ?>">
            </div>
            
            <div class="smart-actions">
                <button type="submit" class="btn-smart-filter">
                    <i class="fas fa-filter"></i> تصفية
                </button>
                
                <a href="index.php?page=audit" class="btn-smart-reset" title="إعادة تعيين">
                    <i class="fas fa-redo-alt"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <!-- Logs Grid (Cards System) -->
    <div class="audit-grid">
        <?php if (!empty($logs)): ?>
            <?php foreach ($logs as $log): ?>
                <div class="audit-card" onclick="openAuditModal(<?php echo htmlspecialchars(json_encode($log)); ?>)">
                    
                    <div class="card-header">
                        <div class="user-info">
                            <div class="user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="user-meta">
                                <span class="user-name"><?php echo htmlspecialchars($log['user_name'] ?? 'نظام'); ?></span>
                                <span class="user-role">مشرف عام</span>
                            </div>
                        </div>
                        <span class="action-badge-card <?php echo strtolower($log['action']); ?>">
                            <?php echo htmlspecialchars($log['action']); ?>
                        </span>
                    </div>

                    <div class="card-body">
                        <p class="log-summary">
                            <span class="log-target"><?php echo htmlspecialchars($log['target']); ?></span>
                            <?php 
                                // Generate a brief summary based on action
                                $summary = '';
                                if(strpos($log['details'], 'قام') !== false) {
                                    $summary = substr($log['details'], 0, 50) . '...';
                                } else {
                                    $summary = $log['details'];
                                }
                                echo htmlspecialchars(mb_strimwidth($summary, 0, 60, "...")); 
                            ?>
                        </p>
                    </div>

                    <div class="card-footer">
                        <span class="card-time">
                            <i class="far fa-clock"></i>
                            <?php echo date('d-m-Y h:i A', strtotime($log['timestamp'])); ?>
                        </span>
                        <span class="view-hint">اضغط للتفاصيل</span>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <p>لا توجد عمليات مسجلة تطابق التصفية</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Details Modal -->
    <div id="auditModal" class="audit-modal-overlay">
        <div class="audit-modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-info-circle"></i> تفاصيل العملية</h3>
                <button class="close-modal" onclick="closeAuditModal()">&times;</button>
            </div>
            <div class="modal-body" id="auditModalBody">
                <!-- Content injected via JS -->
            </div>
            <div class="modal-footer">
                <button class="btn-danger-glass" onclick="deleteAuditLog()">
                    <i class="fas fa-trash"></i> حذف السجل
                </button>
                <button class="btn-secondary-glass" onclick="closeAuditModal()">إغلاق</button>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="audit-modal-overlay">
        <div class="confirm-modal-content">
            <div class="confirm-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3>تأكيد الإجراء</h3>
            <p id="confirmMessage">هل أنت متأكد من تنفيذ هذا الإجراء؟</p>
            <div class="confirm-actions">
                <button id="btnConfirmYes" class="btn-danger-glass">نعم، تنفيذ</button>
                <button onclick="closeConfirmModal()" class="btn-secondary-glass">إلغاء</button>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer" class="toast-container"></div>

    <!-- Include Audit JS -->
    <script src="<?php echo asset('js/audit.js'); ?>?v=<?php echo time(); ?>"></script>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="index.php?page=audit&p=<?php echo $i; ?>&user_id=<?php echo $filters['user_id']; ?>&from=<?php echo $filters['date_from']; ?>&to=<?php echo $filters['date_to']; ?>" 
                   class="page-link <?php echo ($page == $i) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
