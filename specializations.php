<?php
// specializations.php - إدارة التخصصات الطبية
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

// إضافة تخصص جديد
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_specialization'])) {
    $name = trim($_POST['name'] ?? '');
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO specializations (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            $success = "✅ Specialization added successfully!";
        } else {
            $error = "❌ Failed to add. Name may already exist.";
        }
    }
}

// تعديل تخصص
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_specialization'])) {
    $id = $_POST['id'] ?? 0;
    $name = trim($_POST['name'] ?? '');
    if (!empty($name) && $id > 0) {
        $stmt = $conn->prepare("UPDATE specializations SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $id);
        if ($stmt->execute()) {
            $success = "✅ Specialization updated successfully!";
        } else {
            $error = "❌ Failed to update.";
        }
    }
}

// حذف تخصص
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    // التحقق من عدم وجود أطباء مرتبطين
    $check = $conn->prepare("SELECT COUNT(*) as count FROM doctors WHERE specialization_id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $result = $check->get_result();
    $count = $result->fetch_assoc()['count'];
    
    if ($count == 0) {
        $stmt = $conn->prepare("DELETE FROM specializations WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success = "✅ Specialization deleted successfully!";
        } else {
            $error = "❌ Failed to delete.";
        }
    } else {
        $error = "❌ Cannot delete: $count doctor(s) are associated with this specialization.";
    }
}

// جلب جميع التخصصات
$specializations = $conn->query("SELECT * FROM specializations ORDER BY name");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Specializations - ClinicDesk</title>
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
        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            padding: 8px 15px;
            border-radius: 5px;
        }
        .nav-links a:hover { background: rgba(255,255,255,0.2); }
        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card h2 {
            margin-bottom: 20px;
            color: #333;
            border-bottom: 3px solid #4e73df;
            display: inline-block;
            padding-bottom: 5px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .btn-add {
            background: #1cc88a;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
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
        .btn-edit {
            background: #f6c23e;
            color: #333;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .btn-delete {
            background: #e74a3b;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
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
            width: 400px;
        }
        .modal-content h3 { margin-bottom: 20px; }
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
         .navbar .nav-links a {
            padding: 8px !important;
        }
         .navbar .logo {
            display: none
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏥 Manage Specializations</h1>
        <!-- <div class="nav-links">
            <a href="index.php?page=dashboard">Dashboard</a>
            <a href="users_list.php">Users</a>
            <a href="doctors_list.php">Doctors</a>
            <a href="appointments.php">Appointments</a>
            <a href="index.php?page=logout">Logout</a>
        </div> -->
         <?php include 'navbar.php'; ?>
         
    </div>
    
    <div class="container">
        <div class="card">
            <h2>➕ Add New Specialization</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Specialization Name</label>
                    <input type="text" name="name" required placeholder="e.g., Cardiology, Dermatology...">
                </div>
                <button type="submit" name="add_specialization" class="btn-add">Add Specialization</button>
            </form>
        </div>
        
        <div class="card">
            <h2>📋 All Specializations</h2>
            <table>
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php while($row = $specializations->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td>
                            <button class="btn-edit" onclick="editSpecialization(<?= $row['id'] ?>, '<?= addslashes($row['name']) ?>')">Edit</button>
                            <a href="?delete_id=<?= $row['id'] ?>" class="btn-delete" onclick="return confirmDelete(event, <?= $row['id'] ?>, '<?= addslashes($row['name']) ?>')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>✏️ Edit Specialization</h3>
            <form method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label>Specialization Name</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                <button type="submit" name="edit_specialization" class="btn-save">Save Changes</button>
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
            </form>
        </div>
    </div>
    
    <script>
        function editSpecialization(id, name) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('editModal').style.display = 'flex';
        }
        
        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        function confirmDelete(event, id, name) {
            event.preventDefault();
            Swal.fire({
                title: '⚠️ Confirm Delete',
                text: `Are you sure you want to delete "${name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `?delete_id=${id}`;
                }
            });
        }
        
        <?php if (isset($success)): ?>
        Swal.fire({ title: '✅ Success!', text: '<?= $success ?>', icon: 'success', confirmButtonColor: '#1cc88a' });
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
        Swal.fire({ title: '❌ Error!', text: '<?= $error ?>', icon: 'error', confirmButtonColor: '#e74a3b' });
        <?php endif; ?>
        
        window.onclick = function(event) {
            if (event.target == document.getElementById('editModal')) {
                closeModal();
            }
        }
    </script>
</body>
</html>