<?php
// download_prescription.php - تحميل ملف PDF بأمان
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'clinicdesk_db';
$conn = new mysqli($host, $user, $pass, $db);

$appointment_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];
$role = $_SESSION['user_role'];

// جلب بيانات الوصفة والموعد
$stmt = $conn->prepare("
    SELECT p.file_path, a.patient_id, a.doctor_id, d.user_id as doctor_user_id
    FROM prescriptions p
    JOIN appointments a ON p.appointment_id = a.id
    JOIN doctors d ON a.doctor_id = d.id
    WHERE p.appointment_id = ?
");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();
$prescription = $result->fetch_assoc();

if (!$prescription || !$prescription['file_path']) {
    die("File not found.");
}

// التحقق من الصلاحيات
$is_authorized = false;

if ($role == 'admin') {
    $is_authorized = true;
} elseif ($role == 'doctor' && $prescription['doctor_user_id'] == $user_id) {
    $is_authorized = true;
} elseif ($role == 'patient' && $prescription['patient_id'] == $user_id) {
    $is_authorized = true;
}

if (!$is_authorized) {
    http_response_code(403);
    die("Access denied. You don't have permission to download this file.");
}

// مسار الملف
$file_path = __DIR__ . '/uploads/prescriptions/' . $prescription['file_path'];

if (!file_exists($file_path)) {
    die("File not found on server.");
}

// إرسال الملف للمتصفح للتحميل
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="prescription_' . $appointment_id . '.pdf"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

readfile($file_path);
exit;
?>