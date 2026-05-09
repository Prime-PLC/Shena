<?php
/**
 * Database Connection Class
 */
class Database 
{
    private static $instance = null;
    private $connection = null;
    
    private function __construct()
    {
    }

    private function connect()
    {
        if ($this->connection instanceof PDO) {
            return;
        }

        try {
            // When local_config.php is present (re-structure branch), prefer the
            // LOCAL_ server vars it injects so no production DB is ever touched.
            if (defined('LOCAL_OVERRIDE_APPLIED')) {
                $host    = $_SERVER['LOCAL_DB_HOST']    ?? DB_HOST;
                $dbname  = $_SERVER['LOCAL_DB_NAME']    ?? DB_NAME;
                $user    = $_SERVER['LOCAL_DB_USER']    ?? DB_USER;
                $pass    = $_SERVER['LOCAL_DB_PASS']    ?? DB_PASS;
                $charset = $_SERVER['LOCAL_DB_CHARSET'] ?? DB_CHARSET;
                $port    = $_SERVER['LOCAL_DB_PORT']    ?? '3306';
            } else {
                $host = DB_HOST; $dbname = DB_NAME; $user = DB_USER;
                $pass = DB_PASS; $charset = DB_CHARSET; $port = '3306';
            }
            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
            $this->connection = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection()
    {
        $this->connect();
        return $this->connection;
    }
    
    public function query($sql, $params = [])
    {
        try {
            $this->connect();
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Params: " . print_r($params, true));
            throw new Exception("Database query failed: " . $e->getMessage());
        }
    }
    
    public function fetch($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    public function fetchAll($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function fetchColumn($sql, $params = [], $column = 0)
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn($column);
    }
    
    public function insert($table, $data)
    {
        $fields = array_keys($data);
        $placeholders = ':' . implode(', :', $fields);
        $sql = "INSERT INTO {$table} (" . implode(', ', $fields) . ") VALUES ({$placeholders})";
        
        $this->query($sql, $data);
        return $this->connection->lastInsertId();
    }
    
    public function update($table, $data, $where, $whereParams = [])
    {
        $fields = [];
        foreach (array_keys($data) as $field) {
            $fields[] = "{$field} = :{$field}";
        }
        
        $sql = "UPDATE {$table} SET " . implode(', ', $fields) . " WHERE {$where}";
        
        $params = array_merge($data, $whereParams);
        return $this->query($sql, $params);
    }
    
    public function delete($table, $where, $params = [])
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $params);
    }
    
    public function execute($sql, $params = [])
    {
        return $this->query($sql, $params);
    }
}
