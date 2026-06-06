<?php
// index.php - ClinicDesk - النسخة النهائية مع الإحصائيات المتكاملة
session_start();

// ============================================
// دوال مساعدة
// ============================================
function redirect($url) {
    header("Location: $url");
    exit;
}

// ============================================
// الاتصال بقاعدة البيانات
// ============================================
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'clinicdesk_db';
$conn = new mysqli($host, $user, $pass, $db);

// ============================================
// نظام التوجيه (Routing)
// ============================================
$page = $_GET['page'] ?? 'login';

// إذا كان المستخدم مسجل دخوله وطلب صفحة login، حوله للداشبورد
if ($page == 'login' && isset($_SESSION['user_id'])) {
    redirect('index.php?page=dashboard');
}

// إذا كان المستخدم غير مسجل وطلب غير login، حوله للوجين
if ($page != 'login' && !isset($_SESSION['user_id'])) {
    redirect('index.php?page=login');
}

// ============================================
// صفحة تسجيل الدخول (LOGIN)
// ============================================
if ($page == 'login') {
    
    // معالجة تسجيل الدخول
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            redirect('index.php?page=dashboard');
        } else {
            $error = "Invalid email or password";
        }
    }
    
    // عرض صفحة تسجيل الدخول
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>ClinicDesk - Login</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            .login-container { width: 100%; max-width: 400px; padding: 20px; }
            .login-card {
                background: white;
                border-radius: 10px;
                box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                overflow: hidden;
            }
            .login-header {
                background: #4e73df;
                color: white;
                padding: 30px;
                text-align: center;
            }
            .login-header h1 { font-size: 28px; margin-bottom: 5px; }
            .login-body { padding: 30px; }
            .form-group { margin-bottom: 20px; }
            .form-group label {
                display: block;
                margin-bottom: 8px;
                font-weight: 600;
                color: #333;
            }
            .form-group input {
                width: 100%;
                padding: 12px;
                border: 1px solid #ddd;
                border-radius: 5px;
                font-size: 14px;
            }
            .btn-login {
                width: 100%;
                padding: 12px;
                background: #4e73df;
                color: white;
                border: none;
                border-radius: 5px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
            }
            .btn-login:hover { background: #224abe; }
            .alert {
                padding: 12px;
                border-radius: 5px;
                margin-bottom: 20px;
            }
            .alert-danger {
                background: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
            .demo-info {
                margin-top: 20px;
                padding: 15px;
                background: #f8f9fc;
                border-radius: 5px;
                text-align: center;
            }
            .demo-info code {
                background: #e9ecef;
                padding: 3px 8px;
                border-radius: 3px;
                font-size: 13px;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <h1>🏥 ClinicDesk</h1>
                    <p>Clinic Management System</p>
                </div>
                <div class="login-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" required autofocus>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" required>
                        </div>
                        <button type="submit" class="btn-login">Login</button>
                    </form>
                    
                    <!-- <div class="demo-info">
                        <strong>Demo Account:</strong><br>
                        Email: <code>admin@clinicdesk.com</code><br>
                        Password: <code>admin123</code>
                    </div> -->
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

// ============================================
// لوحة التحكم (DASHBOARD) مع إحصائيات حقيقية
// ============================================
elseif ($page == 'dashboard') {
    $role = $_SESSION['user_role'];
    $user_id = $_SESSION['user_id'];
    
    // ========== إحصائيات الأدمن ==========
    if ($role == 'admin') {
        $total_users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
        $total_doctors = $conn->query("SELECT COUNT(*) as total FROM doctors")->fetch_assoc()['total'];
        $total_patients = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'patient'")->fetch_assoc()['total'];
        $today_appointments = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE appt_date = CURDATE()")->fetch_assoc()['total'];
        $pending_appointments = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE status = 'pending'")->fetch_assoc()['total'];
        
        // إحصائيات حسب الأدوار
        $role_stats = $conn->query("SELECT role, COUNT(*) as total FROM users GROUP BY role");
        $role_data = ['admin' => 0, 'doctor' => 0, 'patient' => 0];
        while($row = $role_stats->fetch_assoc()) {
            $role_data[$row['role']] = $row['total'];
        }
        
        // مواعيد هذا الأسبوع حسب الحالة
        $weekly_stats = $conn->query("
            SELECT status, COUNT(*) as total 
            FROM appointments 
            WHERE WEEK(appt_date) = WEEK(CURDATE()) 
            GROUP BY status
        ");
        $weekly_data = ['pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0];
        while($row = $weekly_stats->fetch_assoc()) {
            $weekly_data[$row['status']] = $row['total'];
        }
        
        // آخر 5 مواعيد
        $recent_appointments = $conn->query("
            SELECT a.*, pat.name as patient_name, doc.name as doctor_name 
            FROM appointments a
            JOIN users pat ON a.patient_id = pat.id
            JOIN doctors d ON a.doctor_id = d.id
            JOIN users doc ON d.user_id = doc.id
            ORDER BY a.created_at DESC LIMIT 5
        ");
    }
    
    // ========== إحصائيات الدكتور ==========
    elseif ($role == 'doctor') {
        $doctor_data = $conn->query("SELECT id FROM doctors WHERE user_id = $user_id")->fetch_assoc();
        $doctor_id = $doctor_data['id'] ?? 0;
        
        $today_appointments = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE doctor_id = $doctor_id AND appt_date = CURDATE()")->fetch_assoc()['total'];
        $pending_count = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE doctor_id = $doctor_id AND status = 'pending'")->fetch_assoc()['total'];
        $confirmed_count = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE doctor_id = $doctor_id AND status = 'confirmed'")->fetch_assoc()['total'];
        $completed_count = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE doctor_id = $doctor_id AND status = 'completed'")->fetch_assoc()['total'];
        $month_appointments = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE doctor_id = $doctor_id AND MONTH(appt_date) = MONTH(CURDATE())")->fetch_assoc()['total'];
        
        // مواعيد اليوم
        $today_list = $conn->query("
            SELECT a.*, u.name as patient_name 
            FROM appointments a
            JOIN users u ON a.patient_id = u.id
            WHERE a.doctor_id = $doctor_id AND a.appt_date = CURDATE() AND a.status != 'cancelled'
            ORDER BY a.appt_time
        ");
        
        // المواعيد القادمة
        $upcoming = $conn->query("
            SELECT a.*, u.name as patient_name 
            FROM appointments a
            JOIN users u ON a.patient_id = u.id
            WHERE a.doctor_id = $doctor_id AND a.appt_date >= CURDATE() AND a.status IN ('pending', 'confirmed')
            ORDER BY a.appt_date, a.appt_time LIMIT 5
        ");
    }
    
    // ========== إحصائيات المريض ==========
    elseif ($role == 'patient') {
        $active_count = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE patient_id = $user_id AND status IN ('pending', 'confirmed')")->fetch_assoc()['total'];
        $completed_count = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE patient_id = $user_id AND status = 'completed'")->fetch_assoc()['total'];
        $cancelled_count = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE patient_id = $user_id AND status = 'cancelled'")->fetch_assoc()['total'];
        $prescriptions_count = $conn->query("
            SELECT COUNT(*) as total FROM prescriptions p
            JOIN appointments a ON p.appointment_id = a.id
            WHERE a.patient_id = $user_id
        ")->fetch_assoc()['total'];
        
        // الموعد القادم
        $next_appointment = $conn->query("
            SELECT a.*, u.name as doctor_name, s.name as specialization
            FROM appointments a
            JOIN doctors d ON a.doctor_id = d.id
            JOIN users u ON d.user_id = u.id
            JOIN specializations s ON d.specialization_id = s.id
            WHERE a.patient_id = $user_id AND a.appt_date >= CURDATE() AND a.status IN ('pending', 'confirmed')
            ORDER BY a.appt_date, a.appt_time LIMIT 1
        ")->fetch_assoc();
        
        // آخر مواعيد المريض
        $recent_patient_appointments = $conn->query("
            SELECT a.*, u.name as doctor_name
            FROM appointments a
            JOIN doctors d ON a.doctor_id = d.id
            JOIN users u ON d.user_id = u.id
            WHERE a.patient_id = $user_id
            ORDER BY a.appt_date DESC LIMIT 5
        ");
    }
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>ClinicDesk - Dashboard</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: #f4f6f9;
            }
            .header {
                background: #4e73df;
                color: white;
                /* padding: 15px 30px; */
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
            }
            .header h1 { font-size: 24px; }
            .nav-links {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
            }
            .nav-links a {
                color: white;
                text-decoration: none;
                padding: 8px 15px;
                border-radius: 5px;
            }
            .nav-links a:hover { background: rgba(255,255,255,0.2); }
            .logout-btn { background: #e74a3b !important; }
            .container { max-width: 1400px; margin: 30px auto; padding: 0 20px; }
            .welcome-card {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border-radius: 10px;
                padding: 30px;
                margin-bottom: 30px;
            }
            .welcome-card h2 { font-size: 28px; margin-bottom: 10px; }
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }
            .stat-card {
                background: white;
                border-radius: 10px;
                padding: 25px;
                text-align: center;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .stat-number { font-size: 36px; font-weight: bold; color: #4e73df; }
            .stat-label { color: #666; margin-top: 10px; }
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
            .menu-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 15px;
                margin-top: 20px;
            }
            .menu-item {
                background: #f8f9fc;
                padding: 20px;
                text-align: center;
                text-decoration: none;
                color: #333;
                border-radius: 8px;
                transition: all 0.3s;
                display: block;
                border: 1px solid #e3e6f0;
            }
            .menu-item:hover {
                transform: translateY(-3px);
                background: #4e73df;
                color: white;
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
            th { background: #4e73df; color: white; }
            .badge {
                padding: 3px 8px;
                border-radius: 20px;
                font-size: 11px;
                color: white;
                display: inline-block;
            }
            .badge-pending { background: #f6c23e; color: #333; }
            .badge-confirmed { background: #4e73df; }
            .badge-completed { background: #1cc88a; }
            .badge-cancelled { background: #e74a3b; }
            .next-card {
                background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
                color: white;
            }
            .next-card h2 { color: white; border-bottom-color: white; }
            @media (max-width: 768px) {
                .header { flex-direction: column; text-align: center; gap: 10px; }
                .stats-grid { grid-template-columns: 1fr 1fr; }
            }
        </style>
    </head>
    <body>
        <div class="header">
             <?php include 'navbar.php'; ?>
            <!-- <h1>🏥 ClinicDesk - <?= ucfirst($role) ?> Dashboard</h1> -->
            <!-- <div class="nav-links">
                <a href="index.php?page=dashboard">Dashboard</a>
                <?php if ($role == 'admin'): ?>
                    <a href="users_list.php">👥 Users</a>
                    <a href="doctors_list.php">👨‍⚕️ Doctors</a>
                    <a href="specializations.php">📚 Specializations</a>
                    <a href="reports.php">📊 Reports</a>
                <?php endif; ?>
                <a href="appointments.php">📅 Appointments</a>
                <?php if ($role == 'patient'): ?>
                    <a href="prescriptions.php">📋 Prescriptions</a>
                <?php endif; ?>
                <a href="index.php?page=logout" class="logout-btn">🚪 Logout</a>
            </div> -->
        </div>
        
        <div class="container">
           
            <div class="welcome-card">
                <h2>Welcome back, <?= $_SESSION['user_name'] ?>! 👋</h2>
                <p><?= date('l, F j, Y') ?> | Logged in as <strong><?= ucfirst($role) ?></strong></p>
            </div>
            
            <!-- ========== إحصائيات الأدمن ========== -->
            <?php if ($role == 'admin'): ?>
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-number"><?= $total_users ?></div><div class="stat-label">Total Users</div></div>
                <div class="stat-card"><div class="stat-number"><?= $role_data['doctor'] ?></div><div class="stat-label">Doctors</div></div>
                <div class="stat-card"><div class="stat-number"><?= $role_data['patient'] ?></div><div class="stat-label">Patients</div></div>
                <div class="stat-card"><div class="stat-number"><?= $today_appointments ?></div><div class="stat-label">Appointments Today</div></div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-number"><?= $weekly_data['pending'] ?></div><div class="stat-label">Pending This Week</div></div>
                <div class="stat-card"><div class="stat-number"><?= $weekly_data['confirmed'] ?></div><div class="stat-label">Confirmed This Week</div></div>
                <div class="stat-card"><div class="stat-number"><?= $weekly_data['completed'] ?></div><div class="stat-label">Completed This Week</div></div>
                <div class="stat-card"><div class="stat-number"><?= $weekly_data['cancelled'] ?></div><div class="stat-label">Cancelled This Week</div></div>
            </div>
            
            <div class="card">
                <h2>📋 Recent Appointments</h2>
                <?php if ($recent_appointments->num_rows > 0): ?>
                <table>
                    <thead><tr><th>Patient</th><th>Doctor</th><th>Date</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php while($apt = $recent_appointments->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($apt['patient_name']) ?></td>
                            <td><?= htmlspecialchars($apt['doctor_name']) ?></td>
                            <td><?= date('d M Y', strtotime($apt['appt_date'])) ?></td>
                            <td><span class="badge badge-<?= $apt['status'] ?>"><?= ucfirst($apt['status']) ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>No appointments yet.</p>
                <?php endif; ?>
            </div>
            
            <!-- ========== إحصائيات الدكتور ========== -->
            <?php elseif ($role == 'doctor'): ?>
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-number"><?= $today_appointments ?></div><div class="stat-label">Today's Appointments</div></div>
                <div class="stat-card"><div class="stat-number"><?= $pending_count ?></div><div class="stat-label">Pending</div></div>
                <div class="stat-card"><div class="stat-number"><?= $confirmed_count ?></div><div class="stat-label">Confirmed</div></div>
                <div class="stat-card"><div class="stat-number"><?= $completed_count ?></div><div class="stat-label">Completed</div></div>
            </div>
            
            <div class="card">
                <h2>📅 Today's Appointments</h2>
                <?php if ($today_list->num_rows > 0): ?>
                <table>
                    <thead><tr><th>Time</th><th>Patient</th><th>Reason</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php while($apt = $today_list->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('h:i A', strtotime($apt['appt_time'])) ?></td>
                            <td><?= htmlspecialchars($apt['patient_name']) ?></td>
                            <td><?= htmlspecialchars($apt['reason'] ?? '-') ?></td>
                            <td><span class="badge badge-<?= $apt['status'] ?>"><?= ucfirst($apt['status']) ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>No appointments today. 🎉</p>
                <?php endif; ?>
            </div>
            
            <div class="card">
                <h2>📌 Upcoming Appointments</h2>
                <?php if ($upcoming->num_rows > 0): ?>
                <table>
                    <thead><tr><th>Date</th><th>Time</th><th>Patient</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php while($apt = $upcoming->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($apt['appt_date'])) ?></td>
                            <td><?= date('h:i A', strtotime($apt['appt_time'])) ?></td>
                            <td><?= htmlspecialchars($apt['patient_name']) ?></td>
                            <td><span class="badge badge-<?= $apt['status'] ?>"><?= ucfirst($apt['status']) ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>No upcoming appointments.</p>
                <?php endif; ?>
            </div>
            
            <!-- ========== إحصائيات المريض ========== -->
            <?php elseif ($role == 'patient'): ?>
            <div class="stats-grid">
                <div class="stat-card"><div class="stat-number"><?= $active_count ?></div><div class="stat-label">Active Appointments</div></div>
                <div class="stat-card"><div class="stat-number"><?= $completed_count ?></div><div class="stat-label">Completed</div></div>
                <div class="stat-card"><div class="stat-number"><?= $cancelled_count ?></div><div class="stat-label">Cancelled</div></div>
                <div class="stat-card"><div class="stat-number"><?= $prescriptions_count ?></div><div class="stat-label">Prescriptions</div></div>
            </div>
            
            <?php if ($next_appointment): ?>
            <div class="card next-card">
                <h2>📅 Next Appointment</h2>
                <p><strong>Doctor:</strong> <?= htmlspecialchars($next_appointment['doctor_name']) ?></p>
                <p><strong>Specialization:</strong> <?= htmlspecialchars($next_appointment['specialization']) ?></p>
                <p><strong>Date:</strong> <?= date('l, d M Y', strtotime($next_appointment['appt_date'])) ?></p>
                <p><strong>Time:</strong> <?= date('h:i A', strtotime($next_appointment['appt_time'])) ?></p>
            </div>
            <?php endif; ?>
            
            <div class="card">
                <h2>📋 Recent Appointments</h2>
                <?php if ($recent_patient_appointments->num_rows > 0): ?>
                <table>
                    <thead><tr><th>Doctor</th><th>Date</th><th>Time</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php while($apt = $recent_patient_appointments->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($apt['doctor_name']) ?></td>
                            <td><?= date('d M Y', strtotime($apt['appt_date'])) ?></td>
                            <td><?= date('h:i A', strtotime($apt['appt_time'])) ?></td>
                            <td><span class="badge badge-<?= $apt['status'] ?>"><?= ucfirst($apt['status']) ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>No appointments yet.</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- ========== القائمة السريعة للجميع ========== -->
            <div class="card">
                <h2>⚡ Quick Actions</h2>
                <div class="menu-grid">
                    <a href="appointments.php" class="menu-item">📅 Appointments</a>
                    <?php if ($role == 'patient'): ?>
                        <a href="prescriptions.php" class="menu-item">📋 My Prescriptions</a>
                    <?php endif; ?>
                    <?php if ($role == 'admin'): ?>
                        <a href="users_list.php" class="menu-item">👤 Manage Users</a>
                        <a href="doctors_list.php" class="menu-item">👨‍⚕️ Manage Doctors</a>
                        <a href="specializations.php" class="menu-item">📚 Specializations</a>
                        <a href="reports.php" class="menu-item">📊 Reports</a>
                    <?php endif; ?>
                    <?php if ($role == 'doctor'): ?>
                        <a href="appointments.php" class="menu-item">✅ Manage Schedule</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

// ============================================
// تسجيل الخروج (LOGOUT)
// ============================================
elseif ($page == 'logout') {
    session_destroy();
    redirect('index.php?page=login');
}

// ============================================
// صفحة غير موجودة (404)
// ============================================
else {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>404 - Page Not Found</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: #f4f6f9;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
            }
            .error-box {
                text-align: center;
                background: white;
                padding: 50px;
                border-radius: 10px;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
            }
            h1 { font-size: 80px; color: #4e73df; margin: 0; }
            p { color: #666; }
            a { color: #4e73df; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class="error-box">
            <h1>404</h1>
            <p>Page not found</p>
            <a href="index.php">← Back to Dashboard</a>
        </div>
    </body>
    </html>
    <?php
}
?>