<?php

/**
 * Coupons Migration
 */
class M0013Coupons
{
    /**
     * Create coupons table
     */
    public function up()
    {
        $db = Database::getInstance();
        
        // Create coupons table
        $sql = "CREATE TABLE IF NOT EXISTS coupons (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(50) NOT NULL UNIQUE,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            type ENUM('percentage', 'fixed_amount', 'free_shipping') NOT NULL DEFAULT 'percentage',
            value DECIMAL(10, 2) NOT NULL,
            minimum_amount DECIMAL(10, 2) DEFAULT 0,
            maximum_discount DECIMAL(10, 2),
            usage_limit INT DEFAULT NULL,
            used_count INT DEFAULT 0,
            user_limit INT DEFAULT 1,
            is_active BOOLEAN DEFAULT TRUE,
            valid_from DATETIME NOT NULL,
            valid_until DATETIME NOT NULL,
            applicable_categories TEXT,
            applicable_products TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_code (code),
            INDEX idx_active (is_active),
            INDEX idx_valid_dates (valid_from, valid_until)
        ) ENGINE=InnoDB";
        
        $db->query($sql)->execute();
        
        // Create coupon_usage table
        $sql = "CREATE TABLE IF NOT EXISTS coupon_usage (
            id INT AUTO_INCREMENT PRIMARY KEY,
            coupon_id INT NOT NULL,
            user_id INT NOT NULL,
            order_id INT,
            discount_amount DECIMAL(10, 2) NOT NULL,
            used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
            INDEX idx_coupon_id (coupon_id),
            INDEX idx_user_id (user_id),
            INDEX idx_order_id (order_id)
        ) ENGINE=InnoDB";
        
        $db->query($sql)->execute();
        
        // Insert sample coupons
        $this->insertSampleCoupons($db);
    }
    
    /**
     * Drop coupons tables
     */
    public function down()
    {
        $db = Database::getInstance();
        $db->query("DROP TABLE IF EXISTS coupon_usage")->execute();
        $db->query("DROP TABLE IF EXISTS coupons")->execute();
    }
    
    /**
     * Insert sample coupons
     */
    private function insertSampleCoupons($db)
    {
        $coupons = [
            [
                'code' => 'WELCOME10',
                'name' => 'Chào mừng khách hàng mới',
                'description' => 'Giảm 10% cho đơn hàng đầu tiên',
                'type' => 'percentage',
                'value' => 10.00,
                'minimum_amount' => 100000,
                'maximum_discount' => 50000,
                'usage_limit' => 1000,
                'user_limit' => 1,
                'valid_from' => date('Y-m-d H:i:s'),
                'valid_until' => date('Y-m-d H:i:s', strtotime('+1 year'))
            ],
            [
                'code' => 'FREESHIP',
                'name' => 'Miễn phí vận chuyển',
                'description' => 'Miễn phí vận chuyển cho đơn hàng trên 500k',
                'type' => 'free_shipping',
                'value' => 0.00,
                'minimum_amount' => 500000,
                'usage_limit' => null,
                'user_limit' => 5,
                'valid_from' => date('Y-m-d H:i:s'),
                'valid_until' => date('Y-m-d H:i:s', strtotime('+6 months'))
            ],
            [
                'code' => 'SAVE50K',
                'name' => 'Giảm 50k',
                'description' => 'Giảm 50,000 VNĐ cho đơn hàng trên 1 triệu',
                'type' => 'fixed_amount',
                'value' => 50000,
                'minimum_amount' => 1000000,
                'usage_limit' => 500,
                'user_limit' => 1,
                'valid_from' => date('Y-m-d H:i:s'),
                'valid_until' => date('Y-m-d H:i:s', strtotime('+3 months'))
            ]
        ];
        
        $sql = "INSERT INTO coupons (code, name, description, type, value, minimum_amount, maximum_discount, usage_limit, user_limit, valid_from, valid_until) 
                VALUES (:code, :name, :description, :type, :value, :minimum_amount, :maximum_discount, :usage_limit, :user_limit, :valid_from, :valid_until)";
        
        foreach ($coupons as $coupon) {
            $db->query($sql)->bind([
                'code' => $coupon['code'],
                'name' => $coupon['name'],
                'description' => $coupon['description'],
                'type' => $coupon['type'],
                'value' => $coupon['value'],
                'minimum_amount' => $coupon['minimum_amount'],
                'maximum_discount' => $coupon['maximum_discount'] ?? null,
                'usage_limit' => $coupon['usage_limit'],
                'user_limit' => $coupon['user_limit'],
                'valid_from' => $coupon['valid_from'],
                'valid_until' => $coupon['valid_until']
            ])->execute();
        }
    }
}
