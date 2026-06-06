<?php
// add_user.php
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
$role = $_POST['role'] ?? 'patient';
$phone = $_POST['phone'] ?? '';

$stmt = $conn->prepare("INSERT INTO users (name, email, password, role, phone, is_active) VALUES (?, ?, ?, ?, ?, 1)");
$stmt->bind_param("sssss", $name, $email, $password, $role, $phone);

if ($stmt->execute()) {
    header("Location: users_list.php?success=1");
} else {
    header("Location: users_list.php?error=" . urlencode($conn->error));
}
?>