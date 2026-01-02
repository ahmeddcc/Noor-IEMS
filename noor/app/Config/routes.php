<?php
// app/Config/routes.php

/** @var \App\Core\Router $router */

// Define Routes
// $router->add(PageName, ControllerName, DefaultAction, Protected?);

// Auth
$router->add('login', 'AuthController', 'index', false);

// Dashboard
$router->add('dashboard', 'DashboardController', 'index', true);

// Transactions
$router->add('transactions', 'TransactionController', 'index', true);

// Clients
$router->add('clients', 'ClientController', 'index', true);

// Reports
$router->add('reports', 'ReportController', 'index', true);

// Users (Admin only)
$router->add('users', 'UserController', 'index', true);

// Audit Log
$router->add('audit', 'AuditController', 'index', true);

// Settings
$router->add('settings', 'SettingsController', 'index', true);
