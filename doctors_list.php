<?php
// doctors_list.php - إدارة الأطباء
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

// جلب جميع الأطباء مع معلوماتهم
$sql = "SELECT d.*, u.name, u.email, u.phone, s.name as specialization_name 
        FROM doctors d 
        JOIN users u ON d.user_id = u.id 
        JOIN specializations s ON d.specialization_id = s.id 
        ORDER BY u.name";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>ClinicDesk - Doctors Management</title>
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
            max-width: 1400px;
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
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            color: white;
        }
        .fee {
            color: #1cc88a;
            font-weight: bold;
        }
        .btn-edit {
            background: #f6c23e;
            color: #333;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
        }
        .btn-delete {
            background: #e74a3b;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
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
            width: 500px;
            max-height: 90%;
            overflow-y: auto;
        }
        .modal-content h3 { margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-group textarea { height: 80px; resize: vertical; }
        .days-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .days-group label {
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: normal;
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
        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .navbar .logo{
            display: none
        }
        .navbar .nav-links a {
            padding: 8px !important;
        
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏥 ClinicDesk - Doctors Management</h1>
        <!-- <div class="nav-links">
            <a href="index.php?page=dashboard">Dashboard</a>
            <a href="users_list.php">Users</a>
            <a href="index.php?page=logout">Logout</a>
        </div> -->
         <?php include 'navbar.php'; ?>
         
    </div>
    
    <div class="container">
        <div class="card">
            <h2>👨‍⚕️ All Doctors</h2>
            <button class="btn-add" onclick="showAddModal()">+ Add New Doctor</button>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="success-msg">✅ Operation completed successfully!</div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <div class="error-msg">❌ Error: <?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Specialization</th>
                        <th>Fee</th>
                        <th>Available Days</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['specialization_name']) ?></td>
                        <td class="fee">$<?= number_format($row['consultation_fee'], 2) ?></td>
                        <td><?= htmlspecialchars($row['available_days']) ?></td>
                        <td><?= htmlspecialchars($row['phone'] ?? '-') ?></td>
                        <td>
                            <a href="#" onclick="editDoctor(<?= $row['id'] ?>)" class="btn-edit">Edit</a>
                            <a href="delete_doctor.php?id=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Delete this doctor?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Add Doctor Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <h3>➕ Add New Doctor</h3>
            <form method="POST" action="add_doctor.php">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="text" name="password" value="123456">
                    <small>Default: 123456</small>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone">
                </div>
                <div class="form-group">
                    <label>Specialization *</label>
                    <select name="specialization_id" required>
                        <option value="">Select Specialization</option>
                        <?php
                        $spec_result = $conn->query("SELECT id, name FROM specializations ORDER BY name");
                        while($spec = $spec_result->fetch_assoc()):
                        ?>
                        <option value="<?= $spec['id'] ?>"><?= htmlspecialchars($spec['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Consultation Fee ($) *</label>
                    <input type="number" name="consultation_fee" step="0.01" value="50" required>
                </div>
                <div class="form-group">
                    <label>Available Days *</label>
                    <div class="days-group">
                        <label><input type="checkbox" name="available_days[]" value="Sun"> Sunday</label>
                        <label><input type="checkbox" name="available_days[]" value="Mon" checked> Monday</label>
                        <label><input type="checkbox" name="available_days[]" value="Tue" checked> Tuesday</label>
                        <label><input type="checkbox" name="available_days[]" value="Wed" checked> Wednesday</label>
                        <label><input type="checkbox" name="available_days[]" value="Thu" checked> Thursday</label>
                        <label><input type="checkbox" name="available_days[]" value="Fri"> Friday</label>
                        <label><input type="checkbox" name="available_days[]" value="Sat"> Saturday</label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Bio</label>
                    <textarea name="bio" placeholder="Doctor's biography..."></textarea>
                </div>
                <button type="submit" class="btn-save">Save Doctor</button>
                <button type="button" class="btn-cancel" onclick="hideAddModal()">Cancel</button>
            </form>
        </div>
    </div>
    
    <script>
        function showAddModal() {
            document.getElementById('addModal').style.display = 'flex';
        }
        function hideAddModal() {
            document.getElementById('addModal').style.display = 'none';
        }
        function editDoctor(id) {
            alert('Edit doctor feature coming soon!');
        }
    </script>
</body>
</html>