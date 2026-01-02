<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="نظام Noor - نظام إدارة الوارد والمنصرف لتجارة الأسماك">
    <meta name="theme-color" content="#0a1628">
    <title>تسجيل الخروج - <?php echo \App\Models\Setting::get('company_name', 'Noor'); ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?php echo asset('css/auth.css'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .logout-icon {
            font-size: 4rem;
            color: var(--neon-cyan);
            margin: 20px 0;
            filter: drop-shadow(0 0 15px rgba(6, 182, 212, 0.4));
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); filter: drop-shadow(0 0 15px rgba(6, 182, 212, 0.4)); }
            50% { transform: scale(1.1); filter: drop-shadow(0 0 25px rgba(6, 182, 212, 0.6)); }
            100% { transform: scale(1); filter: drop-shadow(0 0 15px rgba(6, 182, 212, 0.4)); }
        }

        .session-info {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 15px;
            margin: 20px 0;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
        }

        .btn-return {
            background: linear-gradient(135deg, var(--neon-cyan), #0ea5e9);
            color: white;
            padding: 14px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            border: none;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(6, 182, 212, 0.3);
        }

        .btn-return:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(6, 182, 212, 0.5);
            background: linear-gradient(135deg, #0ea5e9, var(--neon-cyan));
        }

        /* Countdown circle styles could be added here if needed */
    </style>
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
    <div class="glass-card" style="max-width: 400px;">
        <!-- Snake Laser Border SVG -->
        <svg class="card-border-svg" viewBox="0 0 420 500" preserveAspectRatio="none">
            <rect class="laser-line" x="2" y="2" width="416" height="496" rx="22" ry="22"/>
        </svg>
        
        <!-- Content -->
        <div style="text-align: center; padding: 20px 0;">
            <i class="fas fa-shield-alt logout-icon"></i>
            
            <h1 class="main-title" style="margin-bottom: 5px;">تم تسجيل الخروج بنجاح</h1>
            <p class="sub-title" style="margin-top: 5px;">نراك قريباً في <?php echo \App\Models\Setting::get('company_name', 'Noor'); ?></p>
            
            <?php if(isset($duration) && $duration): ?>
            <div class="session-info">
                <i class="fas fa-clock" style="margin-left: 5px; color: var(--neon-cyan);"></i>
                مدة الجلسة: <strong><?php echo $duration; ?></strong>
            </div>
            <?php endif; ?>

            <a href="index.php?page=login" class="btn-return">
                <i class="fas fa-sign-in-alt"></i>
                تسجيل الدخول مجدداً
            </a>
            
            <p style="margin-top: 20px; font-size: 0.85rem; color: rgba(255,255,255,0.5);">
                سيتم تحويلك تلقائياً خلال <span id="countdown">5</span> ثوانٍ...
            </p>
        </div>
        
        <!-- Footer -->
        <div class="card-footer">
            <p class="copyright">&copy; <?php echo date('Y'); ?> <?php echo \App\Models\Setting::get('company_name', 'Noor'); ?> - جميع الحقوق محفوظة</p>
        </div>
    </div>

    <script>
        // Auto Redirect
        let count = 5;
        const countdownEl = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            count--;
            if(countdownEl) countdownEl.innerText = count;
            
            if (count <= 0) {
                clearInterval(timer);
                window.location.href = 'index.php?page=login';
            }
        }, 1000);
    </script>

</body>
</html>
