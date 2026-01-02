<?php
namespace App\Core;

/**
 * Error Analyzer - تحليل الأخطاء واقتراح الحلول
 */
class ErrorAnalyzer
{
    /**
     * تحليل الخطأ واقتراح حل
     */
    public static function analyze($error)
    {
        $error = strtolower($error);
        
        // قاعدة بيانات الحلول
        $solutions = [
            // أخطاء قاعدة البيانات
            'duplicate entry' => [
                'problem' => 'محاولة إدخال قيمة مكررة في عمود فريد',
                'steps' => [
                    '1️⃣ تحقق من القيمة المُدخلة',
                    '2️⃣ تأكد من عدم وجودها مسبقًا',
                    '3️⃣ استخدم INSERT IGNORE أو ON DUPLICATE KEY UPDATE'
                ]
            ],
            'table doesn\'t exist' => [
                'problem' => 'الجدول غير موجود في قاعدة البيانات',
                'steps' => [
                    '1️⃣ تأكد من اسم الجدول (حساس لحالة الأحرف)',
                    '2️⃣ شغّل ملفات الـ migrations',
                    '3️⃣ أنشئ الجدول يدويًا إذا لزم'
                ]
            ],
            'unknown column' => [
                'problem' => 'العمود غير موجود في الجدول',
                'steps' => [
                    '1️⃣ تأكد من اسم العمود',
                    '2️⃣ أضف العمود للجدول عبر ALTER TABLE',
                    '3️⃣ تحقق من الـ migrations'
                ]
            ],
            'access denied' => [
                'problem' => 'صلاحيات قاعدة البيانات غير كافية',
                'steps' => [
                    '1️⃣ تأكد من بيانات الاتصال في config.php',
                    '2️⃣ تحقق من صلاحيات المستخدم في MySQL',
                    '3️⃣ أعد تشغيل MySQL'
                ]
            ],
            'connection refused' => [
                'problem' => 'لا يمكن الاتصال بقاعدة البيانات',
                'steps' => [
                    '1️⃣ تأكد من تشغيل MySQL',
                    '2️⃣ تحقق من host و port',
                    '3️⃣ أعد تشغيل XAMPP'
                ]
            ],
            
            // أخطاء PHP
            'undefined variable' => [
                'problem' => 'استخدام متغير غير معرَّف',
                'steps' => [
                    '1️⃣ عرِّف المتغير قبل استخدامه',
                    '2️⃣ تحقق من نطاق المتغير (scope)',
                    '3️⃣ أضف قيمة افتراضية: $var ?? "default"'
                ]
            ],
            'undefined index' => [
                'problem' => 'الوصول لمفتاح غير موجود في مصفوفة',
                'steps' => [
                    '1️⃣ استخدم isset() أو array_key_exists()',
                    '2️⃣ أضف قيمة افتراضية: $arr["key"] ?? null',
                    '3️⃣ تحقق من مصدر البيانات'
                ]
            ],
            'class not found' => [
                'problem' => 'الكلاس غير موجود أو لم يُحمَّل',
                'steps' => [
                    '1️⃣ تأكد من namespace الصحيح',
                    '2️⃣ تحقق من اسم الملف (يجب أن يطابق اسم الكلاس)',
                    '3️⃣ شغّل: composer dump-autoload'
                ]
            ],
            'call to undefined function' => [
                'problem' => 'استدعاء دالة غير موجودة',
                'steps' => [
                    '1️⃣ تأكد من اسم الدالة',
                    '2️⃣ تحقق من تضمين الملف الصحيح',
                    '3️⃣ تأكد من تفعيل الـ extension المطلوب'
                ]
            ],
            'memory exhausted' => [
                'problem' => 'نفاد الذاكرة المتاحة',
                'steps' => [
                    '1️⃣ زد memory_limit في php.ini',
                    '2️⃣ حسّن الاستعلامات باستخدام LIMIT',
                    '3️⃣ استخدم pagination للبيانات الكبيرة'
                ]
            ],
            'maximum execution time' => [
                'problem' => 'تجاوز الوقت المسموح للتنفيذ',
                'steps' => [
                    '1️⃣ حسّن الكود لتقليل وقت التنفيذ',
                    '2️⃣ زد max_execution_time في php.ini',
                    '3️⃣ قسّم العمليات الكبيرة لدفعات'
                ]
            ],
            
            // أخطاء الملفات
            'permission denied' => [
                'problem' => 'صلاحيات الملفات غير كافية',
                'steps' => [
                    '1️⃣ chmod 755 للمجلدات',
                    '2️⃣ chmod 644 للملفات',
                    '3️⃣ تأكد من ملكية الملفات'
                ]
            ],
            'no such file' => [
                'problem' => 'الملف المطلوب غير موجود',
                'steps' => [
                    '1️⃣ تأكد من مسار الملف',
                    '2️⃣ تحقق من اسم الملف (حساس لحالة الأحرف)',
                    '3️⃣ أنشئ الملف إذا كان مطلوبًا'
                ]
            ],
            
            // أخطاء HTTP
            '404' => [
                'problem' => 'الصفحة غير موجودة',
                'steps' => [
                    '1️⃣ تأكد من الرابط',
                    '2️⃣ تحقق من ملف التوجيه (routing)',
                    '3️⃣ أنشئ الصفحة المطلوبة'
                ]
            ],
            '500' => [
                'problem' => 'خطأ داخلي في الخادم',
                'steps' => [
                    '1️⃣ راجع سجل الأخطاء (error_log)',
                    '2️⃣ فعّل display_errors للتطوير',
                    '3️⃣ تحقق من الكود الأخير الذي تم تعديله'
                ]
            ],
        ];
        
        // البحث عن الحل المناسب
        foreach ($solutions as $pattern => $solution) {
            if (strpos($error, $pattern) !== false) {
                $result = "⚠️ المشكلة: " . $solution['problem'] . "\n\n";
                $result .= implode("\n", $solution['steps']);
                return $result;
            }
        }
        
        // حل افتراضي
        return "1️⃣ راجع سجل الأخطاء\n2️⃣ تحقق من الكود في السطر المذكور\n3️⃣ ابحث عن الخطأ في Google";
    }
}
