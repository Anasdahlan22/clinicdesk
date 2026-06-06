<?php
// controllers/DashboardController.php
class DashboardController
{
    public function index(): void
    {
        Auth::requireRole('admin', 'doctor', 'patient');
        
        $user = Auth::currentUser();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title><?= APP_NAME ?> - Dashboard</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
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
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                .header h1 {
                    font-size: 24px;
                }
                .logout-form button {
                    background: #e74a3b;
                    color: white;
                    border: none;
                    padding: 8px 20px;
                    border-radius: 5px;
                    cursor: pointer;
                    font-size: 14px;
                }
                .logout-form button:hover {
                    background: #c0392b;
                }
                .container {
                    max-width: 1200px;
                    margin: 30px auto;
                    padding: 0 20px;
                }
                .welcome-card {
                    background: white;
                    border-radius: 10px;
                    padding: 25px;
                    margin-bottom: 25px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                .welcome-card h2 {
                    color: #333;
                    margin-bottom: 15px;
                }
                .info-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                    gap: 20px;
                    margin-top: 20px;
                }
                .info-card {
                    background: white;
                    border-radius: 10px;
                    padding: 20px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    border-left: 4px solid #4e73df;
                }
                .info-card h3 {
                    color: #4e73df;
                    margin-bottom: 10px;
                    font-size: 16px;
                }
                .info-card p {
                    color: #666;
                    font-size: 14px;
                }
                .status-badge {
                    background: #27ae60;
                    color: white;
                    padding: 5px 10px;
                    border-radius: 20px;
                    font-size: 12px;
                    display: inline-block;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1><?= APP_NAME ?> - Dashboard</h1>
                <form method="POST" action="index.php?page=auth&action=logout" class="logout-form">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                    <button type="submit">Logout</button>
                </form>
            </div>
            
            <div class="container">
                <div class="welcome-card">
                    <h2>Welcome, <?= e($user['name']) ?>!</h2>
                    <p>You are logged in as <strong><?= e($user['role']) ?></strong></p>
                </div>
                
                <div class="info-grid">
                    <div class="info-card">
                        <h3>📊 System Status</h3>
                        <p>✅ Database Connected</p>
                        <p>✅ Authentication Working</p>
                        <p><span class="status-badge">Online</span></p>
                    </div>
                    <div class="info-card">
                        <h3>👤 Your Information</h3>
                        <p><strong>Name:</strong> <?= e($user['name']) ?></p>
                        <p><strong>Email:</strong> <?= e($user['email']) ?></p>
                        <p><strong>Role:</strong> <?= e($user['role']) ?></p>
                    </div>
                    <div class="info-card">
                        <h3>🚀 Quick Links</h3>
                        <p>• Appointments Management</p>
                        <p>• Patient Records</p>
                        <p>• Reports</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
}