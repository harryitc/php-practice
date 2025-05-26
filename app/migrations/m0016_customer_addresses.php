<?php

/**
 * Customer Addresses Migration
 */
class M0016CustomerAddresses
{
    /**
     * Create customer_addresses table
     */
    public function up()
    {
        $db = Database::getInstance();
        
        // Create customer_addresses table
        $sql = "CREATE TABLE IF NOT EXISTS customer_addresses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            type ENUM('billing', 'shipping', 'both') NOT NULL DEFAULT 'both',
            is_default BOOLEAN DEFAULT FALSE,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            company VARCHAR(100),
            address_line_1 VARCHAR(255) NOT NULL,
            address_line_2 VARCHAR(255),
            city VARCHAR(100) NOT NULL,
            state VARCHAR(100) NOT NULL,
            postal_code VARCHAR(20) NOT NULL,
            country VARCHAR(100) NOT NULL DEFAULT 'Việt Nam',
            phone VARCHAR(20),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_type (type),
            INDEX idx_is_default (is_default)
        ) ENGINE=InnoDB";
        
        $db->query($sql)->execute();
        
        // Insert sample addresses
        $this->insertSampleAddresses($db);
    }
    
    /**
     * Drop customer_addresses table
     */
    public function down()
    {
        $db = Database::getInstance();
        $sql = "DROP TABLE IF EXISTS customer_addresses";
        $db->query($sql)->execute();
    }
    
    /**
     * Insert sample addresses
     */
    private function insertSampleAddresses($db)
    {
        // Get customer users for sample addresses
        $users = $db->query("SELECT id, name FROM users WHERE role = 'customer' LIMIT 5")->fetchAll();
        
        if (empty($users)) {
            return; // No users to create addresses for
        }
        
        $addresses = [
            [
                'user_id' => $users[0]['id'],
                'type' => 'both',
                'is_default' => true,
                'first_name' => 'Nguyễn Thị',
                'last_name' => 'Hoa',
                'address_line_1' => '123 Đường Nguyễn Huệ',
                'address_line_2' => 'Phường Bến Nghé',
                'city' => 'Hồ Chí Minh',
                'state' => 'Hồ Chí Minh',
                'postal_code' => '70000',
                'phone' => '0901234567'
            ],
            [
                'user_id' => $users[1]['id'],
                'type' => 'shipping',
                'is_default' => true,
                'first_name' => 'Trần Văn',
                'last_name' => 'Nam',
                'address_line_1' => '456 Phố Hàng Bài',
                'address_line_2' => 'Phường Hàng Bài',
                'city' => 'Hà Nội',
                'state' => 'Hà Nội',
                'postal_code' => '10000',
                'phone' => '0912345678'
            ],
            [
                'user_id' => $users[2]['id'],
                'type' => 'both',
                'is_default' => true,
                'first_name' => 'Lê Thị',
                'last_name' => 'Mai',
                'address_line_1' => '789 Đường Trần Phú',
                'address_line_2' => 'Phường Hải Châu',
                'city' => 'Đà Nẵng',
                'state' => 'Đà Nẵng',
                'postal_code' => '50000',
                'phone' => '0923456789'
            ]
        ];
        
        if (count($users) >= 4) {
            $addresses[] = [
                'user_id' => $users[3]['id'],
                'type' => 'billing',
                'is_default' => true,
                'first_name' => 'Phạm Minh',
                'last_name' => 'Tuấn',
                'address_line_1' => '321 Đường Lê Lợi',
                'address_line_2' => 'Phường 4',
                'city' => 'Cần Thơ',
                'state' => 'Cần Thơ',
                'postal_code' => '90000',
                'phone' => '0934567890'
            ];
        }
        
        if (count($users) >= 5) {
            $addresses[] = [
                'user_id' => $users[4]['id'],
                'type' => 'shipping',
                'is_default' => true,
                'first_name' => 'Hoàng Thị',
                'last_name' => 'Lan',
                'address_line_1' => '654 Đường Nguyễn Văn Linh',
                'address_line_2' => 'Phường An Phú',
                'city' => 'Hải Phòng',
                'state' => 'Hải Phòng',
                'postal_code' => '18000',
                'phone' => '0945678901'
            ];
        }
        
        $sql = "INSERT INTO customer_addresses (user_id, type, is_default, first_name, last_name, address_line_1, address_line_2, city, state, postal_code, phone) 
                VALUES (:user_id, :type, :is_default, :first_name, :last_name, :address_line_1, :address_line_2, :city, :state, :postal_code, :phone)";
        
        foreach ($addresses as $address) {
            $db->query($sql)->bind([
                'user_id' => $address['user_id'],
                'type' => $address['type'],
                'is_default' => $address['is_default'],
                'first_name' => $address['first_name'],
                'last_name' => $address['last_name'],
                'address_line_1' => $address['address_line_1'],
                'address_line_2' => $address['address_line_2'],
                'city' => $address['city'],
                'state' => $address['state'],
                'postal_code' => $address['postal_code'],
                'phone' => $address['phone']
            ])->execute();
        }
    }
}
