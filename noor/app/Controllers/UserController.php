<?php
// app/Controllers/UserController.php

namespace App\Controllers;

use App\Core\Session;
use App\Models\User;
use App\Models\Permission;

class UserController {
    private $userModel;
    private $permissionModel;

    public function __construct() {
        Session::check();
        
        // التحقق من صلاحية الوصول
        if (!Session::hasPermission('users.view')) {
            Session::setFlash('error', 'ليس لديك صلاحية للوصول لهذه الصفحة');
            redirect('index.php?page=dashboard');
        }
        
        $this->userModel = new User();
        $this->permissionModel = new Permission();
    }

    /**
     * صفحة عرض المستخدمين
     */
    public function index() {
        $users = $this->userModel->getAll();
        $permissions = $this->permissionModel->getAllGroupedByCategory();
        $categoryLabels = Permission::getCategoryLabels();
        
        // جلب صلاحيات كل مستخدم
        foreach ($users as &$user) {
            $user['permissions'] = $this->userModel->getPermissions($user['id']);
        }
        unset($user); // كسر الارتباط (Reference) لتجنب مشاكل التكرار في العرض
        
        $pageTitle = 'إدارة المستخدمين';
        $page = 'users';
        require_once ROOT_PATH . '/app/Views/layouts/header.php';
        require_once ROOT_PATH . '/app/Views/users/index.php';
        require_once ROOT_PATH . '/app/Views/layouts/footer.php';
    }

