<?php
// delete_doctor.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'] ?? 0;

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'clinicdesk_db';
$conn = new mysqli($host, $user, $pass, $db);

// جلب user_id المرتبط بالطبيب
$stmt = $conn->prepare("SELECT user_id FROM doctors WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();

if ($doctor) {
    $user_id = $doctor['user_id'];
    
    // حذف الطبيب (user ستحذف تلقائياً بسبب CASCADE)
    $delete = $conn->prepare("DELETE FROM doctors WHERE id = ?");
    $delete->bind_param("i", $id);
    $delete->execute();
    
    header("Location: doctors_list.php?success=1");
} else {
    header("Location: doctors_list.php?error=Doctor not found");
}
?>