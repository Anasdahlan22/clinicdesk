<?php
// reports.php - التقارير مع تصدير CSV
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

// معالجة تصدير CSV
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    $start_date = $_GET['start_date'] ?? '';
    $end_date = $_GET['end_date'] ?? '';
    $doctor_id = $_GET['doctor_id'] ?? '';
    $status = $_GET['status'] ?? '';
    
    $conditions = ["a.appt_date BETWEEN ? AND ?"];
    $params = [$start_date, $end_date];
    $types = "ss";
    
    if (!empty($doctor_id) && $doctor_id != 'all') {
        $conditions[] = "a.doctor_id = ?";
        $types .= "i";
        $params[] = $doctor_id;
    }
    if (!empty($status) && $status != 'all') {
        $conditions[] = "a.status = ?";
        $types .= "s";
        $params[] = $status;
    }
    
    $where = "WHERE " . implode(" AND ", $conditions);
    
    $sql = "SELECT 
                a.id, pat.name as patient_name, doc.name as doctor_name,
                s.name as specialization, a.appt_date, a.appt_time, 
                a.status, a.reason
            FROM appointments a
            JOIN users pat ON a.patient_id = pat.id
            JOIN doctors d ON a.doctor_id = d.id
            JOIN users doc ON d.user_id = doc.id
            JOIN specializations s ON d.specialization_id = s.id
            $where
            ORDER BY a.appt_date DESC, a.appt_time DESC";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    // إعداد ملف CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="appointments_report_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Patient', 'Doctor', 'Specialization', 'Date', 'Time', 'Status', 'Reason']);
    
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['patient_name'],
            $row['doctor_name'],
            $row['specialization'],
            $row['appt_date'],
            $row['appt_time'],
            $row['status'],
            $row['reason']
        ]);
    }
    
    fclose($output);
    exit;
}

// جلب قائمة الأطباء للفلترة
$doctors = $conn->query("SELECT d.id, u.name FROM doctors d JOIN users u ON d.user_id = u.id ORDER BY u.name");

// معالجة عرض التقرير
$report_data = null;
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$doctor_id = $_GET['doctor_id'] ?? '';
$status = $_GET['status'] ?? '';
$show_report = false;
$summary = ['total' => 0, 'pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0];

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['generate'])) {
    if (empty($start_date) || empty($end_date)) {
        $error = "Please select both start date and end date.";
    } elseif ($start_date > $end_date) {
        $error = "Start date cannot be after end date.";
    } else {
        $show_report = true;
        
        $conditions = ["a.appt_date BETWEEN ? AND ?"];
        $params = [$start_date, $end_date];
        $types = "ss";
        
        if (!empty($doctor_id) && $doctor_id != 'all') {
            $conditions[] = "a.doctor_id = ?";
            $types .= "i";
            $params[] = $doctor_id;
        }
        if (!empty($status) && $status != 'all') {
            $conditions[] = "a.status = ?";
            $types .= "s";
            $params[] = $status;
        }
        
        $where = "WHERE " . implode(" AND ", $conditions);
        
        $sql = "SELECT 
                    a.id, pat.name as patient_name, doc.name as doctor_name,
                    s.name as specialization, a.appt_date, a.appt_time, 
                    a.status, a.reason
                FROM appointments a
                JOIN users pat ON a.patient_id = pat.id
                JOIN doctors d ON a.doctor_id = d.id
                JOIN users doc ON d.user_id = doc.id
                JOIN specializations s ON d.specialization_id = s.id
                $where
                ORDER BY a.appt_date DESC, a.appt_time DESC";
        
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $report_data = $stmt->get_result();
        
        // حساب الملخص
        $summary['total'] = $report_data->num_rows;
        $report_data->data_seek(0);
        while ($row = $report_data->fetch_assoc()) {
            $summary[$row['status']]++;
        }
        $report_data->data_seek(0);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reports - ClinicDesk</title>
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
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .btn-generate {
            background: #4e73df;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            align-self: end;
        }
        .btn-export {
            background: #1cc88a;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #4e73df;
            color: white;
        }
        tr:hover { background: #f5f5f5; }
        .badge {
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 11px;
            color: white;
        }
        .badge-pending { background: #f6c23e; color: #333; }
        .badge-confirmed { background: #4e73df; }
        .badge-completed { background: #1cc88a; }
        .badge-cancelled { background: #e74a3b; }
        .summary {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .summary span { font-weight: bold; }
        @media (max-width: 768px) {
            .header { flex-direction: column; gap: 10px; text-align: center; }
            .filter-form { grid-template-columns: 1fr; }
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
        <h1>📊 Reports & Analytics</h1>
        <!-- <div class="nav-links">
            <a href="index.php?page=dashboard">Dashboard</a>
            <a href="users_list.php">Users</a>
            <a href="doctors_list.php">Doctors</a>
            <a href="specializations.php">Specializations</a>
            <a href="appointments.php">Appointments</a>
            <a href="index.php?page=logout">Logout</a>
        </div> -->
        <?php include 'navbar.php'; ?>
        
    </div>

    

    
    <div class="container">
        <div class="card">
            <h2>🔍 Generate Appointment Report</h2>
            
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label>Start Date *</label>
                    <input type="date" name="start_date" value="<?= $start_date ?>" required>
                </div>
                <div class="form-group">
                    <label>End Date *</label>
                    <input type="date" name="end_date" value="<?= $end_date ?>" required>
                </div>
                <div class="form-group">
                    <label>Doctor</label>
                    <select name="doctor_id">
                        <option value="all">-- All Doctors --</option>
                        <?php while($doc = $doctors->fetch_assoc()): ?>
                        <option value="<?= $doc['id'] ?>" <?= $doctor_id == $doc['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($doc['name']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="all">-- All Status --</option>
                        <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="confirmed" <?= $status == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="completed" <?= $status == 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $status == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <button type="submit" name="generate" class="btn-generate">📊 Generate Report</button>
            </form>
        </div>
        
        <?php if ($show_report): ?>
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <h2>📋 Report Results</h2>
                <a href="?export=csv&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&doctor_id=<?= $doctor_id ?>&status=<?= $status ?>" class="btn-export">📥 Export CSV</a>
            </div>
            
            <div class="summary">
                <strong>Summary:</strong> 
                Total: <span><?= $summary['total'] ?></span> | 
                Pending: <span><?= $summary['pending'] ?></span> | 
                Confirmed: <span><?= $summary['confirmed'] ?></span> | 
                Completed: <span><?= $summary['completed'] ?></span> | 
                Cancelled: <span><?= $summary['cancelled'] ?></span>
            </div>
            
            <?php if ($report_data && $report_data->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Specialization</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Reason</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $report_data->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['patient_name']) ?></td>
                        <td><?= htmlspecialchars($row['doctor_name']) ?></td>
                        <td><?= htmlspecialchars($row['specialization']) ?></td>
                        <td><?= date('d M Y', strtotime($row['appt_date'])) ?></td>
                        <td><?= date('h:i A', strtotime($row['appt_time'])) ?></td>
                        <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                        <td><?= htmlspecialchars($row['reason'] ?? '-') ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>No appointments found for the selected criteria.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>