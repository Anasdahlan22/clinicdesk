<?php
// add_doctor.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: index.php");
    exit;
}

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'clinicdesk_db';
$conn = new mysqli($host, $user, $pass, $db);

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$password = password_hash($_POST['password'] ?? '123456', PASSWORD_DEFAULT);
$phone = $_POST['phone'] ?? '';
$specialization_id = $_POST['specialization_id'] ?? 0;
$consultation_fee = $_POST['consultation_fee'] ?? 0;
$bio = $_POST['bio'] ?? '';
$available_days = isset($_POST['available_days']) ? implode(',', $_POST['available_days']) : 'Mon,Tue,Wed,Thu';

// بدء المعاملة (Transaction)
$conn->begin_transaction();

try {
    // 1. إضافة المستخدم
    $stmt1 = $conn->prepare("INSERT INTO users (name, email, password, role, phone, is_active) VALUES (?, ?, ?, 'doctor', ?, 1)");
    $stmt1->bind_param("ssss", $name, $email, $password, $phone);
    $stmt1->execute();
    $user_id = $conn->insert_id;
    
    // 2. إضافة الطبيب
    $stmt2 = $conn->prepare("INSERT INTO doctors (user_id, specialization_id, bio, consultation_fee, available_days) VALUES (?, ?, ?, ?, ?)");
    $stmt2->bind_param("iisds", $user_id, $specialization_id, $bio, $consultation_fee, $available_days);
    $stmt2->execute();
    
    $conn->commit();
    header("Location: doctors_list.php?success=1");
    
} catch (Exception $e) {
    $conn->rollback();
    header("Location: doctors_list.php?error=" . urlencode($e->getMessage()));
}
?>