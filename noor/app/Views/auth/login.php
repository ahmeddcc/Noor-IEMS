<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="نظام Noor - نظام إدارة الوارد والمنصرف لتجارة الأسماك">
    <meta name="theme-color" content="#0a1628">
    <title>تسجيل الدخول - <?php echo \App\Models\Setting::get('company_name', 'Noor'); ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?php echo asset('css/auth.css'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">

    <!-- Ocean Background with Waves -->
    <div class="ocean-bg">
        <div class="wave-container">
            <svg class="waves" xmlns="http://www.w3.org/2000/svg" viewBox="0 24 150 28" preserveAspectRatio="none">
                <defs>
                    <path id="wave" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z"/>
                </defs>
                <g class="wave-parallax">
                    <use href="#wave" x="48" y="0" fill="rgba(6, 182, 212, 0.15)"/>
                    <use href="#wave" x="48" y="3" fill="rgba(6, 182, 212, 0.10)"/>
                    <use href="#wave" x="48" y="5" fill="rgba(6, 182, 212, 0.08)"/>
                    <use href="#wave" x="48" y="7" fill="rgba(6, 182, 212, 0.05)"/>
                </g>
            </svg>
        </div>
    </div>

    <!-- Glass Card -->
    <div class="glass-card">
        <!-- Snake Laser Border SVG -->
        <svg class="card-border-svg" viewBox="0 0 420 500" preserveAspectRatio="none">
            <rect class="laser-line" x="2" y="2" width="416" height="496" rx="22" ry="22"/>
        </svg>
        <!-- Logo Section -->
        <div class="logo-section">
            <div class="logo-wrapper">
                <img src="<?php echo asset('images/fish-logo.png'); ?>" alt="Noor" class="logo-img">
            </div>
        </div>
        
        <!-- Title -->
        <div class="title-section">
            <h1 class="main-title"><?php echo \App\Models\Setting::get('company_name', 'Noor'); ?></h1>
            <p class="sub-title">نظام إدارة الوارد والمنصرف لتجارة الأسماك</p>
        </div>
        
        <!-- Error Messages -->
        <?php 
        $error = \App\Core\Session::getFlash('error');
        if ($error): 
        ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo e($error['message']); ?></span>
            </div>
        <?php endif; ?>

        <?php 
        $timeout = \App\Core\Session::getFlash('timeout');
        if ($timeout): 
        ?>
            <div class="alert alert-warning">
                <i class="fas fa-clock"></i>
                <span><?php echo e($timeout['message']); ?></span>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="<?php echo base_url('index.php?page=login&action=submit'); ?>" method="POST" class="login-form" id="loginForm" autocomplete="off">
            <?php echo \App\Core\Session::csrfField(); ?>
            
            <div class="input-group">
                <input type="text" 
                       id="username" 
                       name="username" 
                       placeholder="اسم المستخدم"
                       autocomplete="off"
                       required>
                <i class="fas fa-user input-icon"></i>
            </div>
            
            <div class="input-group">
                <input type="password" 
                       id="password" 
                       name="password" 
                       placeholder="كلمة المرور"
                       autocomplete="new-password"
                       required>
                <i class="fas fa-lock input-icon"></i>
            </div>

            <button type="submit" class="btn-submit" id="btnLogin">
                تسجيل الدخول
            </button>
        </form>
        
        <!-- Footer -->
        <div class="card-footer">
            <p class="copyright">&copy; <?php echo date('Y'); ?> <?php echo \App\Models\Setting::get('company_name', 'Noor'); ?> - جميع الحقوق محفوظة</p>
        </div>
    </div>

    <script>
        // Form Submit Handler
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('btnLogin');
            btn.classList.add('loading');
            btn.textContent = 'جاري الدخول...';
        });
        
        // Auto-dismiss alerts
        document.querySelectorAll('.alert').forEach(function(alert) {
            setTimeout(function() {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(function() { alert.remove(); }, 500);
            }, 5000);
        });
    </script>

</body>
</html>
