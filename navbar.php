<?php
// navbar.php - قائمة التنقل الموحدة (بدون تكرار)
if (!isset($_SESSION)) {
    session_start();
}
?>

<style>
    .navbar {
        background: #4e73df;
        color: white;
        padding: 15px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        /* margin-bottom: 20px; */
       
    }
    .navbar .logo {
        font-size: 24px;
        font-weight: bold;
    }
    .navbar .logo a {
        color: white;
        text-decoration: none;
    }
    .navbar .nav-links {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
         position: absolute;
        right: 3em;
    }
    .navbar .nav-links a {
        color: white;
        text-decoration: none;
        padding: 8px 15px;
        border-radius: 5px;
        transition: background 0.3s;
    }
    .navbar .nav-links a:hover {
        background: rgba(255,255,255,0.2);
    }
    .navbar .nav-links .logout-btn {
        background: #e74a3b;
    }
    .navbar .nav-links .logout-btn:hover {
        background: #c0392b;
    }
    @media (max-width: 768px) {
        .navbar {
            flex-direction: column;
            gap: 10px;
            text-align: center;
        }
        .navbar .nav-links {
            justify-content: center;
        }
    }
</style>

<div class="navbar">
    <div class="logo">
        <a href="index.php?page=dashboard">🏥 ClinicDesk</a>
    </div>
    <div class="nav-links">
        <a href="index.php?page=dashboard">📊 Dashboard</a>
        
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
            <a href="users_list.php">👥 Users</a>
            <a href="doctors_list.php">👨‍⚕️ Doctors</a>
            <a href="specializations.php">📚 Specializations</a>
            <a href="reports.php">📊 Reports</a>
        <?php endif; ?>
        
        <a href="appointments.php">📅 Appointments</a>
        
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'patient'): ?>
            <a href="prescriptions.php">📋 Prescriptions</a>
        <?php endif; ?>
        
        <a href="index.php?page=logout" class="logout-btn">🚪 Logout</a>
    </div>
</div>