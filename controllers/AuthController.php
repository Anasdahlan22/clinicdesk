<?php
// controllers/AuthController.php
class AuthController
{
    public function login(): void
    {
        if (Auth::check()) {
            redirect('index.php?page=dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLogin();
            return;
        }
        
        $this->showLoginForm();
    }
    
    private function handleLogin(): void
    {
        if (!isset($_POST['csrf_token']) || !CSRF::validateToken($_POST['csrf_token'])) {
            flashError('Invalid request');
            redirect('index.php?page=auth&action=login');
        }
        
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            flashError('Please enter email and password');
            redirect('index.php?page=auth&action=login');
        }
        
        $db = Database::getInstance();
        $result = $db->query("SELECT * FROM users WHERE email = ?", 's', [$email]);
        $user = $result->fetch_assoc();
        
        if (!$user || !password_verify($password, $user['password'])) {
            flashError('Invalid email or password');
            redirect('index.php?page=auth&action=login');
        }
        
        if (!$user['is_active']) {
            flashError('Account is deactivated');
            redirect('index.php?page=auth&action=login');
        }
        
        Auth::login($user);
        redirect('index.php?page=dashboard');
    }
    
    private function showLoginForm(): void
    {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title><?= APP_NAME ?> - Login</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background: #f0f2f5;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                }
                .login-box {
                    background: white;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 0 20px rgba(0,0,0,0.1);
                    width: 350px;
                    text-align: center;
                }
                .login-box h2 {
                    color: #4e73df;
                    margin-bottom: 20px;
                }
                .form-group {
                    margin-bottom: 15px;
                    text-align: left;
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
                button {
                    width: 100%;
                    padding: 10px;
                    background: #4e73df;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    font-size: 16px;
                }
                button:hover {
                    background: #224abe;
                }
                .alert {
                    padding: 10px;
                    border-radius: 5px;
                    margin-bottom: 15px;
                }
                .alert-danger {
                    background: #f8d7da;
                    color: #721c24;
                }
                .demo-box {
                    margin-top: 20px;
                    padding: 10px;
                    background: #e9ecef;
                    border-radius: 5px;
                    font-size: 13px;
                }
                code {
                    background: #fff;
                    padding: 2px 5px;
                    border-radius: 3px;
                }
            </style>
        </head>
        <body>
            <div class="login-box">
                <h2><?= APP_NAME ?></h2>
                
                <?php if (isset($_SESSION['flash'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['flash']['message'] ?>
                    </div>
                    <?php unset($_SESSION['flash']); ?>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken() ?>">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required autofocus>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>
                    <button type="submit">Login</button>
                </form>
                
                <div class="demo-box">
                    <strong>Demo Login:</strong><br>
                    Email: <code>admin@clinicdesk.com</code><br>
                    Password: <code>admin123</code>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
    
    public function logout(): void
    {
        Auth::logout();
    }
}