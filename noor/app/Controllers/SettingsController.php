<?php
// app/Controllers/SettingsController.php

namespace App\Controllers;

use App\Core\Session;
use App\Core\Database;
use App\Models\Category;

class SettingsController {
    private $db;

    public function __construct() {
        Session::check();
        // التحقق من أن المستخدم لديه على الأقل صلاحية واحدة من الإعدادات
        if (!Session::hasAnyPermission(['settings.general', 'settings.categories', 'settings.backup', 'settings.telegram'])) {
            Session::setFlash('error', 'ليس لديك صلاحية للوصول للإعدادات');
            redirect('index.php?page=dashboard');
        }
        $this->db = Database::getInstance();
    }
    
    public function index() {
        // جلب الإعدادات الحالية
        $settings = [];
        $rows = $this->db->fetchAll("SELECT * FROM settings");
        foreach ($rows as $row) {
            $settings[$row['key']] = $row['value'];
        }

        $categoryModel = new Category();
        $categories = $categoryModel->getAll();
        
        // جلب قائمة النسخ الاحتياطية
        $backups = $this->getBackupFiles();

        $pageTitle = 'الإعدادات المتقدمة';
        $page = 'settings';
        require_once ROOT_PATH . '/app/Views/layouts/header.php';
        require_once ROOT_PATH . '/app/Views/settings/index.php';
        require_once ROOT_PATH . '/app/Views/layouts/footer.php';
    }

