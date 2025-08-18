<?php
/**
 * Database Configuration and Connection
 * This file handles the database connection for BookMarket
 * 
 * @author BookMarket Team
 * @version 1.0
 */

// Database configuration constants
define('DB_HOST', 'localhost');        // Database host (usually localhost for XAMPP)
define('DB_NAME', 'bookmarket_DataBase'); // Database name
define('DB_USER', 'root');             // Database username (default for XAMPP)
define('DB_PASS', '');                 // Database password (empty for XAMPP default)
define('DB_CHARSET', 'utf8mb4');       // Character set for proper text handling

/**
 * Database connection class
 * Handles all database operations with proper error handling
 */
class Database {
    private $connection;
    private static $instance = null;
    
    /**
     * Private constructor to prevent direct instantiation
     * Establishes database connection
     */
    private function __construct() {
        try {
            // Create PDO connection with error handling
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            // Log error and display user-friendly message
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database connection failed. Please try again later.");
        }
    }
    
    /**
     * Get singleton instance of database connection
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get the PDO connection object
     * @return PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Execute a SELECT query and return results
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters for the query
     * @return array|false Query results or false on failure
     */
    public function select($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Database Select Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Execute a SELECT query and return single row
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters for the query
     * @return array|false Single row or false on failure
     */
    public function selectOne($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Database SelectOne Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Execute INSERT, UPDATE, DELETE queries
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters for the query
     * @return int|false Number of affected rows or false on failure
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Database Execute Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Execute INSERT query and return the last insert ID
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters for the query
     * @return int|false Last insert ID or false on failure
     */
    public function insert($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            error_log("Database Insert Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Begin a database transaction
     * @return bool True on success, false on failure
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit a database transaction
     * @return bool True on success, false on failure
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Rollback a database transaction
     * @return bool True on success, false on failure
     */
    public function rollback() {
        return $this->connection->rollback();
    }
    
    /**
     * Close the database connection
     */
    public function close() {
        $this->connection = null;
    }
}

/**
 * Helper function to get database instance
 * @return Database
 */
function getDB() {
    return Database::getInstance();
}

/**
 * Helper function to get database connection
 * @return PDO
 */
function getConnection() {
    return getDB()->getConnection();
}
?>