<?php

/**
 * Products Migration
 */
class M0003Products
{
    /**
     * Create products table
     */
    public function up()
    {
        $db = Database::getInstance();
        
        $sql = "CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            category_id INT,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            price DECIMAL(10, 2) NOT NULL,
            status ENUM('Scoping', 'Quoting', 'Production', 'Shipped') NOT NULL DEFAULT 'Scoping',
            inventory_count INT NOT NULL DEFAULT 0,
            incoming_count INT NOT NULL DEFAULT 0,
            out_of_stock INT NOT NULL DEFAULT 0,
            grade ENUM('A', 'B', 'C') NOT NULL DEFAULT 'A',
            image VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        ) ENGINE=InnoDB";
        
        $db->query($sql)->execute();
        
        // Insert sample products
        $products = [
            [
                'category_id' => 4, // Body Care
                'name' => 'Hydrate replenish(body oil)',
                'description' => 'Hydrating body oil for dry skin',
                'price' => 29.99,
                'status' => 'Scoping',
                'inventory_count' => 45,
                'incoming_count' => 12,
                'out_of_stock' => 11,
                'grade' => 'A',
                'image' => 'https://via.placeholder.com/40'
            ],
            [
                'category_id' => 1, // Skin Care
                'name' => 'Hydrate replenish',
                'description' => 'Hydrating face cream',
                'price' => 24.99,
                'status' => 'Scoping',
                'inventory_count' => 45,
                'incoming_count' => 65,
                'out_of_stock' => 11,
                'grade' => 'A',
                'image' => 'https://via.placeholder.com/40'
            ],
            [
                'category_id' => 1, // Skin Care
                'name' => 'Illumination (mask)',
                'description' => 'Brightening face mask',
                'price' => 19.99,
                'status' => 'Quoting',
                'inventory_count' => 45,
                'incoming_count' => 35,
                'out_of_stock' => 11,
                'grade' => 'B',
                'image' => 'https://via.placeholder.com/40'
            ],
            [
                'category_id' => 2, // Hair Care
                'name' => 'Act+ acre hair mask',
                'description' => 'Nourishing hair mask',
                'price' => 34.99,
                'status' => 'Scoping',
                'inventory_count' => 45,
                'incoming_count' => 24,
                'out_of_stock' => 11,
                'grade' => 'A',
                'image' => 'https://via.placeholder.com/40'
            ],
            [
                'category_id' => 3, // Makeup
                'name' => 'Mecca cosmetica',
                'description' => 'Luxury cosmetics set',
                'price' => 89.99,
                'status' => 'Production',
                'inventory_count' => 0,
                'incoming_count' => 22,
                'out_of_stock' => 11,
                'grade' => 'A',
                'image' => 'https://via.placeholder.com/40'
            ]
        ];
        
        $sql = "INSERT INTO products (category_id, name, description, price, status, inventory_count, incoming_count, out_of_stock, grade, image) 
                VALUES (:category_id, :name, :description, :price, :status, :inventory_count, :incoming_count, :out_of_stock, :grade, :image)";
        
        foreach ($products as $product) {
            $db->query($sql)->bind([
                'category_id' => $product['category_id'],
                'name' => $product['name'],
                'description' => $product['description'],
                'price' => $product['price'],
                'status' => $product['status'],
                'inventory_count' => $product['inventory_count'],
                'incoming_count' => $product['incoming_count'],
                'out_of_stock' => $product['out_of_stock'],
                'grade' => $product['grade'],
                'image' => $product['image']
            ])->execute();
        }
    }
    
    /**
     * Drop products table
     */
    public function down()
    {
        $db = Database::getInstance();
        $sql = "DROP TABLE IF EXISTS products";
        $db->query($sql)->execute();
    }
}
