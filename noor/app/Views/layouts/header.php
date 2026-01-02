<?php if (!defined('ROOT_PATH')) exit; 

// Security Headers moved to app/init.php 
if (!function_exists('toArabicNum')) {
    function toArabicNum($number) {
        $western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $eastern = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        return str_replace($western, $eastern, strval($number));
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Preconnect: تسريع الاتصال بالخوادم الخارجية -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    <!-- DNS Prefetch: تسريع البحث عن DNS -->
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    
    <title><?php echo $pageTitle ?? \App\Models\Setting::get('company_name', 'النظام'); ?></title>
    
    <!-- Local Fonts (بديل عن Google Fonts) -->
    <link rel="stylesheet" href="<?php echo asset('css/local-fonts.css'); ?>">
    
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
    <!-- Google Fonts (معلق - نستخدم الخطوط المحلية) -->
    <!-- <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet"> -->
    <!-- FontAwesome (Local for Offline Support) -->
    <link rel="stylesheet" href="<?php echo asset('css/fontawesome.min.css'); ?>">
    
    <!-- Core CSS (Variables first, then Main) -->
    <link rel="stylesheet" href="<?php echo asset('css/variables.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/main.css'); ?>">
    <!-- Mobile Responsive CSS -->
    <link rel="stylesheet" href="<?php echo asset('css/mobile.css'); ?>">
    

    <style>
        /* Anti-FOUC: Dark background immediately */
        html, body {
            background: #0a1628 !important;
            color: #fff !important;
        }
        /* DISABLE ALL TRANSITIONS AND ANIMATIONS ON LOAD */
        .main-content,
        .content-wrapper,
        .app-container,
        .transactions-page,
        .trans-header-wrapper,
        .header-action-buttons-neon,
        .stats-bar-neon,
        .stat-card-neon,
        .filter-bar-neon,
        .table-card-neon,
        .neon-table,
        .action-btn,
        main, section,
        div[class*="page"],
        div[class*="container"],
        button, input, select {
            opacity: 1 !important;
            animation: none !important;
            transform: none !important;
            transition: none !important;
        }
    </style>
</head>

<?php
// Define Page Colors (RGB for opacity)
$pageColors = [
    'dashboard'  => ['hex' => '#3b82f6', 'rgb' => '59, 130, 246'],   // Blue
    'transactions' => ['hex' => '#10b981', 'rgb' => '16, 185, 129'], // Emerald
    'clients'    => ['hex' => '#a855f7', 'rgb' => '168, 85, 247'], // Purple
    'reports'    => ['hex' => '#f59e0b', 'rgb' => '245, 158, 11'], // Orange
    'settings'   => ['hex' => '#f43f5e', 'rgb' => '244, 63, 94']   // Rose
];
$currentPage = $page ?? 'dashboard';
$activeColor = $pageColors[$currentPage] ?? $pageColors['dashboard'];
?>
<body style="--active-neon-color: <?php echo $activeColor['hex']; ?>; --active-neon-rgb: <?php echo $activeColor['rgb']; ?>;">

<div class="app-container">
    <!-- Cyber Strip Header Container -->
    <div class="cyber-header-container">
        <header class="cyber-trip">
            
            <!-- RIGHT: Logo Section -->
            <a href="index.php?page=dashboard" class="header-section logo-section" style="text-decoration: none; color: inherit;">
                <!-- Cyber Fish SVG -->
                <svg class="fish-icon" width="40" height="40" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M48 32C52 30 58 24 58 24C58 24 54 28 52 32C54 36 58 40 58 40C58 40 52 34 48 32Z" fill="#38bdf8" stroke="#38bdf8" stroke-width="2" stroke-linejoin="round"/>
                    <path d="M44 32C44 42 34 50 20 46C14 44 8 36 4 32C8 28 14 20 20 18C34 14 44 22 44 32Z" stroke="#38bdf8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="16" cy="30" r="2" fill="#fff" filter="drop-shadow(0 0 2px #fff)"/>
                    <path d="M26 32L34 32" stroke="#38bdf8" stroke-width="2" stroke-linecap="round"/>
                    <path d="M28 26L36 22" stroke="#38bdf8" stroke-width="1.5" stroke-linecap="round" opacity="0.7"/>
                    <path d="M28 38L36 42" stroke="#38bdf8" stroke-width="1.5" stroke-linecap="round" opacity="0.7"/>
                </svg>
                
                <span class="logo-text"><?php echo \App\Models\Setting::get('company_name', 'Noor'); ?></span>
                <div class="v-separator"></div>
            </a>

            <!-- CENTER: Navigation -->
            <!-- Mobile Hamburger Button -->
            <button class="mobile-menu-toggle" id="mobileMenuToggle" onclick="toggleMobileMenu()" aria-label="قائمة التنقل">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
            
            <nav class="header-section nav-section" id="mainNav">
                <?php if(\App\Core\Session::hasPermission('dashboard.view')): ?>
                <!-- Dashboard (Home) - Blue -->
                <a href="index.php?page=dashboard" class="nav-item <?php echo ($page??'')=='dashboard'?'active':''; ?>" style="--i:1; --neon-color: #3b82f6;">
                    <svg class="dock-svg"><rect class="dock-rect" x="2" y="2" width="66" height="56" rx="8" ry="8"></rect></svg>
                    <i class="fas fa-home"></i>
                    <span class="nav-label">الرئيسية</span>
                </a>
                <?php endif; ?>

                <?php if(\App\Core\Session::hasPermission('transactions.view')): ?>
                <!-- Transactions (Wallet) - Emerald Green -->
                <a href="index.php?page=transactions" class="nav-item <?php echo ($page??'')=='transactions'?'active':''; ?>" style="--i:2; --neon-color: #10b981;">
                    <svg class="dock-svg"><rect class="dock-rect" x="2" y="2" width="66" height="56" rx="8" ry="8"></rect></svg>
                    <i class="fas fa-wallet"></i>
                    <span class="nav-label">المعاملات</span>
                </a>
                <?php endif; ?>

                <?php if(\App\Core\Session::hasPermission('clients.view')): ?>
                <!-- Clients (Users) - Purple -->
                <a href="index.php?page=clients" class="nav-item <?php echo ($page??'')=='clients'?'active':''; ?>" style="--i:3; --neon-color: #a855f7;">
                    <svg class="dock-svg"><rect class="dock-rect" x="2" y="2" width="66" height="56" rx="8" ry="8"></rect></svg>
                    <i class="fas fa-users"></i>
                    <span class="nav-label">العملاء</span>
                </a>
                <?php endif; ?>

                <?php if(\App\Core\Session::hasPermission('reports.view')): ?>
                <!-- Reports (Chart) - Orange -->
                <a href="index.php?page=reports" class="nav-item <?php echo ($page??'')=='reports'?'active':''; ?>" style="--i:4; --neon-color: #f59e0b;">
                    <svg class="dock-svg"><rect class="dock-rect" x="2" y="2" width="66" height="56" rx="8" ry="8"></rect></svg>
                    <i class="fas fa-chart-pie"></i>
                    <span class="nav-label">التقارير</span>
                </a>
                <?php endif; ?>

                <?php if(\App\Core\Session::hasPermission('audit.view')): ?>
                <!-- Audit (History) - Cyan -->
                <a href="index.php?page=audit" class="nav-item <?php echo ($page??'')=='audit'?'active':''; ?>" style="--i:4.5; --neon-color: #06b6d4;">
                    <svg class="dock-svg"><rect class="dock-rect" x="2" y="2" width="66" height="56" rx="8" ry="8"></rect></svg>
                    <i class="fas fa-history"></i>
                    <span class="nav-label">السجل</span>
                </a>
                <?php endif; ?>

                <?php if(\App\Core\Session::hasPermission('users.view')): ?>
                <!-- Users (Admin) - Purple -->
                <a href="index.php?page=users" class="nav-item <?php echo ($page??'')=='users'?'active':''; ?>" style="--i:5; --neon-color: #a855f7;">
                    <svg class="dock-svg"><rect class="dock-rect" x="2" y="2" width="66" height="56" rx="8" ry="8"></rect></svg>
                    <i class="fas fa-users-cog"></i>
                    <span class="nav-label">المستخدمون</span>
                </a>
                <?php endif; ?>

                <?php if(\App\Core\Session::hasAnyPermission(['settings.general', 'settings.categories', 'settings.backup', 'settings.telegram'])): ?>
                <!-- Settings (Cogs) - Rose -->
                <a href="index.php?page=settings" class="nav-item <?php echo ($page??'')=='settings'?'active':''; ?>" style="--i:6; --neon-color: #f43f5e;">
                    <svg class="dock-svg"><rect class="dock-rect" x="2" y="2" width="66" height="56" rx="8" ry="8"></rect></svg>
                    <i class="fas fa-cogs"></i>
                    <span class="nav-label">الإعدادات</span>
                </a>
                
                <?php if(\App\Core\Session::hasPermission('settings.backup')): ?>
                <!-- Separator -->
                <span class="nav-separator" style="color: rgba(255,255,255,0.2); font-size: 1.5rem; margin: 0 5px; font-weight: 100;">|</span>

                <!-- Quick Backup (Cloud) - Sky Blue -->
                <a href="index.php?page=settings&action=quickBackup" class="nav-item" style="--i:6; --neon-color: #38bdf8;" title="نسخ احتياطي سريع">
                    <svg class="dock-svg"><rect class="dock-rect" x="2" y="2" width="66" height="56" rx="8" ry="8"></rect></svg>
                    <i class="fas fa-cloud-download-alt"></i>
                    <span class="nav-label">نسخ سريع</span>
                </a>
                <?php endif; ?>
                <?php endif; ?>
            </nav>

            <!-- LEFT: Profile Section -->
            <div class="header-section profile-section">
                <div class="v-separator"></div>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars(\App\Core\Session::get('username') ?? 'مستخدم'); ?></span>
                    <?php 
                    $roleLabel = match(\App\Core\Session::get('role')) {
                        'admin' => 'مدير',
                        'manager' => 'مشرف',
                        default => 'مستخدم'
                    };
                    ?>
                    <span class="user-role-badge"><?php echo $roleLabel; ?></span>
                </div>
                <a href="index.php?page=login&action=logout" class="logout-btn" title="تسجيل خروج">
                    <i class="fas fa-power-off"></i>
                </a>
            </div>

        </header>
    </div>

    <!-- Main Content -->
    <main class="main-content">
        
        <div class="content-wrapper">
            <!-- Flash Messages -->
            <?php 
            $flash = \App\Core\Session::getFlash('success');
            if ($flash): ?>
                <div class="alert alert-success"><?php echo e($flash['message']); ?></div>
            <?php endif; ?>
            
            <?php 
            $error = \App\Core\Session::getFlash('error');
            if ($error): ?>
                <div class="alert alert-error"><?php echo e($error['message']); ?></div>
            <?php endif; ?>
