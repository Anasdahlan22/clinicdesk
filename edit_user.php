<?php
// edit_user.php - تعديل بيانات المستخدم
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

$id = $_GET['id'] ?? 0;

// جلب بيانات المستخدم
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

if (!$user_data) {
    header("Location: users_list.php?error=User not found");
    exit;
}

// معالجة تحديث البيانات
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $is_active = $_POST['is_active'] ?? 1;
    $new_password = $_POST['new_password'] ?? '';
    
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET name = ?, phone = ?, is_active = ?, password = ? WHERE id = ?");
        $update->bind_param("ssisi", $name, $phone, $is_active, $hashed_password, $id);
    } else {
        $update = $conn->prepare("UPDATE users SET name = ?, phone = ?, is_active = ? WHERE id = ?");
        $update->bind_param("ssii", $name, $phone, $is_active, $id);
    }
    
    if ($update->execute()) {
        header("Location: users_list.php?success=1&name=" . urlencode($name));
    } else {
        header("Location: edit_user.php?id=$id&error=" . urlencode($conn->error));
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit User - ClinicDesk</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
        }
        .header {
            background: #4e73df;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header a {
            color: white;
            text-decoration: none;
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 5px;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 0 20px;
        }
        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card h2 {
            color: #4e73df;
            margin-bottom: 20px;
            border-bottom: 2px solid #4e73df;
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .btn-save {
            background: #1cc88a;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-cancel {
            background: #858796;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-left: 10px;
            text-decoration: none;
            display: inline-block;
        }
        .info-box {
            background: #e3f2fd;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        hr {
            margin: 20px 0;
            border: none;
            border-top: 1px solid #ddd;
        }
        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏥 Edit User</h1>
        <a href="users_list.php">← Back to Users</a>
    </div>
    
    <div class="container">
        <div class="card">
            <h2>✏️ Edit User: <?= htmlspecialchars($user_data['name']) ?></h2>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="error-msg">❌ <?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>
            
            <div class="info-box">
                <strong>📧 Email:</strong> <?= htmlspecialchars($user_data['email']) ?> (لا يمكن تغيير البريد الإلكتروني)
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user_data['name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($user_data['phone'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="is_active">
                        <option value="1" <?= $user_data['is_active'] ? 'selected' : '' ?>>✅ Active</option>
                        <option value="0" <?= !$user_data['is_active'] ? 'selected' : '' ?>>❌ Inactive</option>
                    </select>
                </div>
                
                <hr>
                
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" placeholder="أتركه فارغاً إذا لا تريد التغيير">
                    <small>اتركه فارغاً إذا كنت لا تريد تغيير كلمة المرور</small>
                </div>
                
                <button type="submit" class="btn-save">💾 Save Changes</button>
                <a href="users_list.php" class="btn-cancel">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>