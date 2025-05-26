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
            order_number VARCHAR(20) UNIQUE NOT NULL,
            user_id INT,
            total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            tax_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            shipping_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            discount_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            status ENUM('pending', 'confirmed', 'processing', 'packed', 'shipped', 'out_for_delivery', 'delivered', 'cancelled', 'returned', 'refunded') NOT NULL DEFAULT 'pending',
            payment_status ENUM('pending', 'paid', 'failed', 'refunded', 'partially_refunded') NOT NULL DEFAULT 'pending',
            shipping_address TEXT NOT NULL,
            shipping_city VARCHAR(100) NOT NULL,
            shipping_state VARCHAR(100) NOT NULL,
            shipping_zip VARCHAR(20) NOT NULL,
            shipping_country VARCHAR(100) NOT NULL,
            billing_address TEXT,
            billing_city VARCHAR(100),
            billing_state VARCHAR(100),
            billing_zip VARCHAR(20),
            billing_country VARCHAR(100),
            payment_method VARCHAR(50) NOT NULL,
            tracking_number VARCHAR(100),
            carrier VARCHAR(50),
            estimated_delivery_date DATE,
            actual_delivery_date DATETIME,
            priority ENUM('low', 'normal', 'high', 'urgent') NOT NULL DEFAULT 'normal',
            source VARCHAR(50) DEFAULT 'website',
            notes TEXT,
            internal_notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_order_number (order_number),
            INDEX idx_status (status),
            INDEX idx_payment_status (payment_status),
            INDEX idx_tracking_number (tracking_number),
            INDEX idx_created_at (created_at)
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
