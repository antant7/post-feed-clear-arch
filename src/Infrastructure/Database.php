<?php

namespace App\Infrastructure;

use PDO;
use PDOException;
use Exception;

class Database implements IDatabase {
    private mixed $config;
    private ?PDO $conn;
    
    public function __construct() {
        $this->config = require __DIR__ . '/../../config/database.php';
        
        $this->connect();
    }
    
    public function getConnection(): PDO {
        return $this->conn;
    }

    private function connect(): void
    {
        $this->conn = null;
        
        try {
            if (empty($this->config['host']) || empty($this->config['db_name']) ||
                empty($this->config['username']) || empty($this->config['port'])) {
                throw new Exception('Connection Error: Missing required database configuration parameters');
            }
            
            if (!array_key_exists('password', $this->config)) {
                throw new Exception('Connection Error: Missing password configuration parameter');
            }
            
            if (!is_numeric($this->config['port'])) {
                throw new Exception('Connection Error: Port must be numeric');
            }
            
            $dsn = "pgsql:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['db_name']}";
            $this->conn = new PDO($dsn, $this->config['username'], $this->config['password']);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            throw new Exception('Connection Error: ' . $e->getMessage());
        }
    }
}