<?php

/**
 * Order Status History Migration
 */
class M0006OrderStatusHistory
{
    /**
     * Create order_status_history table
     */
    public function up()
    {
        $db = Database::getInstance();
        
        // Create order_status_history table
        $sql = "CREATE TABLE IF NOT EXISTS order_status_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            old_status VARCHAR(50),
            new_status VARCHAR(50) NOT NULL,
            changed_by INT,
            change_reason TEXT,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_order_id (order_id),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB";
        
        $db->query($sql)->execute();
    }
    
    /**
     * Drop order_status_history table
     */
    public function down()
    {
        $db = Database::getInstance();
        $sql = "DROP TABLE IF EXISTS order_status_history";
        $db->query($sql)->execute();
    }
}
