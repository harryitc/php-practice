<?php

/**
 * Database Class
 *
 * This class handles database connections and provides methods for executing queries.
 */
class Database
{
    private static $instance = null;
    private $connection;
    private $statement;
    private $config;

    /**
     * Constructor - Establishes a database connection
     */
    private function __construct()
    {
        $this->config = require_once 'app/config/database.php';

        $dsn = "mysql:host={$this->config['host']};dbname={$this->config['database']};charset={$this->config['charset']};port={$this->config['port']}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->connection = new PDO($dsn, $this->config['username'], $this->config['password'], $options);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Get database instance (Singleton pattern)
     *
     * @return Database
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Prepare a SQL statement
     *
     * @param string $sql
     * @return Database
     */
    public function query($sql)
    {
        $this->statement = $this->connection->prepare($sql);
        return $this;
    }

    /**
     * Bind values to prepared statement
     *
     * @param array $params
     * @return Database
     */
    public function bind($params = [])
    {
        if (!empty($params)) {
            foreach ($params as $param => $value) {
                // Handle named parameters (starting with :) and positional parameters
                $paramName = is_numeric($param) ? $param + 1 : (strpos($param, ':') === 0 ? $param : ':' . $param);
                $this->statement->bindValue(
                    $paramName,
                    $value,
                    $this->getParamDataType($value)
                );
            }
        }

        return $this;
    }

    /**
     * Determine the PDO data type based on the value
     *
     * @param mixed $value
     * @return int
     */
    private function getParamDataType($value)
    {
        switch (true) {
            case is_int($value):
                return PDO::PARAM_INT;
            case is_bool($value):
                return PDO::PARAM_BOOL;
            case is_null($value):
                return PDO::PARAM_NULL;
            default:
                return PDO::PARAM_STR;
        }
    }

    /**
     * Execute the prepared statement
     *
     * @return bool
     */
    public function execute()
    {
        return $this->statement->execute();
    }

    /**
     * Execute a query and return all results
     *
     * @param array $params
     * @return array
     */
    public function fetchAll($params = [])
    {
        try {
            $this->bind($params)->execute();
            return $this->statement->fetchAll();
        } catch (PDOException $e) {
            error_log("Database fetchAll error: " . $e->getMessage());
            throw new Exception("Database query failed: " . $e->getMessage());
        }
    }

    /**
     * Execute a query and return a single result
     *
     * @param array $params
     * @return array|bool
     */
    public function fetch($params = [])
    {
        try {
            $this->bind($params)->execute();
            return $this->statement->fetch();
        } catch (PDOException $e) {
            error_log("Database fetch error: " . $e->getMessage());
            throw new Exception("Database query failed: " . $e->getMessage());
        }
    }

    /**
     * Execute a query and return the number of affected rows
     *
     * @param array $params
     * @return int
     */
    public function rowCount($params = [])
    {
        $this->bind($params)->execute();
        return $this->statement->rowCount();
    }

    /**
     * Get the last inserted ID
     *
     * @return string
     */
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Begin a transaction
     *
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit a transaction
     *
     * @return bool
     */
    public function commit()
    {
        return $this->connection->commit();
    }

    /**
     * Rollback a transaction
     *
     * @return bool
     */
    public function rollback()
    {
        return $this->connection->rollBack();
    }
}
