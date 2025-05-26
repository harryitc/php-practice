<?php

/**
 * Script kiểm tra và chạy tất cả migrations
 * 
 * Script này sẽ:
 * 1. Kiểm tra tất cả migration files
 * 2. Hiển thị trạng thái của từng migration
 * 3. Chạy các migration chưa được áp dụng
 * 4. Báo cáo kết quả chi tiết
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
echo "    <title>Kiểm Tra và Chạy Migrations</title>\n";
echo "    <style>\n";
echo "        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }\n";
echo "        .container { max-width: 1000px; margin: 0 auto; background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); overflow: hidden; }\n";
echo "        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }\n";
echo "        .content { padding: 30px; }\n";
echo "        h1 { margin: 0; font-size: 2.5em; font-weight: 300; }\n";
echo "        .subtitle { margin: 10px 0 0 0; opacity: 0.9; font-size: 1.1em; }\n";
echo "        .migration-item { background: #f8f9fa; border-radius: 8px; padding: 15px; margin: 10px 0; border-left: 4px solid #6c757d; }\n";
echo "        .migration-applied { border-left-color: #28a745; }\n";
echo "        .migration-pending { border-left-color: #ffc107; }\n";
echo "        .migration-error { border-left-color: #dc3545; }\n";
echo "        .migration-new { border-left-color: #17a2b8; }\n";
echo "        .status { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 0.8em; font-weight: bold; }\n";
echo "        .status-applied { background: #d4edda; color: #155724; }\n";
echo "        .status-pending { background: #fff3cd; color: #856404; }\n";
echo "        .status-error { background: #f8d7da; color: #721c24; }\n";
echo "        .status-new { background: #d1ecf1; color: #0c5460; }\n";
echo "        .success { color: #155724; background: #d4edda; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #28a745; }\n";
echo "        .error { color: #721c24; background: #f8d7da; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #dc3545; }\n";
echo "        .info { color: #0c5460; background: #d1ecf1; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #17a2b8; }\n";
echo "        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }\n";
echo "        .summary-card { background: white; padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }\n";
echo "        .summary-number { font-size: 2em; font-weight: bold; color: #667eea; }\n";
echo "        .summary-label { color: #6c757d; margin-top: 5px; }\n";
echo "        .links { margin-top: 30px; text-align: center; padding: 20px; background: #f8f9fa; border-radius: 10px; }\n";
echo "        .links a { display: inline-block; margin: 5px 10px; padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 25px; transition: transform 0.2s ease; }\n";
echo "        .links a:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }\n";
echo "        .migration-description { font-size: 0.9em; color: #6c757d; margin-top: 5px; }\n";
echo "    </style>\n";
echo "</head>\n";
echo "<body>\n";

echo "<div class='container'>\n";
echo "<div class='header'>\n";
echo "<h1>🔧 Migration Manager</h1>\n";
echo "<p class='subtitle'>Kiểm tra và quản lý database migrations</p>\n";
echo "</div>\n";

echo "<div class='content'>\n";

try {
    // Test database connection
    echo "<div class='info'>\n";
    echo "<h3>🔌 Kiểm tra kết nối cơ sở dữ liệu</h3>\n";
    $db = Database::getInstance();
    echo "✓ Kết nối cơ sở dữ liệu thành công\n";
    echo "</div>\n";
    
    // Get all migration files
    $migrationFiles = scandir('app/migrations');
    $migrationFiles = array_diff($migrationFiles, ['.', '..']);
    sort($migrationFiles);
    
    // Get applied migrations
    $migration = new Migration();
    $appliedMigrations = $migration->getAppliedMigrations();
    
    // Analyze migrations
    $totalMigrations = count($migrationFiles);
    $appliedCount = count($appliedMigrations);
    $pendingCount = $totalMigrations - $appliedCount;
    
    // Display summary
    echo "<div class='summary'>\n";
    echo "<div class='summary-card'><div class='summary-number'>{$totalMigrations}</div><div class='summary-label'>Tổng migrations</div></div>\n";
    echo "<div class='summary-card'><div class='summary-number'>{$appliedCount}</div><div class='summary-label'>Đã áp dụng</div></div>\n";
    echo "<div class='summary-card'><div class='summary-number'>{$pendingCount}</div><div class='summary-label'>Chưa áp dụng</div></div>\n";
    echo "</div>\n";
    
    // Display migration status
    echo "<h3>📋 Trạng thái migrations</h3>\n";
    
    $migrationDescriptions = [
        'm0001_users.php' => 'Tạo bảng users và admin mặc định',
        'm0002_categories.php' => 'Tạo bảng categories và dữ liệu mẫu',
        'm0003_products.php' => 'Tạo bảng products và sản phẩm mẫu',
        'm0004_orders.php' => 'Tạo bảng orders với đầy đủ thông tin',
        'm0005_order_items.php' => 'Tạo bảng order_items',
        'm0006_order_status_history.php' => 'Tạo bảng lịch sử trạng thái đơn hàng',
        'm0007_order_tracking.php' => 'Tạo bảng tracking đơn hàng',
        'm0008_order_notes.php' => 'Tạo bảng ghi chú đơn hàng',
        'm0009_order_payments.php' => 'Tạo bảng thanh toán đơn hàng',
        'm0010_order_notifications.php' => 'Tạo bảng thông báo đơn hàng',
        'm0011_sample_data_vietnamese.php' => 'Thêm dữ liệu mẫu tiếng Việt',
        'm0012_shopping_cart.php' => 'Tạo bảng giỏ hàng',
        'm0013_coupons.php' => 'Tạo bảng mã giảm giá và sử dụng',
        'm0014_product_reviews.php' => 'Tạo bảng đánh giá sản phẩm',
        'm0015_wishlist.php' => 'Tạo bảng danh sách yêu thích',
        'm0016_customer_addresses.php' => 'Tạo bảng địa chỉ khách hàng',
        'm0017_shipping_methods.php' => 'Tạo bảng phương thức vận chuyển',
        'm0018_payment_methods.php' => 'Tạo bảng phương thức thanh toán',
        'm0019_inventory_logs.php' => 'Tạo bảng lịch sử tồn kho',
        'm0020_system_settings.php' => 'Tạo bảng cài đặt hệ thống và email templates'
    ];
    
    foreach ($migrationFiles as $file) {
        $isApplied = in_array($file, $appliedMigrations);
        $statusClass = $isApplied ? 'migration-applied' : 'migration-pending';
        $statusText = $isApplied ? 'Đã áp dụng' : 'Chưa áp dụng';
        $statusBadgeClass = $isApplied ? 'status-applied' : 'status-pending';
        
        echo "<div class='migration-item {$statusClass}'>\n";
        echo "<strong>{$file}</strong> ";
        echo "<span class='status {$statusBadgeClass}'>{$statusText}</span>\n";
        
        if (isset($migrationDescriptions[$file])) {
            echo "<div class='migration-description'>{$migrationDescriptions[$file]}</div>\n";
        }
        
        echo "</div>\n";
    }
    
    // Run pending migrations if any
    if ($pendingCount > 0) {
        echo "<div class='info'>\n";
        echo "<h3>🚀 Chạy migrations chưa áp dụng</h3>\n";
        echo "Đang áp dụng {$pendingCount} migration(s)...\n";
        echo "</div>\n";
        
        $appliedMigrations = $migration->applyMigrations();
        
        if (!empty($appliedMigrations)) {
            echo "<div class='success'>\n";
            echo "<h3>✅ Hoàn thành!</h3>\n";
            echo "Đã áp dụng thành công " . count($appliedMigrations) . " migration(s):\n";
            echo "<ul>\n";
            foreach ($appliedMigrations as $migrationName) {
                echo "<li>{$migrationName}</li>\n";
            }
            echo "</ul>\n";
            echo "</div>\n";
        } else {
            echo "<div class='info'>\n";
            echo "<h3>ℹ️ Không có migration mới</h3>\n";
            echo "Tất cả migrations đã được áp dụng trước đó.\n";
            echo "</div>\n";
        }
    } else {
        echo "<div class='success'>\n";
        echo "<h3>✅ Tất cả migrations đã được áp dụng</h3>\n";
        echo "Cơ sở dữ liệu đã được cập nhật với tất cả migrations có sẵn.\n";
        echo "</div>\n";
    }
    
    // Final verification
    echo "<div class='info'>\n";
    echo "<h3>🔍 Kiểm tra cuối cùng</h3>\n";
    
    // Check some key tables
    $tables = ['users', 'categories', 'products', 'orders', 'order_items', 'shopping_cart', 'coupons', 'product_reviews', 'wishlist', 'system_settings'];
    $existingTables = [];
    
    foreach ($tables as $table) {
        try {
            $result = $db->query("SHOW TABLES LIKE '{$table}'")->fetch();
            if ($result) {
                $existingTables[] = $table;
            }
        } catch (Exception $e) {
            // Table doesn't exist
        }
    }
    
    echo "Đã tạo " . count($existingTables) . "/" . count($tables) . " bảng chính:\n";
    echo "<ul>\n";
    foreach ($existingTables as $table) {
        echo "<li>✓ {$table}</li>\n";
    }
    echo "</ul>\n";
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<div class='error'>\n";
    echo "<h3>❌ Lỗi khi chạy migrations</h3>\n";
    echo "<p><strong>Thông báo lỗi:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>\n";
    echo "<p><strong>Dòng:</strong> " . $e->getLine() . "</p>\n";
    echo "<details><summary>Chi tiết lỗi</summary><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></details>\n";
    echo "</div>\n";
}

echo "<div class='links'>\n";
echo "<a href='/'>🏠 Trang chủ</a>\n";
echo "<a href='/setup_complete_database.php'>🚀 Setup hoàn chỉnh</a>\n";
echo "<a href='/test_order_placement.php'>🧪 Test đặt hàng</a>\n";
echo "<a href='/run-migrations.php'>🔧 Migration đơn giản</a>\n";
echo "</div>\n";

echo "</div>\n";
echo "</div>\n";
echo "</body>\n";
echo "</html>\n";
?>
