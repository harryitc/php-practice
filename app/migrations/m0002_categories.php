<?php

/**
 * Categories Migration
 */
class M0002Categories
{
    /**
     * Create categories table
     */
    public function up()
    {
        $db = Database::getInstance();
        
        $sql = "CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB";
        
        $db->query($sql)->execute();
        
        // Insert default categories
        $categories = [
            ['name' => 'Skin Care', 'description' => 'Products for skin care and treatment'],
            ['name' => 'Hair Care', 'description' => 'Products for hair care and treatment'],
            ['name' => 'Makeup', 'description' => 'Makeup and cosmetic products'],
            ['name' => 'Body Care', 'description' => 'Products for body care and treatment']
        ];
        
        $sql = "INSERT INTO categories (name, description) VALUES (:name, :description)";
        
        foreach ($categories as $category) {
            $db->query($sql)->bind([
                'name' => $category['name'],
                'description' => $category['description']
            ])->execute();
        }
    }
    
    /**
     * Drop categories table
     */
    public function down()
    {
        $db = Database::getInstance();
        $sql = "DROP TABLE IF EXISTS categories";
        $db->query($sql)->execute();
    }
}
