<?php
// appointments.php - نظام المواعيد المتكامل
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

$user_id = $_SESSION['user_id'];
$role = $_SESSION['user_role'];

// معالجة حجز موعد جديد
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_appointment'])) {
    $doctor_id = $_POST['doctor_id'] ?? 0;
    $appt_date = $_POST['appt_date'] ?? '';
    $appt_time = $_POST['appt_time'] ?? '';
    $reason = $_POST['reason'] ?? '';
    
    // التحقق من عدم وجود تعارض
    $check = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE doctor_id = ? AND appt_date = ? AND appt_time = ? AND status != 'cancelled'");
    $check->bind_param("iss", $doctor_id, $appt_date, $appt_time);
    $check->execute();
    $result = $check->get_result();
    $conflict = $result->fetch_assoc();
    
    if ($conflict['count'] > 0) {
        $error = "This time slot is already booked!";
    } else {
        $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appt_date, appt_time, reason, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("iisss", $user_id, $doctor_id, $appt_date, $appt_time, $reason);
        if ($stmt->execute()) {
            $success = "Appointment booked successfully!";
        } else {
            $error = "Failed to book appointment.";
        }
    }
}

// معالجة تحديث حالة الموعد
if (isset($_GET['update_status'])) {
    $appt_id = $_GET['id'] ?? 0;
    $new_status = $_GET['status'] ?? '';
    $allowed_statuses = ['confirmed', 'completed', 'cancelled'];
    
    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $appt_id);
        $stmt->execute();
        header("Location: appointments.php");
        exit;
    }
}

// جلب قائمة الأطباء لعرضها في نموذج الحجز
$doctors_list = $conn->query("SELECT d.id, u.name, s.name as specialization FROM doctors d JOIN users u ON d.user_id = u.id JOIN specializations s ON d.specialization_id = s.id WHERE u.is_active = 1 ORDER BY u.name");

