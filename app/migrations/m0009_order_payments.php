<?php

/**
 * Order Payments Migration
 */
class M0009OrderPayments
{
    /**
     * Create order_payments table
     */
    public function up()
    {
        $db = Database::getInstance();
        
        // Create order_payments table
        $sql = "CREATE TABLE IF NOT EXISTS order_payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            payment_method VARCHAR(50) NOT NULL,
            payment_status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded', 'partially_refunded') NOT NULL DEFAULT 'pending',
            amount DECIMAL(10, 2) NOT NULL,
            currency VARCHAR(3) DEFAULT 'USD',
            transaction_id VARCHAR(255),
            gateway VARCHAR(50),
            gateway_response TEXT,
            payment_date DATETIME,
            refund_amount DECIMAL(10, 2) DEFAULT 0.00,
            refund_date DATETIME,
            refund_reason TEXT,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            INDEX idx_order_id (order_id),
            INDEX idx_payment_status (payment_status),
            INDEX idx_transaction_id (transaction_id),
            INDEX idx_payment_date (payment_date)
        ) ENGINE=InnoDB";
        
        $db->query($sql)->execute();
    }
    
    /**
     * Drop order_payments table
     */
    public function down()
    {
        $db = Database::getInstance();
        $sql = "DROP TABLE IF EXISTS order_payments";
        $db->query($sql)->execute();
    }
}
