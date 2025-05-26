<?php

/**
 * Script thiết lập hoàn chỉnh cơ sở dữ liệu
 * 
 * Script này sẽ:
 * 1. Chạy tất cả migration cơ bản
 * 2. Thêm dữ liệu mẫu tiếng Việt
 * 3. Kiểm tra và báo cáo kết quả
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
echo "    <title>Thiết Lập Hoàn Chỉnh Cơ Sở Dữ Liệu</title>\n";
echo "    <style>\n";
echo "        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }\n";
echo "        .container { max-width: 900px; margin: 0 auto; background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); overflow: hidden; }\n";
echo "        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }\n";
echo "        .content { padding: 30px; }\n";
echo "        h1 { margin: 0; font-size: 2.5em; font-weight: 300; }\n";
echo "        .subtitle { margin: 10px 0 0 0; opacity: 0.9; font-size: 1.1em; }\n";
echo "        .success { color: #155724; background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #28a745; }\n";
echo "        .error { color: #721c24; background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #dc3545; }\n";
echo "        .info { color: #0c5460; background: #d1ecf1; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #17a2b8; }\n";
echo "        .warning { color: #856404; background: #fff3cd; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #ffc107; }\n";
echo "        .step { margin: 20px 0; padding: 20px; border-radius: 10px; background: #f8f9fa; border: 1px solid #e9ecef; }\n";
echo "        .step h3 { margin-top: 0; color: #495057; }\n";
echo "        .progress { background: #e9ecef; border-radius: 10px; overflow: hidden; margin: 15px 0; }\n";
echo "        .progress-bar { background: linear-gradient(90deg, #28a745, #20c997); height: 20px; transition: width 0.3s ease; }\n";
echo "        .links { margin-top: 30px; text-align: center; padding: 20px; background: #f8f9fa; border-radius: 10px; }\n";
echo "        .links a { display: inline-block; margin: 5px 10px; padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 25px; transition: transform 0.2s ease; }\n";
echo "        .links a:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }\n";
echo "        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }\n";
echo "        .stat-card { background: white; padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }\n";
echo "        .stat-number { font-size: 2em; font-weight: bold; color: #667eea; }\n";
echo "        .stat-label { color: #6c757d; margin-top: 5px; }\n";
echo "    </style>\n";
echo "</head>\n";
echo "<body>\n";

echo "<div class='container'>\n";
echo "<div class='header'>\n";
echo "<h1>🚀 Thiết Lập E-commerce</h1>\n";
echo "<p class='subtitle'>Cơ sở dữ liệu hoàn chỉnh với dữ liệu mẫu tiếng Việt</p>\n";
echo "</div>\n";

echo "<div class='content'>\n";

$totalSteps = 4;
$currentStep = 0;

try {
    // Step 1: Database Connection
    $currentStep++;
    echo "<div class='step'>\n";
    echo "<h3>Bước {$currentStep}/{$totalSteps}: 🔌 Kiểm tra kết nối cơ sở dữ liệu</h3>\n";
    echo "<div class='progress'><div class='progress-bar' style='width: " . ($currentStep/$totalSteps*100) . "%'></div></div>\n";
    
    $db = Database::getInstance();
    echo "<div class='success'>✓ Kết nối cơ sở dữ liệu thành công</div>\n";
    echo "</div>\n";
    
    // Step 2: Run Basic Migrations
    $currentStep++;
    echo "<div class='step'>\n";
    echo "<h3>Bước {$currentStep}/{$totalSteps}: 🔧 Chạy migration cơ bản</h3>\n";
    echo "<div class='progress'><div class='progress-bar' style='width: " . ($currentStep/$totalSteps*100) . "%'></div></div>\n";
    
    $migration = new Migration();
    $appliedMigrations = $migration->applyMigrations();
    
    if (empty($appliedMigrations)) {
        echo "<div class='info'>ℹ️ Tất cả migration cơ bản đã được áp dụng trước đó</div>\n";
    } else {
        echo "<div class='success'>✓ Đã áp dụng " . count($appliedMigrations) . " migration:</div>\n";
        foreach ($appliedMigrations as $migrationName) {
            echo "<div class='info'>• {$migrationName}</div>\n";
        }
    }
    echo "</div>\n";
    
    // Step 3: Add Vietnamese Sample Data
    $currentStep++;
    echo "<div class='step'>\n";
    echo "<h3>Bước {$currentStep}/{$totalSteps}: 🇻🇳 Thêm dữ liệu mẫu tiếng Việt</h3>\n";
    echo "<div class='progress'><div class='progress-bar' style='width: " . ($currentStep/$totalSteps*100) . "%'></div></div>\n";
    
    // Check if Vietnamese data already exists
    $existingCategories = $db->query("SELECT COUNT(*) as count FROM categories WHERE name LIKE '%Chăm sóc%'")->fetch();
    
    if ($existingCategories['count'] > 0) {
        echo "<div class='warning'>⚠️ Dữ liệu tiếng Việt đã tồn tại. Đang cập nhật...</div>\n";
        
        // Clear existing Vietnamese data
        require_once 'app/migrations/m0011_sample_data_vietnamese.php';
        $vietnameseMigration = new M0011SampleDataVietnamese();
        $vietnameseMigration->down();
        echo "<div class='info'>🗑️ Đã xóa dữ liệu cũ</div>\n";
    }
    
    // Add new Vietnamese data
    require_once 'app/migrations/m0011_sample_data_vietnamese.php';
    $vietnameseMigration = new M0011SampleDataVietnamese();
    $vietnameseMigration->up();
    
    echo "<div class='success'>✓ Đã thêm dữ liệu mẫu tiếng Việt thành công!</div>\n";
    echo "</div>\n";
    
    // Step 4: Verification and Statistics
    $currentStep++;
    echo "<div class='step'>\n";
    echo "<h3>Bước {$currentStep}/{$totalSteps}: 📊 Kiểm tra và thống kê</h3>\n";
    echo "<div class='progress'><div class='progress-bar' style='width: 100%'></div></div>\n";
    
    // Get statistics
    $stats = [];
    $stats['categories'] = $db->query("SELECT COUNT(*) as count FROM categories")->fetch()['count'];
    $stats['products'] = $db->query("SELECT COUNT(*) as count FROM products")->fetch()['count'];
    $stats['customers'] = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")->fetch()['count'];
    $stats['orders'] = $db->query("SELECT COUNT(*) as count FROM orders")->fetch()['count'];
    $stats['order_items'] = $db->query("SELECT COUNT(*) as count FROM order_items")->fetch()['count'];
    
    echo "<div class='stats'>\n";
    echo "<div class='stat-card'><div class='stat-number'>{$stats['categories']}</div><div class='stat-label'>Danh mục</div></div>\n";
    echo "<div class='stat-card'><div class='stat-number'>{$stats['products']}</div><div class='stat-label'>Sản phẩm</div></div>\n";
    echo "<div class='stat-card'><div class='stat-number'>{$stats['customers']}</div><div class='stat-label'>Khách hàng</div></div>\n";
    echo "<div class='stat-card'><div class='stat-number'>{$stats['orders']}</div><div class='stat-label'>Đơn hàng</div></div>\n";
    echo "</div>\n";
    
    echo "<div class='success'>✓ Cơ sở dữ liệu đã được thiết lập hoàn chỉnh!</div>\n";
    echo "</div>\n";
    
    // Sample Data Preview
    echo "<div class='step'>\n";
    echo "<h3>🎯 Xem trước dữ liệu mẫu</h3>\n";
    
    // Sample products with images
    echo "<h4>🛍️ Sản phẩm nổi bật:</h4>\n";
    $featuredProducts = $db->query("SELECT name, price, image, description FROM products ORDER BY RAND() LIMIT 3")->fetchAll();
    foreach ($featuredProducts as $product) {
        $price = number_format($product['price'], 0, ',', '.') . ' VNĐ';
        echo "<div class='info'>\n";
        echo "<strong>{$product['name']}</strong> - <span style='color: #28a745; font-weight: bold;'>{$price}</span><br>\n";
        echo "<small>{$product['description']}</small><br>\n";
        if ($product['image']) {
            echo "<img src='{$product['image']}' alt='{$product['name']}' style='width: 60px; height: 60px; object-fit: cover; border-radius: 5px; margin-top: 5px;'>\n";
        }
        echo "</div>\n";
    }
    
    // Sample orders
    echo "<h4>📦 Đơn hàng mẫu:</h4>\n";
    $sampleOrders = $db->query("
        SELECT o.order_number, u.name, o.total_amount, o.status, o.shipping_city 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC 
        LIMIT 3
    ")->fetchAll();
    
    foreach ($sampleOrders as $order) {
        $total = number_format($order['total_amount'], 0, ',', '.') . ' VNĐ';
        $statusColors = [
            'pending' => '#ffc107',
            'confirmed' => '#17a2b8',
            'processing' => '#fd7e14',
            'shipped' => '#6f42c1',
            'delivered' => '#28a745'
        ];
        $statusColor = $statusColors[$order['status']] ?? '#6c757d';
        
        echo "<div class='info'>\n";
        echo "<strong>#{$order['order_number']}</strong> - {$order['name']}<br>\n";
        echo "Tổng tiền: <strong style='color: #28a745;'>{$total}</strong> | ";
        echo "Trạng thái: <span style='color: {$statusColor}; font-weight: bold;'>" . ucfirst($order['status']) . "</span><br>\n";
        echo "Giao đến: {$order['shipping_city']}\n";
        echo "</div>\n";
    }
    
    echo "</div>\n";
    
    // Success message with login info
    echo "<div class='success'>\n";
    echo "<h3>🎉 Thiết lập hoàn tất!</h3>\n";
    echo "<p>Cơ sở dữ liệu e-commerce đã được thiết lập hoàn chỉnh với dữ liệu mẫu tiếng Việt.</p>\n";
    echo "<h4>🔑 Thông tin đăng nhập:</h4>\n";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0;'>\n";
    echo "<strong>👨‍💼 Admin:</strong><br>\n";
    echo "Email: <code>admin@example.com</code><br>\n";
    echo "Mật khẩu: <code>admin123</code><br><br>\n";
    echo "<strong>👥 Khách hàng mẫu:</strong><br>\n";
    echo "Email: <code>hoa.nguyen@vietnam.com</code> | Mật khẩu: <code>123456</code><br>\n";
    echo "Email: <code>nam.tran@vietnam.com</code> | Mật khẩu: <code>123456</code><br>\n";
    echo "Email: <code>mai.le@vietnam.com</code> | Mật khẩu: <code>123456</code>\n";
    echo "</div>\n";
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<div class='error'>\n";
    echo "<h3>❌ Lỗi trong quá trình thiết lập</h3>\n";
    echo "<p><strong>Thông báo lỗi:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>\n";
    echo "<p><strong>Dòng:</strong> " . $e->getLine() . "</p>\n";
    echo "<details><summary>Chi tiết lỗi</summary><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></details>\n";
    echo "</div>\n";
}

echo "<div class='links'>\n";
echo "<a href='/'>🏠 Trang chủ</a>\n";
echo "<a href='/test_order_placement.php'>🧪 Test đặt hàng</a>\n";
echo "<a href='/test_tracking.php'>📍 Test tracking</a>\n";
echo "<a href='/run-migrations.php'>🔧 Migration</a>\n";
echo "</div>\n";

echo "</div>\n";
echo "</div>\n";
echo "</body>\n";
echo "</html>\n";
?>