    public function save() {
        if (!Session::hasPermission('settings.general')) {
            Session::setFlash('error', 'ليس لديك صلاحية لتعديل الإعدادات العامة');
            redirect('index.php?page=settings');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                redirect('index.php?page=settings');
            }

            $keys = ['company_name', 'company_address', 'max_login_attempts', 'session_timeout', 'enable_bulk_delete'];
            foreach ($keys as $key) {
                if (isset($_POST[$key])) {
                // تحقق من وجود الإعداد أولاً
                $exists = $this->db->fetch("SELECT 1 FROM settings WHERE `key` = ?", [$key]);
                if ($exists) {
                    $this->db->query("UPDATE settings SET `value` = ? WHERE `key` = ?", [$_POST[$key], $key]);
                } else {
                    $this->db->query("INSERT INTO settings (`key`, `value`) VALUES (?, ?)", [$key, $_POST[$key]]);
                }
                }
            }
            Session::setFlash('success', 'تم حفظ الإعدادات بنجاح');
        }
        redirect('index.php?page=settings');
    }

    public function addCategory() {
        if (!Session::hasPermission('settings.categories')) {
            Session::setFlash('error', 'ليس لديك صلاحية لإدارة التصنيفات');
            redirect('index.php?page=settings&tab=categories');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                redirect('index.php?page=settings&tab=categories');
            }

            $name = $_POST['category_name'] ?? '';
            if (!empty($name)) {
                $catModel = new Category();
                if ($catModel->add($name)) {
                    Session::setFlash('success', 'تم إضافة التصنيف بنجاح');
                } else {
                    Session::setFlash('error', 'فشل إضافة التصنيف (قد يكون موجوداً بالفعل)');
                }
            }
        }
        redirect('index.php?page=settings&tab=categories');
    }

    public function toggleCategory() {
        if (!Session::hasPermission('settings.categories')) {
            Session::setFlash('error', 'ليس لديك صلاحية لإدارة التصنيفات');
            redirect('index.php?page=settings');
        }

        $token = $_GET['token'] ?? '';
        if (!Session::validateCsrfToken($token)) {
            Session::setFlash('error', 'رمز الأمان غير صالح');
            redirect('index.php?page=settings');
        }
        
        $id = $_GET['id'] ?? 0;
        $status = $_GET['status'] ?? 0;
        
        $catModel = new Category();
        $cat = $catModel->getById($id);
        
        if ($cat && $cat['is_mandatory'] != 1) {
            $catModel->update($id, $cat['name'], $status);
            Session::setFlash('success', 'تم تحديث حالة التصنيف');
        } else {
            Session::setFlash('error', 'لا يمكن تعطيل التصنيف الإجباري');
        }
        redirect('index.php?page=settings&tab=categories');
    }

    public function editCategory() {
        if (!Session::hasPermission('settings.categories')) {
            Session::setFlash('error', 'ليس لديك صلاحية لإدارة التصنيفات');
            redirect('index.php?page=settings');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                redirect('index.php?page=settings&tab=categories');
            }

            $id = $_POST['category_id'] ?? 0;
            $name = trim($_POST['category_name'] ?? '');
            
            if (empty($name)) {
                Session::setFlash('error', 'اسم التصنيف مطلوب');
                redirect('index.php?page=settings&tab=categories');
            }

            $catModel = new Category();
            $cat = $catModel->getById($id);
            
            if ($cat) {
                if ($catModel->update($id, $name, $cat['is_active'])) {
                    Session::setFlash('success', 'تم تعديل التصنيف بنجاح');
                } else {
                    Session::setFlash('error', 'فشل تعديل التصنيف');
                }
            } else {
                Session::setFlash('error', 'التصنيف غير موجود');
            }
        }
        redirect('index.php?page=settings&tab=categories');
    }

    public function deleteCategory() {
        if (!Session::hasPermission('settings.categories')) {
            Session::setFlash('error', 'ليس لديك صلاحية لإدارة التصنيفات');
            redirect('index.php?page=settings');
        }

        $token = $_GET['token'] ?? '';
        if (!Session::validateCsrfToken($token)) {
            Session::setFlash('error', 'رمز الأمان غير صالح');
            redirect('index.php?page=settings&tab=categories');
        }
        
        $id = $_GET['id'] ?? 0;
        
        $catModel = new Category();
        $cat = $catModel->getById($id);
        
        if (!$cat) {
            Session::setFlash('error', 'التصنيف غير موجود');
            redirect('index.php?page=settings&tab=categories');
        }

        if ($cat['is_mandatory'] == 1) {
            Session::setFlash('error', 'لا يمكن حذف التصنيف الإجباري');
            redirect('index.php?page=settings&tab=categories');
        }

        if ($catModel->delete($id)) {
            Session::setFlash('success', 'تم حذف التصنيف بنجاح');
        } else {
            Session::setFlash('error', 'لا يمكن حذف التصنيف لأنه مرتبط بعملاء');
        }
        redirect('index.php?page=settings&tab=categories');
    }

    public function reorderCategories() {
        header('Content-Type: application/json');
        
        if (!Session::hasPermission('settings.categories')) {
            echo json_encode(['success' => false, 'message' => 'ليس لديك صلاحية']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'طريقة غير مسموحة']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $orderedIds = $input['order'] ?? [];

        if (empty($orderedIds)) {
            echo json_encode(['success' => false, 'message' => 'لا توجد بيانات']);
            exit;
        }

        $catModel = new Category();
        if ($catModel->updateOrder($orderedIds)) {
            echo json_encode(['success' => true, 'message' => 'تم حفظ الترتيب']);
        } else {
            echo json_encode(['success' => false, 'message' => 'فشل حفظ الترتيب']);
        }
        exit;
    }


    // --- إدارة النسخ الاحتياطي ---

    public function backup() {
        if (!Session::hasPermission('settings.backup')) {
            Session::setFlash('error', 'ليس لديك صلاحية للنسخ الاحتياطي');
            redirect('index.php?page=settings');
        }

        $dbPath = DB_PATH;
        $backupDir = ROOT_PATH . '/backups/';
        if (!is_dir($backupDir)) mkdir($backupDir, 0777, true);

        $backupName = 'backup_' . date('Y-m-d_H-i-s') . '.sqlite';
        if (copy($dbPath, $backupDir . $backupName)) {
            Session::setFlash('success', 'تم إنشاء نسخة احتياطية بنجاح: ' . $backupName);
        } else {
            Session::setFlash('error', 'فشل إنشاء النسخة الاحتياطية');
        }
        redirect('index.php?page=settings&tab=backup');
    }

    public function quickBackup() {
        if (!Session::hasPermission('settings.backup')) {
            Session::setFlash('error', 'ليس لديك صلاحية للنسخ الاحتياطي');
            redirect('index.php?page=dashboard');
        }

        $dbPath = DB_PATH;
        $filename = 'noor_backup_' . date('Y-m-d_H-i') . '.sqlite';

        if (file_exists($dbPath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($dbPath));
            readfile($dbPath);
            exit;
        }
        
        Session::setFlash('error', 'ملف قاعدة البيانات غير موجود!');
        redirect('index.php?page=dashboard');
    }

    public function downloadBackup() {
        if (!Session::hasPermission('settings.backup')) {
            Session::setFlash('error', 'ليس لديك صلاحية للنسخ الاحتياطي');
            redirect('index.php?page=settings');
        }
        $file = $_GET['file'] ?? '';
        $filepath = ROOT_PATH . '/backups/' . basename($file);

        if (file_exists($filepath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
            exit;
        }
        Session::setFlash('error', 'الملف غير موجود');
        redirect('index.php?page=settings&tab=backup');
    }

    public function deleteBackup() {
        if (!Session::hasPermission('settings.backup')) {
            Session::setFlash('error', 'ليس لديك صلاحية للنسخ الاحتياطي');
            redirect('index.php?page=settings');
        }
        $token = $_GET['token'] ?? '';
        if (!Session::validateCsrfToken($token)) {
            Session::setFlash('error', 'رمز الأمان غير صالح');
            redirect('index.php?page=settings&tab=backup');
        }

        $file = $_GET['file'] ?? '';
        $filepath = ROOT_PATH . '/backups/' . basename($file);

        if (file_exists($filepath)) {
            unlink($filepath);
            Session::setFlash('success', 'تم حذف النسخة الاحتياطية بنجاح');
        } else {
            Session::setFlash('error', 'الملف غير موجود');
        }
        redirect('index.php?page=settings&tab=backup');
    }

    public function restoreBackup() {
        if (!Session::hasPermission('settings.backup')) {
            Session::setFlash('error', 'ليس لديك صلاحية للنسخ الاحتياطي');
            redirect('index.php?page=settings');
        }
        $token = $_GET['token'] ?? '';
        if (!Session::validateCsrfToken($token)) {
            Session::setFlash('error', 'رمز الأمان غير صالح');
            redirect('index.php?page=settings&tab=backup');
        }

        $file = $_GET['file'] ?? '';
        $filepath = ROOT_PATH . '/backups/' . basename($file);
        $dbPath = DB_PATH;

        if (file_exists($filepath)) {
            copy($dbPath, ROOT_PATH . '/backups/auto_backup_before_restore_' . date('Y-m-d_H-i-s') . '.sqlite');
            if (copy($filepath, $dbPath)) {
                Session::setFlash('success', 'تم استعادة النظام بنجاح من النسخة: ' . $file);
            } else {
                Session::setFlash('error', 'فشل استعادة النسخة');
            }
        } else {
            Session::setFlash('error', 'ملف النسخة غير موجود');
        }
        redirect('index.php?page=settings&tab=backup');
    }

    public function uploadBackup() {
        if (!Session::hasPermission('settings.backup')) {
            Session::setFlash('error', 'ليس لديك صلاحية للنسخ الاحتياطي');
            redirect('index.php?page=settings');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['backup_file'])) {
            if (!Session::verifyCsrf()) {
                redirect('index.php?page=settings&tab=backup');
            }

            $file = $_FILES['backup_file'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);

            if ($ext !== 'sqlite') {
                Session::setFlash('error', 'يجب أن يكون الملف بصيغة .sqlite');
                redirect('index.php?page=settings&tab=backup');
            }

            $backupDir = ROOT_PATH . '/backups/';
            if (!is_dir($backupDir)) mkdir($backupDir, 0777, true);

            $filename = 'uploaded_' . date('Y-m-d_H-i-s') . '.sqlite';
            if (move_uploaded_file($file['tmp_name'], $backupDir . $filename)) {
                Session::setFlash('success', 'تم رفع ملف النسخة الاحتياطية بنجاح');
            } else {
                Session::setFlash('error', 'فشل رفع الملف');
            }
        }
        redirect('index.php?page=settings&tab=backup');
    }

    private function getBackupFiles() {
        $backupDir = ROOT_PATH . '/backups/';
        $files = [];
        if (is_dir($backupDir)) {
            $scanned = scandir($backupDir);
            foreach ($scanned as $file) {
                if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sqlite') {
                    $files[] = [
                        'name' => $file,
                        'size' => round(filesize($backupDir . $file) / 1024 / 1024, 2) . ' MB',
                        'date' => date('Y-m-d H:i', filemtime($backupDir . $file)),
                        'path' => $backupDir . $file
                    ];
                }
            }
            usort($files, function($a, $b) {
                return filemtime($b['path']) - filemtime($a['path']);
            });
        }
        return $files;
    }

    public function saveTelegram() {
        if (!Session::hasPermission('settings.telegram')) {
            Session::setFlash('error', 'ليس لديك صلاحية لإعدادات التليجرام');
            redirect('index.php?page=settings');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf()) {
                redirect('index.php?page=settings');
            }

            $this->saveSetting('telegram_bot_token', $_POST['telegram_bot_token'] ?? '');
            $this->saveSetting('telegram_chat_id', $_POST['telegram_chat_id'] ?? '');
            $this->saveSetting('telegram_notify_login', $_POST['telegram_notify_login'] ?? '0');
            $this->saveSetting('telegram_notify_errors', $_POST['telegram_notify_errors'] ?? '0');

            Session::setFlash('success', 'تم حفظ إعدادات Telegram بنجاح');
        }
        redirect('index.php?page=settings');
    }

    public function testTelegram() {
        if (!Session::hasPermission('settings.telegram')) {
            echo json_encode(['success' => false, 'message' => 'ليس لديك صلاحية']);
            exit;
        }
        require_once ROOT_PATH . '/app/Core/TelegramNotifier.php';
        $result = \App\Core\TelegramNotifier::send("🧪 <b>رسالة اختبار</b>\n\nتم إرسال هذه الرسالة من صفحة الإعدادات.\n⏰ " . date('Y-m-d H:i:s'));
        echo json_encode(['success' => (bool)$result, 'message' => $result ? 'تم إرسال الرسالة بنجاح!' : 'فشل الإرسال.']);
        exit;
    }

    private function saveSetting($key, $value) {
        $exists = $this->db->fetch("SELECT `key` FROM settings WHERE `key` = ?", [$key]);
        if ($exists) {
            $this->db->query("UPDATE settings SET value = ? WHERE `key` = ?", [$value, $key]);
        } else {
            $this->db->query("INSERT INTO settings (`key`, value) VALUES (?, ?)", [$key, $value]);
        }
    }
}
