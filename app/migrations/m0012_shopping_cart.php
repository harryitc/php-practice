<?php

/**
 * Shopping Cart Migration
 */
class M0012ShoppingCart
{
    /**
     * Create shopping_cart table
     */
    public function up()
    {
        $db = Database::getInstance();
        
        // Create shopping_cart table
        $sql = "CREATE TABLE IF NOT EXISTS shopping_cart (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            session_id VARCHAR(255),
            product_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            price DECIMAL(10, 2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_session_id (session_id),
            INDEX idx_product_id (product_id),
            UNIQUE KEY unique_cart_item (user_id, product_id, session_id)
        ) ENGINE=InnoDB";
        
        $db->query($sql)->execute();
    }
    
    /**
     * Drop shopping_cart table
     */
    public function down()
    {
        $db = Database::getInstance();
        $sql = "DROP TABLE IF EXISTS shopping_cart";
        $db->query($sql)->execute();
    }
}
