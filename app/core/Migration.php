<?php

/**
 * Migration Class
 * 
 * This class provides functionality for database migrations.
 */
class Migration
{
    private $db;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->createMigrationsTable();
    }
    
    /**
     * Create migrations table if it doesn't exist
     */
    private function createMigrationsTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB";
        
        $this->db->query($sql)->execute();
    }
    
    /**
     * Get all applied migrations
     * 
     * @return array
     */
    public function getAppliedMigrations()
    {
        $sql = "SELECT migration FROM migrations";
        $result = $this->db->query($sql)->fetchAll();
        
        return array_map(function($item) {
            return $item['migration'];
        }, $result);
    }
    
    /**
     * Save migration to the migrations table
     * 
     * @param string $migration
     */
    public function saveMigration($migration)
    {
        $sql = "INSERT INTO migrations (migration) VALUES (:migration)";
        $this->db->query($sql)->bind(['migration' => $migration])->execute();
    }
    
    /**
     * Apply migrations
     * 
     * @return array Applied migrations
     */
    public function applyMigrations()
    {
        // Create database if it doesn't exist
        $this->createDatabase();
        
        // Get all migration files
        $files = scandir('app/migrations');
        $migrations = array_diff($files, ['.', '..']);
        
        // Get applied migrations
        $appliedMigrations = $this->getAppliedMigrations();
        
        // Determine which migrations need to be applied
        $newMigrations = [];
        
        foreach ($migrations as $migration) {
            if (!in_array($migration, $appliedMigrations)) {
                $newMigrations[] = $migration;
            }
        }
        
        // Sort migrations by name (which should include timestamp)
        sort($newMigrations);
        
        // Apply new migrations
        $appliedMigrations = [];
        
        foreach ($newMigrations as $migration) {
            require_once "app/migrations/{$migration}";
            
            // Extract class name from file name (m0001_initial.php -> M0001Initial)
            $className = $this->getClassNameFromFile($migration);
            
            if (class_exists($className)) {
                $instance = new $className();
                
                echo "Applying migration {$migration}" . PHP_EOL;
                $instance->up();
                echo "Applied migration {$migration}" . PHP_EOL;
                
                $this->saveMigration($migration);
                $appliedMigrations[] = $migration;
            }
        }
        
        return $appliedMigrations;
    }
    
    /**
     * Create database if it doesn't exist
     */
    private function createDatabase()
    {
        $config = require 'app/config/database.php';
        $dbName = $config['database'];
        
        try {
            // Connect without specifying database
            $dsn = "mysql:host={$config['host']};port={$config['port']}";
            $pdo = new PDO($dsn, $config['username'], $config['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create database if it doesn't exist
            $sql = "CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET {$config['charset']} COLLATE {$config['collation']}";
            $pdo->exec($sql);
            
            echo "Database '{$dbName}' created or already exists." . PHP_EOL;
        } catch (PDOException $e) {
            die("Error creating database: " . $e->getMessage());
        }
    }
    
    /**
     * Extract class name from migration file name
     * 
     * @param string $fileName
     * @return string
     */
    private function getClassNameFromFile($fileName)
    {
        // Remove extension
        $withoutExtension = pathinfo($fileName, PATHINFO_FILENAME);
        
        // Convert to CamelCase
        $parts = explode('_', $withoutExtension);
        $className = '';
        
        foreach ($parts as $part) {
            $className .= ucfirst($part);
        }
        
        return $className;
    }
}
