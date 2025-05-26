<?php

/**
 * Sample Data Migration - Vietnamese
 * Thêm dữ liệu mẫu tiếng Việt với hình ảnh từ Unsplash
 */
class M0011SampleDataVietnamese
{
    /**
     * Thêm dữ liệu mẫu
     */
    public function up()
    {
        $db = Database::getInstance();

        // Xóa dữ liệu cũ trước khi thêm dữ liệu mới
        $this->clearExistingData($db);

        // Thêm categories tiếng Việt
        $this->insertCategories($db);

        // Thêm users mẫu
        $this->insertUsers($db);

        // Thêm products tiếng Việt với hình ảnh Unsplash
        $this->insertProducts($db);

        // Thêm orders mẫu
        $this->insertOrders($db);
    }

    /**
     * Xóa dữ liệu mẫu
     */
    public function down()
    {
        $db = Database::getInstance();

        // Xóa theo thứ tự ngược lại để tránh lỗi foreign key
        $db->query("DELETE FROM order_items WHERE order_id IN (SELECT id FROM orders WHERE order_number LIKE 'VN%')")->execute();
        $db->query("DELETE FROM orders WHERE order_number LIKE 'VN%'")->execute();
        $db->query("DELETE FROM products WHERE name LIKE '%Việt%' OR description LIKE '%Việt%'")->execute();
        $db->query("DELETE FROM users WHERE email LIKE '%vietnam%' OR name LIKE '%Việt%'")->execute();
        $db->query("DELETE FROM categories WHERE name IN ('Chăm sóc da', 'Chăm sóc tóc', 'Trang điểm', 'Chăm sóc cơ thể', 'Nước hoa', 'Thực phẩm chức năng')")->execute();
    }

    /**
     * Xóa dữ liệu cũ
     */
    private function clearExistingData($db)
    {
        // Xóa dữ liệu cũ (chỉ xóa dữ liệu mẫu, không xóa admin)
        $db->query("DELETE FROM order_items")->execute();
        $db->query("DELETE FROM orders")->execute();
        $db->query("DELETE FROM products")->execute();
        $db->query("DELETE FROM users WHERE role = 'customer'")->execute();
        $db->query("DELETE FROM categories")->execute();
    }

    /**
     * Thêm categories tiếng Việt
     */
    private function insertCategories($db)
    {
        $categories = [
            [
                'name' => 'Chăm sóc da',
                'description' => 'Các sản phẩm chăm sóc và điều trị da mặt, giúp da khỏe mạnh và rạng rỡ'
            ],
            [
                'name' => 'Chăm sóc tóc',
                'description' => 'Sản phẩm chăm sóc tóc từ dầu gội, dầu xả đến mặt nạ dưỡng tóc'
            ],
            [
                'name' => 'Trang điểm',
                'description' => 'Mỹ phẩm trang điểm cao cấp từ các thương hiệu nổi tiếng'
            ],
            [
                'name' => 'Chăm sóc cơ thể',
                'description' => 'Sản phẩm chăm sóc toàn thân từ sữa tắm đến kem dưỡng da'
            ],
            [
                'name' => 'Nước hoa',
                'description' => 'Nước hoa cao cấp với nhiều mùi hương quyến rũ và sang trọng'
            ],
            [
                'name' => 'Thực phẩm chức năng',
                'description' => 'Vitamin và thực phẩm bổ sung cho sức khỏe và sắc đẹp'
            ]
        ];

        $sql = "INSERT INTO categories (name, description) VALUES (:name, :description)";

        foreach ($categories as $category) {
            $db->query($sql)->bind([
                'name' => $category['name'],
                'description' => $category['description']
            ])->execute();
        }
    }

    /**
     * Thêm users mẫu
     */
    private function insertUsers($db)
    {
        $users = [
            [
                'name' => 'Nguyễn Thị Hoa',
                'email' => 'hoa.nguyen@vietnam.com',
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'role' => 'customer'
            ],
            [
                'name' => 'Trần Văn Nam',
                'email' => 'nam.tran@vietnam.com',
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'role' => 'customer'
            ],
            [
                'name' => 'Lê Thị Mai',
                'email' => 'mai.le@vietnam.com',
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'role' => 'customer'
            ],
            [
                'name' => 'Phạm Minh Tuấn',
                'email' => 'tuan.pham@vietnam.com',
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'role' => 'customer'
            ],
            [
                'name' => 'Hoàng Thị Lan',
                'email' => 'lan.hoang@vietnam.com',
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'role' => 'customer'
            ]
        ];

        $sql = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)";

