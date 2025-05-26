<?php

/**
 * Product Reviews Migration
 */
class M0014ProductReviews
{
    /**
     * Create product_reviews table
     */
    public function up()
    {
        $db = Database::getInstance();
        
        // Create product_reviews table
        $sql = "CREATE TABLE IF NOT EXISTS product_reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            user_id INT NOT NULL,
            order_id INT,
            rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
            title VARCHAR(255),
            review_text TEXT,
            is_verified_purchase BOOLEAN DEFAULT FALSE,
            is_approved BOOLEAN DEFAULT FALSE,
            helpful_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
            INDEX idx_product_id (product_id),
            INDEX idx_user_id (user_id),
            INDEX idx_rating (rating),
            INDEX idx_approved (is_approved),
            INDEX idx_created_at (created_at),
            UNIQUE KEY unique_user_product_review (user_id, product_id, order_id)
        ) ENGINE=InnoDB";
        
        $db->query($sql)->execute();
        
        // Create review_images table
        $sql = "CREATE TABLE IF NOT EXISTS review_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            review_id INT NOT NULL,
            image_path VARCHAR(500) NOT NULL,
            image_alt VARCHAR(255),
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (review_id) REFERENCES product_reviews(id) ON DELETE CASCADE,
            INDEX idx_review_id (review_id),
            INDEX idx_sort_order (sort_order)
        ) ENGINE=InnoDB";
        
        $db->query($sql)->execute();
        
        // Create review_helpful table
        $sql = "CREATE TABLE IF NOT EXISTS review_helpful (
            id INT AUTO_INCREMENT PRIMARY KEY,
            review_id INT NOT NULL,
            user_id INT NOT NULL,
            is_helpful BOOLEAN NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (review_id) REFERENCES product_reviews(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_review_id (review_id),
            INDEX idx_user_id (user_id),
            UNIQUE KEY unique_user_review_helpful (user_id, review_id)
        ) ENGINE=InnoDB";
        
        $db->query($sql)->execute();
        
        // Insert sample reviews
        $this->insertSampleReviews($db);
    }
    
    /**
     * Drop product_reviews tables
     */
    public function down()
    {
        $db = Database::getInstance();
        $db->query("DROP TABLE IF EXISTS review_helpful")->execute();
        $db->query("DROP TABLE IF EXISTS review_images")->execute();
        $db->query("DROP TABLE IF EXISTS product_reviews")->execute();
    }
    
    /**
     * Insert sample reviews
     */
    private function insertSampleReviews($db)
    {
        // Get some products and users for sample reviews
        $products = $db->query("SELECT id FROM products LIMIT 3")->fetchAll();
        $users = $db->query("SELECT id FROM users WHERE role = 'customer' LIMIT 3")->fetchAll();
        
        if (empty($products) || empty($users)) {
            return; // No products or users to create reviews for
        }
        
        $reviews = [
            [
                'product_id' => $products[0]['id'],
                'user_id' => $users[0]['id'],
                'rating' => 5,
                'title' => 'Sản phẩm tuyệt vời!',
                'review_text' => 'Tôi rất hài lòng với sản phẩm này. Chất lượng tốt, đóng gói cẩn thận và giao hàng nhanh. Sẽ mua lại lần sau.',
                'is_verified_purchase' => true,
                'is_approved' => true,
                'helpful_count' => 5
            ],
            [
                'product_id' => $products[1]['id'],
                'user_id' => $users[1]['id'],
                'rating' => 4,
                'title' => 'Chất lượng ổn',
                'review_text' => 'Sản phẩm khá tốt, phù hợp với giá tiền. Có một vài điểm nhỏ cần cải thiện nhưng nhìn chung vẫn hài lòng.',
                'is_verified_purchase' => true,
                'is_approved' => true,
                'helpful_count' => 3
            ],
            [
                'product_id' => $products[2]['id'],
                'user_id' => $users[2]['id'],
                'rating' => 5,
                'title' => 'Đáng đồng tiền bát gạo',
                'review_text' => 'Mình đã sử dụng sản phẩm này được 2 tuần và thấy hiệu quả rõ rệt. Rất recommend cho mọi người.',
                'is_verified_purchase' => false,
                'is_approved' => true,
                'helpful_count' => 8
            ]
        ];
        
        $sql = "INSERT INTO product_reviews (product_id, user_id, rating, title, review_text, is_verified_purchase, is_approved, helpful_count) 
                VALUES (:product_id, :user_id, :rating, :title, :review_text, :is_verified_purchase, :is_approved, :helpful_count)";
        
        foreach ($reviews as $review) {
            $db->query($sql)->bind([
                'product_id' => $review['product_id'],
                'user_id' => $review['user_id'],
                'rating' => $review['rating'],
                'title' => $review['title'],
                'review_text' => $review['review_text'],
                'is_verified_purchase' => $review['is_verified_purchase'],
                'is_approved' => $review['is_approved'],
                'helpful_count' => $review['helpful_count']
            ])->execute();
        }
    }
}
