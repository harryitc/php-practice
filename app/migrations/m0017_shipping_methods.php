<?php

/**
 * Shipping Methods Migration
 */
class M0017ShippingMethods
{
    /**
     * Create shipping_methods table
     */
    public function up()
    {
        $db = Database::getInstance();
        
        // Create shipping_methods table
        $sql = "CREATE TABLE IF NOT EXISTS shipping_methods (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            carrier VARCHAR(50) NOT NULL,
            method_code VARCHAR(50) NOT NULL UNIQUE,
            base_cost DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            cost_per_kg DECIMAL(10, 2) DEFAULT 0.00,
            free_shipping_threshold DECIMAL(10, 2),
            estimated_days_min INT DEFAULT 1,
            estimated_days_max INT DEFAULT 7,
            is_active BOOLEAN DEFAULT TRUE,
            sort_order INT DEFAULT 0,
            available_regions TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_method_code (method_code),
            INDEX idx_is_active (is_active),
            INDEX idx_sort_order (sort_order)
        ) ENGINE=InnoDB";
        
        $db->query($sql)->execute();
        
        // Insert sample shipping methods
        $this->insertSampleShippingMethods($db);
    }
    
    /**
     * Drop shipping_methods table
     */
    public function down()
    {
        $db = Database::getInstance();
        $sql = "DROP TABLE IF EXISTS shipping_methods";
        $db->query($sql)->execute();
    }
    
    /**
     * Insert sample shipping methods
     */
    private function insertSampleShippingMethods($db)
    {
        $shippingMethods = [
            [
                'name' => 'Giao Hàng Nhanh - Tiêu chuẩn',
                'description' => 'Giao hàng trong 2-3 ngày làm việc',
                'carrier' => 'Giao Hàng Nhanh',
                'method_code' => 'GHN_STANDARD',
                'base_cost' => 25000,
                'cost_per_kg' => 5000,
                'free_shipping_threshold' => 500000,
                'estimated_days_min' => 2,
                'estimated_days_max' => 3,
                'sort_order' => 1,
                'available_regions' => 'Toàn quốc'
            ],
            [
                'name' => 'Giao Hàng Nhanh - Hỏa tốc',
                'description' => 'Giao hàng trong ngày hoặc 24h',
                'carrier' => 'Giao Hàng Nhanh',
                'method_code' => 'GHN_EXPRESS',
                'base_cost' => 45000,
                'cost_per_kg' => 8000,
                'free_shipping_threshold' => 1000000,
                'estimated_days_min' => 1,
                'estimated_days_max' => 1,
                'sort_order' => 2,
                'available_regions' => 'TP.HCM, Hà Nội, Đà Nẵng'
            ],
            [
                'name' => 'Viettel Post - Tiêu chuẩn',
                'description' => 'Giao hàng trong 3-5 ngày làm việc',
                'carrier' => 'Viettel Post',
                'method_code' => 'VTP_STANDARD',
                'base_cost' => 20000,
                'cost_per_kg' => 4000,
                'free_shipping_threshold' => 300000,
                'estimated_days_min' => 3,
                'estimated_days_max' => 5,
                'sort_order' => 3,
                'available_regions' => 'Toàn quốc'
            ],
            [
                'name' => 'Viettel Post - Nhanh',
                'description' => 'Giao hàng trong 1-2 ngày làm việc',
                'carrier' => 'Viettel Post',
                'method_code' => 'VTP_FAST',
                'base_cost' => 35000,
                'cost_per_kg' => 6000,
                'free_shipping_threshold' => 800000,
                'estimated_days_min' => 1,
                'estimated_days_max' => 2,
                'sort_order' => 4,
                'available_regions' => 'Các tỉnh thành lớn'
            ],
            [
                'name' => 'Grab Express',
                'description' => 'Giao hàng trong 2-4 giờ (nội thành)',
                'carrier' => 'Grab',
                'method_code' => 'GRAB_EXPRESS',
                'base_cost' => 30000,
                'cost_per_kg' => 10000,
                'free_shipping_threshold' => null,
                'estimated_days_min' => 1,
                'estimated_days_max' => 1,
                'sort_order' => 5,
                'available_regions' => 'TP.HCM, Hà Nội nội thành'
            ],
            [
                'name' => 'Nhận tại cửa hàng',
                'description' => 'Khách hàng đến nhận trực tiếp tại cửa hàng',
                'carrier' => 'Tự nhận',
                'method_code' => 'PICKUP',
                'base_cost' => 0,
                'cost_per_kg' => 0,
                'free_shipping_threshold' => 0,
                'estimated_days_min' => 1,
                'estimated_days_max' => 1,
                'sort_order' => 6,
                'available_regions' => 'TP.HCM, Hà Nội'
            ]
        ];
        
        $sql = "INSERT INTO shipping_methods (name, description, carrier, method_code, base_cost, cost_per_kg, free_shipping_threshold, estimated_days_min, estimated_days_max, sort_order, available_regions) 
                VALUES (:name, :description, :carrier, :method_code, :base_cost, :cost_per_kg, :free_shipping_threshold, :estimated_days_min, :estimated_days_max, :sort_order, :available_regions)";
        
        foreach ($shippingMethods as $method) {
            $db->query($sql)->bind([
                'name' => $method['name'],
                'description' => $method['description'],
                'carrier' => $method['carrier'],
                'method_code' => $method['method_code'],
                'base_cost' => $method['base_cost'],
                'cost_per_kg' => $method['cost_per_kg'],
                'free_shipping_threshold' => $method['free_shipping_threshold'],
                'estimated_days_min' => $method['estimated_days_min'],
                'estimated_days_max' => $method['estimated_days_max'],
                'sort_order' => $method['sort_order'],
                'available_regions' => $method['available_regions']
            ])->execute();
        }
    }
}
