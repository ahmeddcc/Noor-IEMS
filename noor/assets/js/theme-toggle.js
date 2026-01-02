/**
 * Theme Toggle Script
 * التبديل بين الوضع الليلي والنهاري
 * - يحفظ تفضيل المستخدم في localStorage
 * - يتبدل تلقائياً حسب وقت الجهاز (6ص-6م = نهار)
 */

(function() {
    'use strict';

    const STORAGE_KEY = 'theme-preference';
    const LIGHT_MODE_CLASS = 'light-mode';
    
    // تحديد الوضع حسب الوقت (6 صباحاً - 6 مساءً = نهار)
    function getTimeBasedTheme() {
        const hour = new Date().getHours();
        return (hour >= 6 && hour < 18) ? 'light' : 'dark';
    }

    // الحصول على التفضيل المحفوظ أو استخدام الوقت
    function getStoredTheme() {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored === 'light' || stored === 'dark') {
            return stored;
        }
        // إذا لا يوجد تفضيل محفوظ، استخدم الوقت
        return null;
    }

    // تطبيق الوضع
    function applyTheme(theme) {
        const body = document.body;
        const icon = document.getElementById('themeIcon');
        const btn = document.getElementById('themeToggleBtn');
        
        if (theme === 'light') {
            body.classList.add(LIGHT_MODE_CLASS);
            if (icon) {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        } else {
            body.classList.remove(LIGHT_MODE_CLASS);
            if (icon) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            }
        }

        // إضافة animation للزر
        if (btn) {
            btn.classList.add('switching');
            setTimeout(() => btn.classList.remove('switching'), 500);
        }
    }

    // تبديل الوضع (يستدعيها الزر)
    window.toggleTheme = function() {
        const body = document.body;
        const isLight = body.classList.contains(LIGHT_MODE_CLASS);
        const newTheme = isLight ? 'dark' : 'light';
        
        // حفظ التفضيل
        localStorage.setItem(STORAGE_KEY, newTheme);
        
        // تطبيق التغيير
        applyTheme(newTheme);
    };

    // تهيئة الوضع عند تحميل الصفحة
    function initTheme() {
        const storedTheme = getStoredTheme();
        const theme = storedTheme || getTimeBasedTheme();
        applyTheme(theme);
    }

    // تشغيل التهيئة عند تحميل الصفحة
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTheme);
    } else {
        initTheme();
    }

    // التحقق كل ساعة للتبديل التلقائي (إذا لم يكن هناك تفضيل محفوظ)
    setInterval(function() {
        if (!getStoredTheme()) {
            const timeTheme = getTimeBasedTheme();
            const body = document.body;
            const currentTheme = body.classList.contains(LIGHT_MODE_CLASS) ? 'light' : 'dark';
            
            if (timeTheme !== currentTheme) {
                applyTheme(timeTheme);
            }
        }
    }, 60 * 60 * 1000); // كل ساعة

})();