// جلب المواعيد حسب دور المستخدم
if ($role == 'admin') {
    $appointments = $conn->query("SELECT a.*, pat.name as patient_name, doc.name as doctor_name, s.name as specialization FROM appointments a JOIN users pat ON a.patient_id = pat.id JOIN doctors d ON a.doctor_id = d.id JOIN users doc ON d.user_id = doc.id JOIN specializations s ON d.specialization_id = s.id ORDER BY a.appt_date DESC, a.appt_time DESC");
} elseif ($role == 'doctor') {
    // جلب doctor_id من جدول الأطباء
    $doc = $conn->query("SELECT id FROM doctors WHERE user_id = $user_id");
    $doctor = $doc->fetch_assoc();
    $doctor_id = $doctor['id'] ?? 0;
    $appointments = $conn->query("SELECT a.*, pat.name as patient_name FROM appointments a JOIN users pat ON a.patient_id = pat.id WHERE a.doctor_id = $doctor_id ORDER BY a.appt_date DESC, a.appt_time DESC");
} else {
    // patient
    $appointments = $conn->query("SELECT a.*, doc.name as doctor_name, s.name as specialization FROM appointments a JOIN doctors d ON a.doctor_id = d.id JOIN users doc ON d.user_id = doc.id JOIN specializations s ON d.specialization_id = s.id WHERE a.patient_id = $user_id ORDER BY a.appt_date DESC, a.appt_time DESC");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>ClinicDesk - Appointments</title>
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
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .form-group {
            margin-bottom: 15px;
        }
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
        .btn-book {
            background: #1cc88a;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
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
            display: inline-block;
        }
        .badge-pending { background: #f6c23e; color: #333; }
        .badge-confirmed { background: #4e73df; }
        .badge-completed { background: #1cc88a; }
        .badge-cancelled { background: #e74a3b; }
        .btn-status {
            padding: 5px 8px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 11px;
            text-decoration: none;
            display: inline-block;
            margin: 2px;
        }
        .btn-confirm { background: #4e73df; color: white; }
        .btn-complete { background: #1cc88a; color: white; }
        .btn-cancel { background: #e74a3b; color: white; }
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
        @media (max-width: 768px) {
            .header { flex-direction: column; gap: 10px; text-align: center; }
            .form-grid { grid-template-columns: 1fr; }
            table { font-size: 12px; }
            th, td { padding: 8px; }
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
        <h1>🏥 ClinicDesk - Appointments</h1>
        <!-- <div class="nav-links">
            <a href="index.php?page=dashboard">Dashboard</a>
            <?php if ($role == 'admin'): ?>
                <a href="users_list.php">Users</a>
                <a href="doctors_list.php">Doctors</a>
            <?php endif; ?>
            <a href="index.php?page=logout">Logout</a>
        </div> -->
          <?php include 'navbar.php'; ?>
        
    </div>
    
    <div class="container">
        <?php if ($role == 'patient'): ?>
        <!-- نموذج حجز موعد جديد - للمريض فقط -->
        <div class="card">
            <h2>📅 Book New Appointment</h2>
            <?php if (isset($success)): ?>
                <div class="success-msg">✅ <?= $success ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="error-msg">❌ <?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Select Doctor *</label>
                        <select name="doctor_id" required>
                            <option value="">-- Select Doctor --</option>
                            <?php while($doc = $doctors_list->fetch_assoc()): ?>
                            <option value="<?= $doc['id'] ?>"><?= htmlspecialchars($doc['name']) ?> (<?= $doc['specialization'] ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date *</label>
                        <input type="date" name="appt_date" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Time *</label>
                        <select name="appt_time" required>
                            <option value="">-- Select Time --</option>
                            <option value="09:00">09:00 AM</option>
                            <option value="09:30">09:30 AM</option>
                            <option value="10:00">10:00 AM</option>
                            <option value="10:30">10:30 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="11:30">11:30 AM</option>
                            <option value="12:00">12:00 PM</option>
                            <option value="12:30">12:30 PM</option>
                            <option value="13:00">01:00 PM</option>
                            <option value="13:30">01:30 PM</option>
                            <option value="14:00">02:00 PM</option>
                            <option value="14:30">02:30 PM</option>
                            <option value="15:00">03:00 PM</option>
                            <option value="15:30">03:30 PM</option>
                            <option value="16:00">04:00 PM</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Reason</label>
                        <textarea name="reason" placeholder="Brief reason for visit..."></textarea>
                    </div>
                </div>
                <button type="submit" name="book_appointment" class="btn-book">📅 Book Appointment</button>
            </form>
        </div>
        <?php endif; ?>
        
        <!-- قائمة المواعيد -->
        <div class="card">
            <h2>📋 <?= $role == 'admin' ? 'All' : ($role == 'doctor' ? 'My Schedule' : 'My') ?> Appointments</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <?php if ($role == 'admin'): ?>
                            <th>Patient</th>
                            <th>Doctor</th>
                        <?php elseif ($role == 'doctor'): ?>
                            <th>Patient</th>
                        <?php else: ?>
                            <th>Doctor</th>
                            <th>Specialization</th>
                        <?php endif; ?>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($appointments->num_rows > 0): ?>
                        <?php while($apt = $appointments->fetch_assoc()): ?>
                        <tr>
                            <td><?= $apt['id'] ?></td>
                            <?php if ($role == 'admin'): ?>
                                <td><?= htmlspecialchars($apt['patient_name']) ?></td>
                                <td><?= htmlspecialchars($apt['doctor_name']) ?></td>
                            <?php elseif ($role == 'doctor'): ?>
                                <td><?= htmlspecialchars($apt['patient_name']) ?></td>
                            <?php else: ?>
                                <td><?= htmlspecialchars($apt['doctor_name']) ?></td>
                                <td><?= htmlspecialchars($apt['specialization']) ?></td>
                            <?php endif; ?>
                            <td><?= date('d M Y', strtotime($apt['appt_date'])) ?></td>
                            <td><?= date('h:i A', strtotime($apt['appt_time'])) ?></td>
                            <td><?= htmlspecialchars($apt['reason'] ?? '-') ?></td>
                            <td>
                                <span class="badge badge-<?= $apt['status'] ?>">
                                    <?= ucfirst($apt['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($role == 'doctor' && $apt['status'] == 'pending'): ?>
                                    <a href="?update_status=1&id=<?= $apt['id'] ?>&status=confirmed" class="btn-status btn-confirm">Confirm</a>
                                    <a href="?update_status=1&id=<?= $apt['id'] ?>&status=cancelled" class="btn-status btn-cancel">Cancel</a>
                                <?php elseif ($role == 'doctor' && $apt['status'] == 'confirmed'): ?>
                                    <a href="?update_status=1&id=<?= $apt['id'] ?>&status=completed" class="btn-status btn-complete">Complete</a>
                                <?php elseif ($role == 'doctor' && $apt['status'] == 'completed'): ?>
                                    <?php
                                    // التحقق من وجود وصفة مسبقاً
                                    $check_pres = $conn->prepare("SELECT id FROM prescriptions WHERE appointment_id = ?");
                                    $check_pres->bind_param("i", $apt['id']);
                                    $check_pres->execute();
                                    $has_prescription = $check_pres->get_result()->num_rows > 0;
                                    ?>
                                    <?php if (!$has_prescription): ?>
                                        <a href="add_prescription.php?appointment_id=<?= $apt['id'] ?>" class="btn-status" style="background:#1cc88a; color:white;">Add Prescription</a>
                                    <?php else: ?>
                                        <span style="color:#1cc88a;">✓ Prescription Added</span>
                                    <?php endif; ?>
                                <?php elseif ($role == 'patient' && $apt['status'] == 'pending'): ?>
                                    <a href="?update_status=1&id=<?= $apt['id'] ?>&status=cancelled" class="btn-status btn-cancel" onclick="return confirm('Cancel this appointment?')">Cancel</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">No appointments found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>