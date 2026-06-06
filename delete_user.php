<?php
// delete_user.php - حذف مستخدم مع التحقق
session_start();

// التحقق من أن المستخدم مسجل دخوله وأدمن
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'] ?? 0;

// منع حذف المستخدم الحالي (نفسه)
if ($id == $_SESSION['user_id']) {
    header("Location: users_list.php?error=" . urlencode("لا يمكنك حذف حسابك الخاص"));
    exit;
}

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'clinicdesk_db';
$conn = new mysqli($host, $user, $pass, $db);

// جلب اسم المستخدم قبل الحذف لعرضه في رسالة النجاح
$get_name = $conn->prepare("SELECT name, role FROM users WHERE id = ?");
$get_name->bind_param("i", $id);
$get_name->execute();
$name_result = $get_name->get_result();

if ($name_result->num_rows == 0) {
    header("Location: users_list.php?error=" . urlencode("المستخدم غير موجود"));
    exit;
}

$user_data = $name_result->fetch_assoc();
$user_name = $user_data['name'];
$user_role = $user_data['role'];

// التحقق: إذا كان المستخدم دكتور، نحتاج حذف سجل الدكتور أولاً (CASCADE سيتعامل معها)
// ولكن للتأكد من عدم وجود أخطاء
$conn->begin_transaction();

try {
    // إذا كان المستخدم دكتور، احذف سجل الدكتور أولاً
    if ($user_role == 'doctor') {
        $delete_doctor = $conn->prepare("DELETE FROM doctors WHERE user_id = ?");
        $delete_doctor->bind_param("i", $id);
        $delete_doctor->execute();
    }
    
    // حذف المستخدم
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $conn->commit();
    header("Location: users_list.php?success=1&name=" . urlencode($user_name));
    
} catch (Exception $e) {
    $conn->rollback();
    header("Location: users_list.php?error=" . urlencode("حدث خطأ أثناء الحذف: " . $e->getMessage()));
}
?>