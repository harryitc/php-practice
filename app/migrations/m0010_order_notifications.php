<?php

/**
 * Order Notifications Migration
 */
class M0010OrderNotifications
{
    /**
     * Create order_notifications table
     */
    public function up()
    {
        $db = Database::getInstance();
        
        // Create order_notifications table
        $sql = "CREATE TABLE IF NOT EXISTS order_notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            user_id INT,
            notification_type ENUM('email', 'sms', 'push', 'system') NOT NULL,
            event_type VARCHAR(50) NOT NULL,
            recipient VARCHAR(255) NOT NULL,
            subject VARCHAR(255),
            message TEXT NOT NULL,
            status ENUM('pending', 'sent', 'failed', 'delivered', 'read') NOT NULL DEFAULT 'pending',
            sent_at DATETIME,
            delivered_at DATETIME,
            read_at DATETIME,
            error_message TEXT,
            retry_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_order_id (order_id),
            INDEX idx_status (status),
            INDEX idx_event_type (event_type),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB";
        
        $db->query($sql)->execute();
    }
    
    /**
     * Drop order_notifications table
     */
    public function down()
    {
        $db = Database::getInstance();
        $sql = "DROP TABLE IF EXISTS order_notifications";
        $db->query($sql)->execute();
    }
}
