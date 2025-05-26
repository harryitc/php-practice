<?php

/**
 * Script chạy migration dữ liệu mẫu tiếng Việt
 * 
 * Script này sẽ thêm dữ liệu mẫu tiếng Việt với hình ảnh từ Unsplash
 * vào cơ sở dữ liệu e-commerce.
 */

// Set content type để hiển thị tiếng Việt đúng
header('Content-Type: text/html; charset=UTF-8');

// Load required files
require_once 'app/core/Database.php';
require_once 'app/core/Migration.php';

echo "<!DOCTYPE html>\n";
echo "<html lang='vi'>\n";
echo "<head>\n";
echo "    <meta charset='UTF-8'>\n";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
echo "    <title>Thêm Dữ Liệu Mẫu Tiếng Việt</title>\n";
echo "    <style>\n";
echo "        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }\n";
echo "        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }\n";
echo "        h1 { color: #333; text-align: center; }\n";
echo "        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 4px; margin: 10px 0; }\n";
echo "        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0; }\n";
echo "        .info { color: #17a2b8; background: #d1ecf1; padding: 10px; border-radius: 4px; margin: 10px 0; }\n";
echo "        .step { margin: 15px 0; padding: 10px; border-left: 4px solid #007bff; background: #f8f9fa; }\n";
echo "        .links { margin-top: 20px; text-align: center; }\n";
echo "        .links a { display: inline-block; margin: 5px 10px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }\n";
echo "        .links a:hover { background: #0056b3; }\n";
echo "    </style>\n";
echo "</head>\n";
echo "<body>\n";
echo "<div class='container'>\n";

echo "<h1>🇻🇳 Thêm Dữ Liệu Mẫu Tiếng Việt</h1>\n";

try {
    echo "<div class='step'>\n";
    echo "<h3>Bước 1: Kiểm tra kết nối cơ sở dữ liệu</h3>\n";
    
    // Test database connection
    $db = Database::getInstance();
    echo "<div class='success'>✓ Kết nối cơ sở dữ liệu thành công</div>\n";
    echo "</div>\n";
    
    echo "<div class='step'>\n";
    echo "<h3>Bước 2: Chạy migration dữ liệu mẫu</h3>\n";
    
    // Load and run the Vietnamese sample data migration
    require_once 'app/migrations/m0011_sample_data_vietnamese.php';
    
    $migration = new M0011SampleDataVietnamese();
    
    echo "<div class='info'>📝 Đang thêm dữ liệu mẫu tiếng Việt...</div>\n";
    
    $migration->up();
    
    echo "<div class='success'>✓ Đã thêm dữ liệu mẫu thành công!</div>\n";
    echo "</div>\n";
    
    echo "<div class='step'>\n";
    echo "<h3>Bước 3: Kiểm tra dữ liệu đã thêm</h3>\n";
    
    // Check categories
    $categories = $db->query("SELECT COUNT(*) as count FROM categories")->fetch();
    echo "<div class='info'>📂 Số danh mục: {$categories['count']}</div>\n";
    
    // Check products
    $products = $db->query("SELECT COUNT(*) as count FROM products")->fetch();
    echo "<div class='info'>🛍️ Số sản phẩm: {$products['count']}</div>\n";
    
    // Check users
    $users = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")->fetch();
    echo "<div class='info'>👥 Số khách hàng: {$users['count']}</div>\n";
    
    // Check orders
    $orders = $db->query("SELECT COUNT(*) as count FROM orders")->fetch();
    echo "<div class='info'>📦 Số đơn hàng: {$orders['count']}</div>\n";
    
    echo "</div>\n";
    
    echo "<div class='step'>\n";
    echo "<h3>Bước 4: Hiển thị một số dữ liệu mẫu</h3>\n";
    
    // Show sample categories
    echo "<h4>📂 Danh mục sản phẩm:</h4>\n";
    $sampleCategories = $db->query("SELECT name, description FROM categories LIMIT 3")->fetchAll();
    foreach ($sampleCategories as $cat) {
        echo "<div class='info'>• <strong>{$cat['name']}</strong>: {$cat['description']}</div>\n";
    }
    
    // Show sample products
    echo "<h4>🛍️ Sản phẩm mẫu:</h4>\n";
    $sampleProducts = $db->query("SELECT name, price, status FROM products LIMIT 3")->fetchAll();
    foreach ($sampleProducts as $product) {
        $price = number_format($product['price'], 0, ',', '.') . ' VNĐ';
        echo "<div class='info'>• <strong>{$product['name']}</strong> - {$price} ({$product['status']})</div>\n";
    }
    
    // Show sample users
    echo "<h4>👥 Khách hàng mẫu:</h4>\n";
    $sampleUsers = $db->query("SELECT name, email FROM users WHERE role = 'customer' LIMIT 3")->fetchAll();
    foreach ($sampleUsers as $user) {
        echo "<div class='info'>• <strong>{$user['name']}</strong> - {$user['email']}</div>\n";
    }
    
    echo "</div>\n";
    
    echo "<div class='success'>\n";
    echo "<h3>🎉 Hoàn thành!</h3>\n";
    echo "<p>Dữ liệu mẫu tiếng Việt đã được thêm thành công vào cơ sở dữ liệu.</p>\n";
    echo "<p><strong>Thông tin đăng nhập:</strong></p>\n";
    echo "<ul>\n";
    echo "<li><strong>Admin:</strong> admin@example.com / admin123</li>\n";
    echo "<li><strong>Khách hàng mẫu:</strong> hoa.nguyen@vietnam.com / 123456</li>\n";
    echo "<li><strong>Khách hàng mẫu:</strong> nam.tran@vietnam.com / 123456</li>\n";
    echo "</ul>\n";
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<div class='error'>\n";
    echo "<h3>❌ Lỗi khi thêm dữ liệu</h3>\n";
    echo "<p><strong>Thông báo lỗi:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>\n";
    echo "<p><strong>Dòng:</strong> " . $e->getLine() . "</p>\n";
    echo "</div>\n";
    
    echo "<div class='info'>\n";
    echo "<h4>💡 Gợi ý khắc phục:</h4>\n";
    echo "<ul>\n";
    echo "<li>Kiểm tra kết nối cơ sở dữ liệu trong <code>app/config/database.php</code></li>\n";
    echo "<li>Đảm bảo đã chạy migration cơ bản trước: <a href='/run-migrations.php'>Chạy Migration</a></li>\n";
    echo "<li>Kiểm tra quyền truy cập cơ sở dữ liệu</li>\n";
    echo "</ul>\n";
    echo "</div>\n";
}

echo "<div class='links'>\n";
echo "<a href='/'>🏠 Trang chủ</a>\n";
echo "<a href='/run-migrations.php'>🔧 Chạy Migration</a>\n";
echo "<a href='/test_order_placement.php'>🧪 Test Đặt hàng</a>\n";
echo "</div>\n";

echo "</div>\n";
echo "</body>\n";
echo "</html>\n";
?>
