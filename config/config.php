<?php
// config/config.php
declare(strict_types=1);

// اسم التطبيق
define('APP_NAME', 'ClinicDesk');
define('APP_VERSION', '1.0.0');

// رابط الموقع الأساسي (عدله حسب إعداداتك)
define('BASE_URL', 'http://localhost/clinicdesk');

// عدد العناصر في كل صفحة
define('ITEMS_PER_PAGE', 10);

// إعدادات رفع الملفات
define('MAX_AVATAR_SIZE', 1 * 1024 * 1024);   // 1 MB
define('MAX_DOCTOR_PHOTO_SIZE', 1 * 1024 * 1024);
define('MAX_PRESCRIPTION_SIZE', 3 * 1024 * 1024);

// أنواع الملفات المسموحة
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// مجلدات رفع الملفات
define('UPLOAD_AVATARS', __DIR__ . '/../public/uploads/avatars/');
define('UPLOAD_DOCTOR_PHOTOS', __DIR__ . '/../public/uploads/doctor_photos/');
define('UPLOAD_PRESCRIPTIONS', __DIR__ . '/../public/uploads/prescriptions/');

// مدة الجلسة (بالثواني)
define('SESSION_LIFETIME', 7200); // ساعتين

// مواعيد الحجز المتاحة
$slots = [];
for ($h = 9; $h <= 15; $h++) {
    $slots[] = sprintf('%02d:00', $h);
    $slots[] = sprintf('%02d:30', $h);
}
$slots[] = '16:00';
define('APPOINTMENT_SLOTS', $slots);