<?php
// core/Database.php - اتصال قاعدة البيانات (Singleton)
declare(strict_types=1);

class Database
{
    private static ?Database $instance = null;
    private mysqli $conn;
    
    private function __construct()
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        
        try {
            $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            $this->conn->set_charset(DB_CHARSET);
        } catch (mysqli_sql_exception $e) {
            error_log('Database error: ' . $e->getMessage());
            throw new RuntimeException('Database connection failed');
        }
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function query(string $sql, string $types = '', array $params = []): mixed
    {
        try {
            $stmt = $this->conn->prepare($sql);
            
            if ($types !== '' && !empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            
            // إذا كانت SELECT
            if ($stmt->result_metadata()) {
                $result = $stmt->get_result();
                $stmt->close();
                return $result;
            }
            
            // INSERT/UPDATE/DELETE
            $stmt->close();
            return true;
            
        } catch (mysqli_sql_exception $e) {
            error_log('Query failed: ' . $e->getMessage());
            throw new RuntimeException('Database query failed');
        }
    }
    
    public function lastInsertId(): int
    {
        return (int)$this->conn->insert_id;
    }
    
    public function beginTransaction(): void
    {
        $this->conn->begin_transaction();
    }
    
    public function commit(): void
    {
        $this->conn->commit();
    }
    
    public function rollback(): void
    {
        $this->conn->rollback();
    }
}