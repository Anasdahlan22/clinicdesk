<?php
// setup.php - ملف إعداد مضمون
error_reporting(E_ALL);
ini_set('display_errors', 1);

// معلومات الاتصال بقاعدة البيانات
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'clinicdesk_db';

// الاتصال بقاعدة البيانات
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

echo "<h1>🔧 إعداد قاعدة البيانات - ClinicDesk</h1>";

// 1. حذف الجدول القديم إذا وجد
echo "<h2>📌 1. إعادة إنشاء جدول users...</h2>";
$conn->query("DROP TABLE IF EXISTS users");

// 2. إنشاء الجدول من جديد
$sql = "CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','doctor','patient') NOT NULL DEFAULT 'patient',
    phone VARCHAR(20) DEFAULT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql)) {
    echo "✅ جدول users تم إنشاؤه بنجاح<br>";
} else {
    echo "❌ خطأ: " . $conn->error . "<br>";
}

// 3. إنشاء كلمة مرور مشفرة لكلمة "admin123"
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

echo "<h2>📌 2. إضافة المستخدم admin...</h2>";
echo "كلمة المرور: <strong style='color:green'>$password</strong><br>";
echo "الهاش: <code>$hashed_password</code><br>";

// 4. إضافة المستخدم
$insert_sql = "INSERT INTO users (name, email, password, role, is_active) VALUES 
('Administrator', 'admin@clinicdesk.com', '$hashed_password', 'admin', 1)";

if ($conn->query($insert_sql)) {
    echo "✅ تم إضافة المستخدم admin بنجاح<br>";
} else {
    echo "❌ خطأ: " . $conn->error . "<br>";
}

// 5. التأكد من وجود البيانات
$result = $conn->query("SELECT id, name, email, role FROM users");
echo "<h2>📌 3. المستخدمين في قاعدة البيانات:</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['name'] . "</td>";
    echo "<td>" . $row['email'] . "</td>";
    echo "<td>" . $row['role'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// 6. اختبار التحقق من كلمة المرور
echo "<h2>📌 4. اختبار التحقق من كلمة المرور:</h2>";
$test_user = $conn->query("SELECT * FROM users WHERE email = 'admin@clinicdesk.com'")->fetch_assoc();
if (password_verify('admin123', $test_user['password'])) {
    echo "✅✅✅ التحقق ناجح! كلمة المرور 'admin123' صحيحة ✅✅✅<br>";
} else {
    echo "❌ فشل التحقق<br>";
}

echo "<hr>";
echo "<h2 style='color:green'>✅ الإعداد اكتمل بنجاح!</h2>";
echo "<h3>🚀 بيانات تسجيل الدخول:</h3>";
echo "<ul style='font-size:18px'>";
echo "<li><strong>البريد الإلكتروني (Email):</strong> <code style='background:yellow'>admin@clinicdesk.com</code></li>";
echo "<li><strong>كلمة المرور (Password):</strong> <code style='background:yellow'>admin123</code></li>";
echo "</ul>";
echo "<h3>👉 <a href='index.php?page=auth&action=login' style='font-size:20px'>اضغط هنا لتسجيل الدخول</a></h3>";

$conn->close();
?>