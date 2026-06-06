<?php
// prescriptions.php - عرض الوصفات الطبية للمريض والأدمن
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

if ($role == 'patient') {
    // جلب الوصفات الخاصة بالمريض فقط
    $stmt = $conn->prepare("
        SELECT p.*, a.appt_date, u.name as doctor_name, s.name as specialization
        FROM prescriptions p
        JOIN appointments a ON p.appointment_id = a.id
        JOIN doctors d ON a.doctor_id = d.id
        JOIN users u ON d.user_id = u.id
        JOIN specializations s ON d.specialization_id = s.id
        WHERE a.patient_id = ?
        ORDER BY a.appt_date DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $prescriptions = $stmt->get_result();
} elseif ($role == 'admin') {
    // الأدمن يشوف كل الوصفات
    $prescriptions = $conn->query("
        SELECT p.*, a.appt_date, pat.name as patient_name, u.name as doctor_name
        FROM prescriptions p
        JOIN appointments a ON p.appointment_id = a.id
        JOIN users pat ON a.patient_id = pat.id
        JOIN doctors d ON a.doctor_id = d.id
        JOIN users u ON d.user_id = u.id
        ORDER BY a.appt_date DESC
    ");
} else {
    // الدكتور ما يشوفش صفحة الوصفات
    header("Location: appointments.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>My Prescriptions - ClinicDesk</title>
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
        .btn-download {
            background: #4e73df;
            color: white;
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
            display: inline-block;
        }
        .btn-download:hover {
            background: #224abe;
        }
        .no-file {
            color: #999;
            font-style: italic;
        }
        .preview-text {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        @media (max-width: 768px) {
            .header { flex-direction: column; gap: 10px; text-align: center; }
            table { font-size: 12px; }
            th, td { padding: 8px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏥 <?= $role == 'admin' ? 'All' : 'My' ?> Prescriptions</h1>
        <div class="nav-links">
            <a href="index.php?page=dashboard">Dashboard</a>
            <a href="appointments.php">Appointments</a>
            <a href="index.php?page=logout">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="card">
            <h2>📋 Prescription History</h2>
            
            <?php if ($prescriptions->num_rows == 0): ?>
                <p style="text-align: center; padding: 40px; color: #666;">
                    📭 No prescriptions found yet.
                </p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <?php if ($role == 'admin'): ?>
                                <th>Patient</th>
                                <th>Doctor</th>
                            <?php else: ?>
                                <th>Doctor</th>
                                <th>Specialization</th>
                            <?php endif; ?>
                            <th>Date</th>
                            <th>Diagnosis</th>
                            <th>Medications</th>
                            <th>PDF File</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $prescriptions->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <?php if ($role == 'admin'): ?>
                                <td><?= htmlspecialchars($row['patient_name']) ?></td>
                                <td><?= htmlspecialchars($row['doctor_name']) ?></td>
                            <?php else: ?>
                                <td><?= htmlspecialchars($row['doctor_name']) ?></td>
                                <td><?= htmlspecialchars($row['specialization']) ?></td>
                            <?php endif; ?>
                            <td><?= date('d M Y', strtotime($row['appt_date'])) ?></td>
                            <td class="preview-text" title="<?= htmlspecialchars($row['diagnosis']) ?>">
                                <?= htmlspecialchars(substr($row['diagnosis'], 0, 50)) ?>...
                            </td>
                            <td class="preview-text" title="<?= htmlspecialchars($row['medications']) ?>">
                                <?= htmlspecialchars(substr($row['medications'], 0, 50)) ?>...
                            </td>
                            <td>
                                <?php if ($row['file_path']): ?>
                                    <a href="download_prescription.php?id=<?= $row['appointment_id'] ?>" class="btn-download">
                                        📄 Download PDF
                                    </a>
                                <?php else: ?>
                                    <span class="no-file">No file uploaded</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>