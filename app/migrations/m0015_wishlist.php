<?php

/**
 * Wishlist Migration
 */
class M0015Wishlist
{
    /**
     * Create wishlist table
     */
    public function up()
    {
        $db = Database::getInstance();
        
        // Create wishlist table
        $sql = "CREATE TABLE IF NOT EXISTS wishlist (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_product_id (product_id),
            INDEX idx_added_at (added_at),
            UNIQUE KEY unique_user_product (user_id, product_id)
        ) ENGINE=InnoDB";
        
        $db->query($sql)->execute();
        
        // Insert sample wishlist items
        $this->insertSampleWishlistItems($db);
    }
    
    /**
     * Drop wishlist table
     */
    public function down()
    {
        $db = Database::getInstance();
        $sql = "DROP TABLE IF EXISTS wishlist";
        $db->query($sql)->execute();
    }
    
    /**
     * Insert sample wishlist items
     */
    private function insertSampleWishlistItems($db)
    {
        // Get some products and users for sample wishlist
        $products = $db->query("SELECT id FROM products LIMIT 5")->fetchAll();
        $users = $db->query("SELECT id FROM users WHERE role = 'customer' LIMIT 3")->fetchAll();
        
        if (empty($products) || empty($users)) {
            return; // No products or users to create wishlist for
        }
        
        $wishlistItems = [
            [
                'user_id' => $users[0]['id'],
                'product_id' => $products[0]['id']
            ],
            [
                'user_id' => $users[0]['id'],
                'product_id' => $products[1]['id']
            ],
            [
                'user_id' => $users[1]['id'],
                'product_id' => $products[2]['id']
            ],
            [
                'user_id' => $users[1]['id'],
                'product_id' => $products[3]['id']
            ],
            [
                'user_id' => $users[2]['id'],
                'product_id' => $products[4]['id']
            ]
        ];
        
        $sql = "INSERT INTO wishlist (user_id, product_id) VALUES (:user_id, :product_id)";
        
        foreach ($wishlistItems as $item) {
            try {
                $db->query($sql)->bind([
                    'user_id' => $item['user_id'],
                    'product_id' => $item['product_id']
                ])->execute();
            } catch (Exception $e) {
                // Skip if duplicate entry
                continue;
            }
        }
    }
}
