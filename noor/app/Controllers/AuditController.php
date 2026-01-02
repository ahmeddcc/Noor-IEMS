<?php
// app/Controllers/AuditController.php

namespace App\Controllers;

use App\Core\Session;
use App\Models\AuditLog;
use App\Models\User;

class AuditController {
    
    public function __construct() {
        Session::check();
        if (!Session::hasPermission('audit.view')) {
            Session::setFlash('error', 'ليس لديك صلاحية للوصول لسجل العمليات');
            redirect('index.php?page=dashboard');
        }
    }
    
    public function index() {
        $auditModel = new AuditLog();
        $userModel = new User();
        
        $page = isset($_GET['p']) && is_numeric($_GET['p']) ? (int)$_GET['p'] : 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        $filters = [
            'user_id' => $_GET['user_id'] ?? '',
            'date_from' => $_GET['from'] ?? '',
            'date_to' => $_GET['to'] ?? '',
            'action' => $_GET['action_type'] ?? ''
        ];
        
        $logs = $auditModel->getLogs($limit, $offset, $filters);
        $totalRecords = $auditModel->getCount($filters);
        $totalPages = ceil($totalRecords / $limit);
        
        $users = $userModel->getAll();
        
        $pageTitle = 'سجل العمليات';
        $page = 'audit';
        require_once ROOT_PATH . '/app/Views/layouts/header.php';
        require_once ROOT_PATH . '/app/Views/audit/index.php';
        require_once ROOT_PATH . '/app/Views/layouts/footer.php';
    }
    public function delete() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid Request Method']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Missing ID']);
            return;
        }
        
        $auditModel = new AuditLog();
        if ($auditModel->delete($id)) {
            // Log the deletion action itself!
            AuditLog::log(Session::get('user_id'), 'DELETE', 'audit_log', "حذف سجل العمليات بمعرف: $id");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database Error']);
        }
    }

    public function clear() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid Request Method']);
            return;
        }
        
        $auditModel = new AuditLog();
        if ($auditModel->clearAll()) {
            AuditLog::log(Session::get('user_id'), 'DELETE', 'audit_log', "تفريغ سجل العمليات بالكامل");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database Error']);
        }
    }
}