        foreach ($users as $user) {
            $db->query($sql)->bind([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => $user['password'],
                'role' => $user['role']
            ])->execute();
        }
    }

    /**
     * Thêm products tiếng Việt với hình ảnh Unsplash
     */
    private function insertProducts($db)
    {
        // Lấy category IDs thực tế từ database
        $categoryIds = [];
        $categories = $db->query("SELECT id, name FROM categories ORDER BY id")->fetchAll();
        foreach ($categories as $category) {
            $categoryIds[$category['name']] = $category['id'];
        }

        // Nếu không có categories, thoát
        if (empty($categoryIds)) {
            return;
        }

        $products = [
            // Chăm sóc da
            [
                'category_id' => $categoryIds['Chăm sóc da'] ?? 1,
                'name' => 'Kem dưỡng ẩm Vitamin C',
                'description' => 'Kem dưỡng ẩm chứa Vitamin C giúp làm sáng da và chống lão hóa. Phù hợp cho mọi loại da, đặc biệt da khô và da thiếu sức sống.',
                'price' => 450000,
                'status' => 'Scoping',
                'inventory_count' => 50,
                'incoming_count' => 20,
                'out_of_stock' => 0,
                'grade' => 'A',
                'image' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=400&h=400&fit=crop'
            ],
            [
                'category_id' => $categoryIds['Chăm sóc da'] ?? 1,
                'name' => 'Serum Hyaluronic Acid',
                'description' => 'Serum cấp ẩm sâu với Hyaluronic Acid, giúp da mềm mại và căng mọng. Thích hợp cho da khô và da lão hóa.',
                'price' => 680000,
                'status' => 'Production',
                'inventory_count' => 35,
                'incoming_count' => 15,
                'out_of_stock' => 5,
                'grade' => 'A',
                'image' => 'https://images.unsplash.com/photo-1570194065650-d99fb4bedf0a?w=400&h=400&fit=crop'
            ],
            [
                'category_id' => $categoryIds['Chăm sóc da'] ?? 1,
                'name' => 'Mặt nạ collagen tươi',
                'description' => 'Mặt nạ giấy chứa collagen tươi giúp làm căng da và giảm nếp nhăn. Sử dụng 2-3 lần/tuần để có hiệu quả tốt nhất.',
                'price' => 25000,
                'status' => 'Shipped',
                'inventory_count' => 200,
                'incoming_count' => 100,
                'out_of_stock' => 0,
                'grade' => 'B',
                'image' => 'https://images.unsplash.com/photo-1596755389378-c31d21fd1273?w=400&h=400&fit=crop'
            ],

            // Chăm sóc tóc
            [
                'category_id' => $categoryIds['Chăm sóc tóc'] ?? 2,
                'name' => 'Dầu gội thảo dược Việt Nam',
                'description' => 'Dầu gội từ các loại thảo dược Việt Nam như bồ kết, trà xanh giúp tóc mềm mại và khỏe mạnh. Không chứa sulfate.',
                'price' => 180000,
                'status' => 'Scoping',
                'inventory_count' => 80,
                'incoming_count' => 30,
                'out_of_stock' => 10,
                'grade' => 'A',
                'image' => 'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?w=400&h=400&fit=crop'
            ],
            [
                'category_id' => $categoryIds['Chăm sóc tóc'] ?? 2,
                'name' => 'Mặt nạ tóc dầu dừa organic',
                'description' => 'Mặt nạ dưỡng tóc từ dầu dừa organic Việt Nam, giúp phục hồi tóc hư tổn và tăng độ bóng mượt cho tóc.',
                'price' => 320000,
                'status' => 'Quoting',
                'inventory_count' => 45,
                'incoming_count' => 25,
                'out_of_stock' => 8,
                'grade' => 'A',
                'image' => 'https://images.unsplash.com/photo-1522338242992-e1a54906a8da?w=400&h=400&fit=crop'
            ],

            // Trang điểm
            [
                'category_id' => $categoryIds['Trang điểm'] ?? 3,
                'name' => 'Son môi lì Việt Nam',
                'description' => 'Son môi lì cao cấp với công thức không khô môi, màu sắc rực rỡ và lâu trôi. Có 12 màu để lựa chọn.',
                'price' => 280000,
                'status' => 'Production',
                'inventory_count' => 120,
                'incoming_count' => 60,
                'out_of_stock' => 15,
                'grade' => 'A',
                'image' => 'https://images.unsplash.com/photo-1586495777744-4413f21062fa?w=400&h=400&fit=crop'
            ],
            [
                'category_id' => $categoryIds['Trang điểm'] ?? 3,
                'name' => 'Phấn nền BB cream tự nhiên',
                'description' => 'BB cream với độ che phủ vừa phải, tạo lớp nền tự nhiên và bảo vệ da khỏi tia UV. SPF 30 PA++.',
                'price' => 380000,
                'status' => 'Scoping',
                'inventory_count' => 65,
                'incoming_count' => 40,
                'out_of_stock' => 12,
                'grade' => 'A',
                'image' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=400&h=400&fit=crop'
            ],

            // Chăm sóc cơ thể
            [
                'category_id' => $categoryIds['Chăm sóc cơ thể'] ?? 4,
                'name' => 'Sữa tắm hương hoa sen',
                'description' => 'Sữa tắm với hương thơm nhẹ nhàng của hoa sen, giúp làm sạch và dưỡng ẩm cho da. Công thức không gây khô da.',
                'price' => 120000,
                'status' => 'Shipped',
                'inventory_count' => 150,
                'incoming_count' => 80,
                'out_of_stock' => 5,
                'grade' => 'B',
                'image' => 'https://images.unsplash.com/photo-1556228578-8c89e6adf883?w=400&h=400&fit=crop'
            ],
            [
                'category_id' => $categoryIds['Chăm sóc cơ thể'] ?? 4,
                'name' => 'Kem dưỡng thể tinh dầu dừa',
                'description' => 'Kem dưỡng thể từ tinh dầu dừa Việt Nam, giúp da mềm mại và có mùi hương tự nhiên. Thấm nhanh, không gây nhờn.',
                'price' => 250000,
                'status' => 'Production',
                'inventory_count' => 75,
                'incoming_count' => 35,
                'out_of_stock' => 8,
                'grade' => 'A',
                'image' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=400&fit=crop'
            ],

            // Nước hoa
            [
                'category_id' => $categoryIds['Nước hoa'] ?? 5,
                'name' => 'Nước hoa hương hoa mai',
                'description' => 'Nước hoa với hương thơm đặc trưng của hoa mai Việt Nam, mang lại cảm giác tươi mát và thanh lịch.',
                'price' => 890000,
                'status' => 'Scoping',
                'inventory_count' => 25,
                'incoming_count' => 15,
                'out_of_stock' => 3,
                'grade' => 'A',
                'image' => 'https://images.unsplash.com/photo-1541643600914-78b084683601?w=400&h=400&fit=crop'
            ],
            [
                'category_id' => $categoryIds['Nước hoa'] ?? 5,
                'name' => 'Nước hoa unisex hương trà xanh',
                'description' => 'Nước hoa unisex với hương trà xanh tươi mát, phù hợp cho cả nam và nữ. Lưu hương lâu và thanh lịch.',
                'price' => 750000,
                'status' => 'Quoting',
                'inventory_count' => 30,
                'incoming_count' => 20,
                'out_of_stock' => 2,
                'grade' => 'A',
                'image' => 'https://images.unsplash.com/photo-1588405748880-12d1d2a59d75?w=400&h=400&fit=crop'
            ],

            // Thực phẩm chức năng
            [
                'category_id' => $categoryIds['Thực phẩm chức năng'] ?? 6,
                'name' => 'Vitamin C + Collagen',
                'description' => 'Viên uống bổ sung Vitamin C và Collagen giúp làm đẹp da từ bên trong. Hộp 60 viên, uống 2 viên/ngày.',
                'price' => 420000,
                'status' => 'Production',
                'inventory_count' => 100,
                'incoming_count' => 50,
                'out_of_stock' => 10,
                'grade' => 'A',
                'image' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&h=400&fit=crop'
            ],
            [
                'category_id' => $categoryIds['Thực phẩm chức năng'] ?? 6,
                'name' => 'Tinh chất nghệ mật ong',
                'description' => 'Tinh chất nghệ kết hợp mật ong rừng, giúp tăng cường sức khỏe và làm đẹp da. Sản phẩm 100% tự nhiên.',
                'price' => 380000,
                'status' => 'Shipped',
                'inventory_count' => 60,
                'incoming_count' => 40,
                'out_of_stock' => 5,
                'grade' => 'A',
                'image' => 'https://images.unsplash.com/photo-1559181567-c3190ca9959b?w=400&h=400&fit=crop'
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
     * Thêm orders mẫu
     */
    private function insertOrders($db)
    {
        // Lấy user IDs
        $userIds = $db->query("SELECT id FROM users WHERE role = 'customer' ORDER BY id LIMIT 5")->fetchAll();

        if (empty($userIds)) {
            return; // Không có customer nào để tạo orders
        }

        $orders = [
            [
                'order_number' => 'VN' . date('Y') . '001',
                'user_id' => $userIds[0]['id'],
                'total_amount' => 730000,
                'tax_amount' => 73000,
                'shipping_amount' => 30000,
                'discount_amount' => 0,
                'status' => 'delivered',
                'payment_status' => 'paid',
                'shipping_address' => '123 Đường Nguyễn Huệ',
                'shipping_city' => 'Hồ Chí Minh',
                'shipping_state' => 'Hồ Chí Minh',
                'shipping_zip' => '70000',
                'shipping_country' => 'Việt Nam',
                'payment_method' => 'credit_card',
                'tracking_number' => 'VN2024001TRK',
                'carrier' => 'Giao Hàng Nhanh',
                'priority' => 'normal'
            ],
            [
                'order_number' => 'VN' . date('Y') . '002',
                'user_id' => $userIds[1]['id'],
                'total_amount' => 500000,
                'tax_amount' => 50000,
                'shipping_amount' => 25000,
                'discount_amount' => 50000,
                'status' => 'shipped',
                'payment_status' => 'paid',
                'shipping_address' => '456 Phố Hàng Bài',
                'shipping_city' => 'Hà Nội',
                'shipping_state' => 'Hà Nội',
                'shipping_zip' => '10000',
                'shipping_country' => 'Việt Nam',
                'payment_method' => 'bank_transfer',
                'tracking_number' => 'VN2024002TRK',
                'carrier' => 'Viettel Post',
                'priority' => 'high'
            ],
            [
                'order_number' => 'VN' . date('Y') . '003',
                'user_id' => $userIds[2]['id'],
                'total_amount' => 1200000,
                'tax_amount' => 120000,
                'shipping_amount' => 0,
                'discount_amount' => 100000,
                'status' => 'processing',
                'payment_status' => 'paid',
                'shipping_address' => '789 Đường Trần Phú',
                'shipping_city' => 'Đà Nẵng',
                'shipping_state' => 'Đà Nẵng',
                'shipping_zip' => '50000',
                'shipping_country' => 'Việt Nam',
                'payment_method' => 'momo',
                'tracking_number' => null,
                'carrier' => null,
                'priority' => 'normal'
            ]
        ];

        $sql = "INSERT INTO orders (order_number, user_id, total_amount, tax_amount, shipping_amount, discount_amount, status, payment_status, shipping_address, shipping_city, shipping_state, shipping_zip, shipping_country, payment_method, tracking_number, carrier, priority)
                VALUES (:order_number, :user_id, :total_amount, :tax_amount, :shipping_amount, :discount_amount, :status, :payment_status, :shipping_address, :shipping_city, :shipping_state, :shipping_zip, :shipping_country, :payment_method, :tracking_number, :carrier, :priority)";

        foreach ($orders as $order) {
            $db->query($sql)->bind([
                'order_number' => $order['order_number'],
                'user_id' => $order['user_id'],
                'total_amount' => $order['total_amount'],
                'tax_amount' => $order['tax_amount'],
                'shipping_amount' => $order['shipping_amount'],
                'discount_amount' => $order['discount_amount'],
                'status' => $order['status'],
                'payment_status' => $order['payment_status'],
                'shipping_address' => $order['shipping_address'],
                'shipping_city' => $order['shipping_city'],
                'shipping_state' => $order['shipping_state'],
                'shipping_zip' => $order['shipping_zip'],
                'shipping_country' => $order['shipping_country'],
                'payment_method' => $order['payment_method'],
                'tracking_number' => $order['tracking_number'],
                'carrier' => $order['carrier'],
                'priority' => $order['priority']
            ])->execute();
        }

        // Thêm order items cho các orders
        $this->insertOrderItems($db);
    }

    /**
     * Thêm order items mẫu
     */
    private function insertOrderItems($db)
    {
        // Lấy order IDs và product IDs
        $orderIds = $db->query("SELECT id FROM orders WHERE order_number LIKE 'VN%' ORDER BY id")->fetchAll();
        $productIds = $db->query("SELECT id, price FROM products ORDER BY id LIMIT 10")->fetchAll();

        if (empty($orderIds) || empty($productIds)) {
            return;
        }

        $orderItems = [
            // Order 1 items
            [
                'order_id' => $orderIds[0]['id'],
                'product_id' => $productIds[0]['id'],
                'quantity' => 1,
                'price' => $productIds[0]['price']
            ],
            [
                'order_id' => $orderIds[0]['id'],
                'product_id' => $productIds[1]['id'],
                'quantity' => 1,
                'price' => $productIds[1]['price']
            ],

            // Order 2 items
            [
                'order_id' => $orderIds[1]['id'],
                'product_id' => $productIds[2]['id'],
                'quantity' => 2,
                'price' => $productIds[2]['price']
            ],

            // Order 3 items
            [
                'order_id' => $orderIds[2]['id'],
                'product_id' => $productIds[3]['id'],
                'quantity' => 1,
                'price' => $productIds[3]['price']
            ],
            [
                'order_id' => $orderIds[2]['id'],
                'product_id' => $productIds[4]['id'],
                'quantity' => 3,
                'price' => $productIds[4]['price']
            ]
        ];

        $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)";

        foreach ($orderItems as $item) {
            $db->query($sql)->bind([
                'order_id' => $item['order_id'],
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price']
            ])->execute();
        }
    }
}
