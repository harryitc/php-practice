<?php

/**
 * Orders Migration
 */
class M0004Orders
{
    /**
     * Create orders table
     */
    public function up()
    {
        $db = Database::getInstance();
        
        // Create orders table
        $sql = "CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
            shipping_address TEXT NOT NULL,
            shipping_city VARCHAR(100) NOT NULL,
            shipping_state VARCHAR(100) NOT NULL,
            shipping_zip VARCHAR(20) NOT NULL,
            shipping_country VARCHAR(100) NOT NULL,
            payment_method VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB";
        
        $db->query($sql)->execute();
    }
    
    /**
     * Drop orders table
     */
    public function down()
    {
        $db = Database::getInstance();
        $sql = "DROP TABLE IF EXISTS orders";
        $db->query($sql)->execute();
    }
}
