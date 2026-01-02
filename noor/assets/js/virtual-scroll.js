/**
 * Virtual Scroll Module - Phase 12
 * مكتبة التمرير الافتراضي للجداول الكبيرة
 * يُفعَّل تلقائياً إذا كان عدد الصفوف > 50
 */

(function () {
    'use strict';

    // إعدادات Virtual Scroll
    const CONFIG = {
        rowHeight: 60,          // ارتفاع الصف بالبكسل
        bufferSize: 5,          // عدد الصفوف الإضافية للتحميل المسبق
        minRowsToActivate: 50,  // الحد الأدنى لتفعيل Virtual Scroll
        tableSelector: '#transactionsTable',
        tbodySelector: '#transactionsTable tbody'
    };

    // حالة Virtual Scroll
    let virtualState = {
        isActive: false,
        allRows: [],
        visibleStart: 0,
        visibleEnd: 0,
        containerHeight: 0
    };

    /**
     * تهيئة Virtual Scroll
     */
    function initVirtualScroll() {
        const tbody = document.querySelector(CONFIG.tbodySelector);
        if (!tbody) return;

        const rows = tbody.querySelectorAll('tr:not(.empty-state)');

        // التحقق من الحد الأدنى
        if (rows.length < CONFIG.minRowsToActivate) {
            console.log(`[VirtualScroll] عدد الصفوف (${rows.length}) أقل من ${CONFIG.minRowsToActivate}. لم يتم التفعيل.`);
            return;
        }

        console.log(`[VirtualScroll] تفعيل Virtual Scroll لـ ${rows.length} صف`);

        // حفظ جميع الصفوف
        virtualState.allRows = Array.from(rows);
        virtualState.isActive = true;

        // إخفاء جميع الصفوف مبدئياً
        virtualState.allRows.forEach(row => {
            row.style.display = 'none';
            row.dataset.vsIndex = virtualState.allRows.indexOf(row);
        });

        // إنشاء حاوية التمرير
        setupScrollContainer(tbody);

        // رسم الصفوف المرئية
        renderVisibleRows();

        // ربط أحداث التمرير
        const scrollContainer = document.querySelector('.vs-scroll-container');
        if (scrollContainer) {
            scrollContainer.addEventListener('scroll', throttle(renderVisibleRows, 16));
        }
    }

    /**
     * إعداد حاوية التمرير
     */
    function setupScrollContainer(tbody) {
        const table = tbody.closest('table');
        const wrapper = document.createElement('div');
        wrapper.className = 'vs-scroll-container';
        wrapper.style.cssText = `
            max-height: 600px;
            overflow-y: auto;
            position: relative;
        `;

        // حساب الارتفاع الكلي
        const totalHeight = virtualState.allRows.length * CONFIG.rowHeight;
        const spacer = document.createElement('div');
        spacer.className = 'vs-spacer';
        spacer.style.height = totalHeight + 'px';
        spacer.style.position = 'absolute';
        spacer.style.width = '1px';
        spacer.style.top = '0';
        spacer.style.left = '0';
        spacer.style.pointerEvents = 'none';

        // تغليف الجدول
        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);
        wrapper.appendChild(spacer);

        virtualState.containerHeight = wrapper.clientHeight;
    }

    /**
     * رسم الصفوف المرئية فقط
     */
    function renderVisibleRows() {
        if (!virtualState.isActive) return;

        const container = document.querySelector('.vs-scroll-container');
        if (!container) return;

        const scrollTop = container.scrollTop;
        const viewportHeight = container.clientHeight;

        // حساب نطاق الصفوف المرئية
        const startIndex = Math.max(0, Math.floor(scrollTop / CONFIG.rowHeight) - CONFIG.bufferSize);
        const endIndex = Math.min(
            virtualState.allRows.length,
            Math.ceil((scrollTop + viewportHeight) / CONFIG.rowHeight) + CONFIG.bufferSize
        );

        // إخفاء الصفوف خارج النطاق
        virtualState.allRows.forEach((row, index) => {
            if (index >= startIndex && index < endIndex) {
                row.style.display = '';
                row.style.transform = `translateY(${index * CONFIG.rowHeight}px)`;
                row.style.position = 'absolute';
                row.style.width = '100%';
            } else {
                row.style.display = 'none';
            }
        });

        virtualState.visibleStart = startIndex;
        virtualState.visibleEnd = endIndex;
    }

    /**
     * Throttle function
     */
    function throttle(func, limit) {
        let inThrottle;
        return function () {
            if (!inThrottle) {
                func.apply(this, arguments);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    /**
     * تعطيل Virtual Scroll
     */
    function disableVirtualScroll() {
        if (!virtualState.isActive) return;

        virtualState.allRows.forEach(row => {
            row.style.display = '';
            row.style.transform = '';
            row.style.position = '';
        });

        const wrapper = document.querySelector('.vs-scroll-container');
        if (wrapper) {
            const table = wrapper.querySelector('table');
            wrapper.parentNode.insertBefore(table, wrapper);
            wrapper.remove();
        }

        virtualState.isActive = false;
        console.log('[VirtualScroll] تم تعطيل Virtual Scroll');
    }

    // تصدير الدوال للاستخدام الخارجي
    window.VirtualScroll = {
        init: initVirtualScroll,
        disable: disableVirtualScroll,
        isActive: () => virtualState.isActive,
        config: CONFIG
    };

    // تفعيل تلقائي عند تحميل الصفحة
    document.addEventListener('DOMContentLoaded', function () {
        // تأخير بسيط للتأكد من اكتمال تحميل الجدول
        setTimeout(initVirtualScroll, 100);
    });

})();
