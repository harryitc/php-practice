<?php

/**
 * Order Notes Migration
 */
class M0008OrderNotes
{
    /**
     * Create order_notes table
     */
    public function up()
    {
        $db = Database::getInstance();
        
        // Create order_notes table
        $sql = "CREATE TABLE IF NOT EXISTS order_notes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            user_id INT,
            note_type ENUM('internal', 'customer', 'system') NOT NULL DEFAULT 'internal',
            title VARCHAR(255),
            content TEXT NOT NULL,
            is_visible_to_customer BOOLEAN DEFAULT FALSE,
            priority ENUM('low', 'normal', 'high') DEFAULT 'normal',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_order_id (order_id),
            INDEX idx_note_type (note_type),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB";
        
        $db->query($sql)->execute();
    }
    
    /**
     * Drop order_notes table
     */
    public function down()
    {
        $db = Database::getInstance();
        $sql = "DROP TABLE IF EXISTS order_notes";
        $db->query($sql)->execute();
    }
}
