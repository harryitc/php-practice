<?php

/**
 * Order Tracking Migration
 */
class M0007OrderTracking
{
    /**
     * Create order_tracking table
     */
    public function up()
    {
        $db = Database::getInstance();
        
        // Create order_tracking table
        $sql = "CREATE TABLE IF NOT EXISTS order_tracking (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            tracking_number VARCHAR(100),
            carrier VARCHAR(50),
            status VARCHAR(50) NOT NULL,
            location VARCHAR(255),
            description TEXT,
            tracking_date DATETIME NOT NULL,
            estimated_delivery DATETIME,
            is_delivered BOOLEAN DEFAULT FALSE,
            proof_of_delivery TEXT,
            recipient_name VARCHAR(100),
            signature_required BOOLEAN DEFAULT FALSE,
            signature_obtained BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            INDEX idx_order_id (order_id),
            INDEX idx_tracking_number (tracking_number),
            INDEX idx_tracking_date (tracking_date),
            INDEX idx_status (status)
        ) ENGINE=InnoDB";
        
        $db->query($sql)->execute();
    }
    
    /**
     * Drop order_tracking table
     */
    public function down()
    {
        $db = Database::getInstance();
        $sql = "DROP TABLE IF EXISTS order_tracking";
        $db->query($sql)->execute();
    }
}