    /**
     * جلب بيانات مستخدم (AJAX)
     */
    public function ajaxGet() {
        header('Content-Type: application/json; charset=utf-8');
        
        $id = $_GET['id'] ?? 0;
        $user = $this->userModel->getUserById($id);
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'المستخدم غير موجود']);
            exit;
        }
        
        $user['permissions'] = $this->userModel->getPermissions($id);
        
        echo json_encode(['success' => true, 'user' => $user]);
        exit;
    }

    /**
     * حفظ مستخدم (إضافة/تعديل) - AJAX
     */
    public function ajaxSave() {
        header('Content-Type: application/json; charset=utf-8');
        
        // التحقق من صلاحية الإدارة
        if (!Session::hasPermission('users.manage')) {
            echo json_encode(['success' => false, 'message' => 'ليس لديك صلاحية لإدارة المستخدمين']);
            exit;
        }
        
        // التحقق من CSRF
        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'خطأ في التحقق من الأمان']);
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';
        $status = $_POST['status'] ?? 'active';
        $permissions = $_POST['permissions'] ?? [];
        
        // التحقق من البيانات
        if (empty($username)) {
            echo json_encode(['success' => false, 'message' => 'اسم المستخدم مطلوب']);
            exit;
        }
        
        // التحقق من تكرار اسم المستخدم
        if ($this->userModel->usernameExists($username, $id ?: null)) {
            echo json_encode(['success' => false, 'message' => 'اسم المستخدم موجود بالفعل']);
            exit;
        }
        
        // التحقق من صحة الدور
        if (!in_array($role, ['admin', 'manager', 'user'])) {
            $role = 'user';
        }
        
        // بدء Transaction
        $pdo = \App\Core\Database::getInstance()->getConnection();
        
        try {
            $pdo->beginTransaction();

            if ($id) {
                // تعديل
                $data = [
                    'username' => $username,
                    'role' => $role,
                    'status' => $status
                ];
                
                if (!empty($password)) {
                    $data['password'] = $password;
                }
                
                $this->userModel->update($id, $data);
                $userId = $id;
            } else {
                // إضافة
                if (empty($password)) {
                    throw new \Exception('كلمة المرور مطلوبة للمستخدم الجديد');
                }
                
                $userId = $this->userModel->create([
                    'username' => $username,
                    'password' => $password,
                    'role' => $role,
                    'status' => $status
                ]);
            }
            
            // تعيين الصلاحيات
            if ($role === 'admin') {
                // المدير يحصل على كل الصلاحيات في قاعدة البيانات أيضاً لضمان التوافق
                $this->permissionModel->grantAllPermissions($userId);
            } else {
                $permIds = array_map('intval', $permissions);
                $this->permissionModel->setUserPermissions($userId, $permIds);
            }
            
            $pdo->commit();

            // إذا قام المستخدم الحالي بتعديل بياناته، نقوم بتحديث جلسة الصلاحيات فوراً
            if ($userId == Session::get('user_id')) {
                Session::loadPermissions($userId);
                Session::set('username', $username);
                Session::set('role', $role);
            }

            echo json_encode(['success' => true, 'message' => $id ? 'تم تحديث المستخدم' : 'تم إضافة المستخدم']);
            
        } catch (\Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo json_encode(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()]);
        }
        
        exit;
    }

    /**
     * حذف مستخدم - AJAX
     */
    /**
     * حذف مستخدم - AJAX
     */
    public function ajaxDelete() {
        header('Content-Type: application/json; charset=utf-8');
        
        // التحقق من صلاحية الإدارة
        if (!Session::hasPermission('users.manage')) {
            echo json_encode(['success' => false, 'message' => 'ليس لديك صلاحية لحذف المستخدمين']);
            exit;
        }
        
        // التحقق من CSRF
        if (!Session::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'خطأ في التحقق من الأمان']);
            exit;
        }
        
        $id = $_POST['id'] ?? 0;
        
        // لا يمكن حذف نفسك
        if ($id == Session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'لا يمكنك حذف حسابك الحالي']);
            exit;
        }
        
        $result = $this->userModel->delete($id);
        
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'تم حذف المستخدم']);
        } else {
            echo json_encode(['success' => false, 'message' => $result['message']]);
        }
        
        exit;
    }

    public function bulkDelete() {
        header('Content-Type: application/json; charset=utf-8');
        
        // 1. Check Security Setting
        $isEnabled = \App\Models\Setting::get('enable_bulk_delete', '0');
        if ($isEnabled !== '1') {
            echo json_encode(['success' => false, 'message' => 'الحذف الجماعي معطل من الإعدادات']);
            exit;
        }

        // 2. Check Permissions
        if (!Session::hasPermission('users.manage') || !Session::isManager()) {
            echo json_encode(['success' => false, 'message' => 'ليس لديك صلاحية الحذف الجماعي للمستخدمين']);
            exit;
        }

        // 3. Validate Input
        $input = json_decode(file_get_contents('php://input'), true);
        $ids = $input['ids'] ?? [];
        
        if (empty($ids) || !is_array($ids)) {
            echo json_encode(['success' => false, 'message' => 'لم يتم تحديد أي عناصر']);
            exit;
        }

        // 4. Execute Delete
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids, function($id) { return $id > 0; });
        
        if (empty($ids)) {
            echo json_encode(['success' => false, 'message' => 'لا توجد عناصر صالحة للحذف']);
            exit;
        }

        $deletedCount = 0;
        $failedCount = 0;
        $currentUserId = Session::get('user_id');

        foreach ($ids as $id) {
            // Self-delete check
            if ($id == $currentUserId) {
                $failedCount++;
                continue;
            }

            $result = $this->userModel->delete($id);
            if ($result['success']) {
                $deletedCount++;
            } else {
                $failedCount++;
            }
        }
        
        if ($deletedCount > 0) {
            $msg = "تم حذف $deletedCount مستخدم بنجاح.";
            if ($failedCount > 0) {
                $msg .= " وفشل حذف $failedCount (بسبب ارتباطات أو محاولة حذف النفس/المدير الأخير).";
            }
            echo json_encode(['success' => true, 'message' => $msg]);
        } else {
            echo json_encode(['success' => false, 'message' => 'فشل حذف المستخدمين المحددين (محاولة حذف النفس أو المدير الأخير)']);
        }
        exit;
    }
}
