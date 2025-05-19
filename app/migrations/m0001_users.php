<?php

/**
 * Users Migration
 */
class M0001Users
{
    /**
     * Create users table
     */
    public function up()
    {
        $db = Database::getInstance();
        
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB";
        
        $db->query($sql)->execute();
        
        // Create default admin user
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (name, email, password, role) 
                VALUES (:name, :email, :password, :role)";
        
        $db->query($sql)->bind([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => $password,
            'role' => 'admin'
        ])->execute();
    }
    
    /**
     * Drop users table
     */
    public function down()
    {
        $db = Database::getInstance();
        $sql = "DROP TABLE IF EXISTS users";
        $db->query($sql)->execute();
    }
}
