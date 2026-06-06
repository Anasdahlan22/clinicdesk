<?php
// users_list.php - إدارة المستخدمين مع Sweet Alert والتحقق من البريد المكرر
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

// معالجة تحديث حالة المستخدم (تفعيل/تعطيل)
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $toggle_id = $_GET['id'];
    $conn->query("UPDATE users SET is_active = NOT is_active WHERE id = $toggle_id");
    header("Location: users_list.php");
    exit;
}

// جلب جميع المستخدمين
$result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ClinicDesk - Users Management</title>
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
            flex-wrap: wrap;
        }
        .header h1 { font-size: 24px; }
        .navbar .logo {
            font-size: 24px;
            font-weight: bold;
            display: none;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            padding: 8px !important;
            border-radius: 5px;
        }
        .nav-links a:hover { background: rgba(255,255,255,0.2); }
        .container {
            max-width: 1300px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card h2 {
            margin-bottom: 20px;
            color: #333;
            border-bottom: 3px solid #4e73df;
            display: inline-block;
            padding-bottom: 5px;
        }
        .btn-add {
            background: #1cc88a;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
            display: inline-block;
            text-decoration: none;
            float: right;
        }
        .btn-add:hover { background: #17a673; }
        .clearfix { clear: both; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #4e73df;
            color: white;
        }
        tr:hover { background: #f5f5f5; }
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            color: white;
            display: inline-block;
        }
        .badge-admin { background: #e74a3b; }
        .badge-doctor { background: #4e73df; }
        .badge-patient { background: #1cc88a; }
        .status-active { color: #1cc88a; font-weight: bold; }
        .status-inactive { color: #e74a3b; font-weight: bold; }
        .btn-status {
            background: #f6c23e;
            color: #333;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            font-size: 11px;
            display: inline-block;
        }
        .btn-edit {
            background: #4e73df;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            font-size: 11px;
            display: inline-block;
        }
        .btn-delete {
            background: #e74a3b;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            font-size: 11px;
            display: inline-block;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 450px;
        }
        .modal-content h3 { margin-bottom: 20px; color: #4e73df; }
        .form-group { margin-bottom: 15px; }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-group small {
            display: block;
            color: #888;
            font-size: 11px;
            margin-top: 3px;
        }
        .btn-save {
            background: #4e73df;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-cancel {
            background: #858796;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
        }
        .btn-save:hover { background: #224abe; }
        .btn-cancel:hover { background: #6c6e7a; }
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .search-box {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .search-box input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            flex: 1;
        }
        .search-box button {
            padding: 10px 20px;
            background: #4e73df;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        @media (max-width: 768px) {
            .header { flex-direction: column; gap: 10px; text-align: center; }
            table { font-size: 12px; }
            th, td { padding: 8px; }
            .action-buttons { flex-direction: column; }
            .btn-add { float: none; display: block; text-align: center; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏥 ClinicDesk - Users Management</h1>
        <!-- <div class="nav-links">
            <a href="index.php?page=dashboard">Dashboard</a>
            <a href="doctors_list.php">Doctors</a>
            <a href="appointments.php">Appointments</a>
            <a href="index.php?page=logout">Logout</a>
        </div> -->
        <?php include 'navbar.php'; ?>
    </div>
    
    <div class="container">
        <div class="card">
            <h2>👥 All Users</h2>
            <button class="btn-add" onclick="showAddModal()">+ Add New User</button>
            <div class="clearfix"></div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td>
                            <span class="badge badge-<?= $row['role'] ?>">
                                <?= ucfirst($row['role']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($row['phone'] ?? '-') ?></td>
                        <td class="<?= $row['is_active'] ? 'status-active' : 'status-inactive' ?>">
                            <?= $row['is_active'] ? '✓ Active' : '✗ Inactive' ?>
                        </td>
                        <td class="action-buttons">
                            <a href="?toggle_status=1&id=<?= $row['id'] ?>" class="btn-status" onclick="return confirmToggle(event, <?= $row['id'] ?>, '<?= addslashes($row['name']) ?>', <?= $row['is_active'] ?>)">
                                <?= $row['is_active'] ? 'Deactivate' : 'Activate' ?>
                            </a>
                            <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn-edit">Edit</a>
                            <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                <a href="javascript:void(0)" onclick="confirmDelete(<?= $row['id'] ?>, '<?= addslashes($row['name']) ?>')" class="btn-delete">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Add User Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <h3>➕ Add New User</h3>
            <form id="addUserForm" method="POST" action="add_user.php">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" required placeholder="Enter full name">
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required placeholder="Enter email address">
                    <small>⚠️ Email must be unique. Cannot be changed later.</small>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="text" name="password" value="123456">
                    <small>Default: 123456 (user can change later)</small>
                </div>
                <div class="form-group">
                    <label>Role *</label>
                    <select name="role" required>
                        <option value="patient">Patient</option>
                        <option value="doctor">Doctor</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" placeholder="Optional">
                </div>
                <button type="submit" class="btn-save">Save User</button>
                <button type="button" class="btn-cancel" onclick="hideAddModal()">Cancel</button>
            </form>
        </div>
    </div>
    
    <script>
        // عرض رسائل النجاح أو الخطأ عند تحميل الصفحة
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            const success = urlParams.get('success');
            const error = urlParams.get('error');
            const name = urlParams.get('name');
            
            if (success) {
                Swal.fire({
                    title: '✅ Success!',
                    text: name ? `User "${name}" has been added successfully.` : 'Operation completed successfully.',
                    icon: 'success',
                    confirmButtonColor: '#1cc88a',
                    confirmButtonText: 'OK',
                    timer: 3000
                });
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            
            if (error) {
                let errorText = decodeURIComponent(error);
                
                if (errorText.includes('Duplicate entry') && errorText.includes('email')) {
                    errorText = '❌ This email address is already registered. Please use a different email.';
                } else if (errorText.includes('email')) {
                    errorText = '❌ Email error. Please use a valid and unique email address.';
                }
                
                Swal.fire({
                    title: '❌ Error!',
                    text: errorText,
                    icon: 'error',
                    confirmButtonColor: '#e74a3b',
                    confirmButtonText: 'OK'
                });
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        };
        
        function showAddModal() {
            document.getElementById('addModal').style.display = 'flex';
        }
        
        function hideAddModal() {
            document.getElementById('addModal').style.display = 'none';
        }
        
        function confirmDelete(id, name) {
            Swal.fire({
                title: '⚠️ Confirm Delete',
                html: `Are you sure you want to delete user "<strong>${name}</strong>"?<br><br>This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `delete_user.php?id=${id}`;
                }
            });
        }
        
        function confirmToggle(event, id, name, currentStatus) {
            event.preventDefault();
            const newStatus = currentStatus ? 'deactivate' : 'activate';
            const actionText = currentStatus ? 'Deactivate' : 'Activate';
            
            Swal.fire({
                title: `⚠️ Confirm ${actionText}`,
                text: `Are you sure you want to ${actionText.toLowerCase()} user "${name}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#f6c23e',
                cancelButtonColor: '#858796',
                confirmButtonText: `Yes, ${actionText}`,
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `?toggle_status=1&id=${id}`;
                }
            });
        }
        
        // التحقق من البريد الإلكتروني قبل إرسال النموذج
        document.getElementById('addUserForm')?.addEventListener('submit', function(e) {
            const email = this.querySelector('input[name="email"]').value;
            const name = this.querySelector('input[name="name"]').value;
            
            if (!email.includes('@') || !email.includes('.')) {
                e.preventDefault();
                Swal.fire({
                    title: '❌ Invalid Email',
                    text: 'Please enter a valid email address (e.g., name@domain.com)',
                    icon: 'error',
                    confirmButtonColor: '#4e73df'
                });
                return;
            }
            
            if (name.trim().length < 3) {
                e.preventDefault();
                Swal.fire({
                    title: '❌ Invalid Name',
                    text: 'Name must be at least 3 characters long',
                    icon: 'error',
                    confirmButtonColor: '#4e73df'
                });
                return;
            }
        });
        
        // إغلاق المودال عند الضغط خارجها
        window.onclick = function(event) {
            const modal = document.getElementById('addModal');
            if (event.target == modal) {
                hideAddModal();
            }
        }
    </script>
</body>
</html>