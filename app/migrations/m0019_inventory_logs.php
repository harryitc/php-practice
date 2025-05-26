<?php

/**
 * Inventory Logs Migration
 */
class M0019InventoryLogs
{
    /**
     * Create inventory_logs table
     */
    public function up()
    {
        $db = Database::getInstance();
        
        // Create inventory_logs table
        $sql = "CREATE TABLE IF NOT EXISTS inventory_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            type ENUM('in', 'out', 'adjustment', 'damaged', 'returned') NOT NULL,
            quantity INT NOT NULL,
            previous_quantity INT NOT NULL,
            new_quantity INT NOT NULL,
            reason VARCHAR(255),
            reference_type ENUM('order', 'purchase', 'adjustment', 'return', 'damage') NOT NULL,
            reference_id INT,
            user_id INT,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_product_id (product_id),
            INDEX idx_type (type),
            INDEX idx_reference (reference_type, reference_id),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB";
        
        $db->query($sql)->execute();
        
        // Insert sample inventory logs
        $this->insertSampleInventoryLogs($db);
    }
    
    /**
     * Drop inventory_logs table
     */
    public function down()
    {
        $db = Database::getInstance();
        $sql = "DROP TABLE IF EXISTS inventory_logs";
        $db->query($sql)->execute();
    }
    
    /**
     * Insert sample inventory logs
     */
    private function insertSampleInventoryLogs($db)
    {
        // Get some products for sample logs
        $products = $db->query("SELECT id, inventory_count FROM products LIMIT 5")->fetchAll();
        $adminUser = $db->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1")->fetch();
        
        if (empty($products) || !$adminUser) {
            return; // No products or admin user to create logs for
        }
        
        $inventoryLogs = [
            [
                'product_id' => $products[0]['id'],
                'type' => 'in',
                'quantity' => 100,
                'previous_quantity' => 0,
                'new_quantity' => 100,
                'reason' => 'Nhập hàng đầu tiên',
                'reference_type' => 'purchase',
                'reference_id' => 1,
                'user_id' => $adminUser['id'],
                'notes' => 'Nhập kho lần đầu cho sản phẩm mới'
            ],
            [
                'product_id' => $products[0]['id'],
                'type' => 'out',
                'quantity' => 5,
                'previous_quantity' => 100,
                'new_quantity' => 95,
                'reason' => 'Bán hàng',
                'reference_type' => 'order',
                'reference_id' => 1,
                'user_id' => null,
                'notes' => 'Xuất kho do bán hàng'
            ],
            [
                'product_id' => $products[1]['id'],
                'type' => 'in',
                'quantity' => 50,
                'previous_quantity' => 20,
                'new_quantity' => 70,
                'reason' => 'Nhập bổ sung',
                'reference_type' => 'purchase',
                'reference_id' => 2,
                'user_id' => $adminUser['id'],
                'notes' => 'Nhập thêm hàng do sắp hết'
            ],
            [
                'product_id' => $products[2]['id'],
                'type' => 'adjustment',
                'quantity' => -3,
                'previous_quantity' => 45,
                'new_quantity' => 42,
                'reason' => 'Kiểm kê phát hiện thiếu',
                'reference_type' => 'adjustment',
                'reference_id' => 1,
                'user_id' => $adminUser['id'],
                'notes' => 'Điều chỉnh sau kiểm kê định kỳ'
            ],
            [
                'product_id' => $products[3]['id'],
                'type' => 'damaged',
                'quantity' => 2,
                'previous_quantity' => 30,
                'new_quantity' => 28,
                'reason' => 'Hàng bị hỏng trong quá trình vận chuyển',
                'reference_type' => 'damage',
                'reference_id' => 1,
                'user_id' => $adminUser['id'],
                'notes' => 'Sản phẩm bị vỡ khi giao hàng, không thể bán được'
            ],
            [
                'product_id' => $products[4]['id'],
                'type' => 'returned',
                'quantity' => 1,
                'previous_quantity' => 25,
                'new_quantity' => 26,
                'reason' => 'Khách hàng trả lại',
                'reference_type' => 'return',
                'reference_id' => 1,
                'user_id' => null,
                'notes' => 'Khách hàng trả hàng do không ưng ý, sản phẩm còn nguyên vẹn'
            ]
        ];
        
        $sql = "INSERT INTO inventory_logs (product_id, type, quantity, previous_quantity, new_quantity, reason, reference_type, reference_id, user_id, notes) 
                VALUES (:product_id, :type, :quantity, :previous_quantity, :new_quantity, :reason, :reference_type, :reference_id, :user_id, :notes)";
        
        foreach ($inventoryLogs as $log) {
            $db->query($sql)->bind([
                'product_id' => $log['product_id'],
                'type' => $log['type'],
                'quantity' => $log['quantity'],
                'previous_quantity' => $log['previous_quantity'],
                'new_quantity' => $log['new_quantity'],
                'reason' => $log['reason'],
                'reference_type' => $log['reference_type'],
                'reference_id' => $log['reference_id'],
                'user_id' => $log['user_id'],
                'notes' => $log['notes']
            ])->execute();
        }
    }
}
