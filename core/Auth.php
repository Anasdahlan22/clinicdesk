<?php
// core/Auth.php - إدارة المصادقة
declare(strict_types=1);

class Auth
{
    private const SESSION_KEY = 'user';
    
    public static function login(array $user): void
    {
        session_regenerate_id(true);
        
        $_SESSION[self::SESSION_KEY] = [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'role' => $user['role'],
            'email' => $user['email'],
        ];
    }
    
    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        redirect('index.php?page=auth&action=login');
    }
    
    public static function check(): bool
    {
        return isset($_SESSION[self::SESSION_KEY]);
    }
    
    public static function currentUser(): ?array
    {
        return $_SESSION[self::SESSION_KEY] ?? null;
    }
    
    public static function role(): string
    {
        return $_SESSION[self::SESSION_KEY]['role'] ?? '';
    }
    
    public static function id(): int
    {
        return (int)($_SESSION[self::SESSION_KEY]['id'] ?? 0);
    }
    
    public static function requireRole(string ...$roles): void
    {
        if (!self::check()) {
            redirect('index.php?page=auth&action=login');
        }
        
        if (!in_array(self::role(), $roles, true)) {
            http_response_code(403);
            echo "<h1>403 - Access Denied</h1>";
            echo "<p>You don't have permission to access this page.</p>";
            exit;
        }
    }
}