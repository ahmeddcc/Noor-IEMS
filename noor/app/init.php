<?php
// app/init.php

// 1. Load Configuration
require_once __DIR__ . '/../config.php';

// 2. Register Autoloader
spl_autoload_register(function ($class) {
    // Prefix for our app classes
    $prefix = 'App\\';
    
    // Base directory for the namespace prefix
    $base_dir = __DIR__ . '/';
    
    // Does the class use the prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }
    
    // Get the relative class name
    $relative_class = substr($class, $len);
    
    // Replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

// 3. Global Error Handlers
set_error_handler(function($severity, $message, $file, $line) {
    // Check if ErrorAnalyzer class exists (autoloaded)
    if (class_exists('\\App\\Core\\ErrorAnalyzer') && class_exists('\\App\\Core\\TelegramNotifier')) {
        $suggestion = \App\Core\ErrorAnalyzer::analyze($message);
        \App\Core\TelegramNotifier::notifyError($message, $file, $line, '', $suggestion);
    }
    return false; // Continue with normal error handling
});

set_exception_handler(function($exception) {
    if (class_exists('\\App\\Core\\ErrorAnalyzer') && class_exists('\\App\\Core\\TelegramNotifier')) {
        $suggestion = \App\Core\ErrorAnalyzer::analyze($exception->getMessage());
        \App\Core\TelegramNotifier::notifyError(
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString(),
            $suggestion
        );
    }
    throw $exception; // Re-throw to show error page
});

// 4. Start Session if not started
\App\Core\Session::start();

// 5. Centralized Security Headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
// Modern Clickjacking Protection
header("Content-Security-Policy: frame-ancestors 'none';");
