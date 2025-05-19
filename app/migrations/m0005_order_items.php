<?php

/**
 * Order Items Migration
 */
class M0005OrderItems
{
    /**
     * Create order_items table
     */
    public function up()
    {
        $db = Database::getInstance();
        
        // Create order_items table
        $sql = "CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT,
            quantity INT NOT NULL,
            price DECIMAL(10, 2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
        ) ENGINE=InnoDB";
        
        $db->query($sql)->execute();
    }
    
    /**
     * Drop order_items table
     */
    public function down()
    {
        $db = Database::getInstance();
        $sql = "DROP TABLE IF EXISTS order_items";
        $db->query($sql)->execute();
    }
}
