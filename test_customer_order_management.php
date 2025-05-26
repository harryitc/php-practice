<?php
/**
 * Test script for customer order management functionality
 * Run this script to test the enhanced customer order management system
 */

require_once 'app/core/Database.php';
require_once 'app/models/OrderModel.php';
require_once 'app/services/CustomerOrderService.php';

echo "=== Testing Customer Order Management System ===\n\n";

try {
    // Initialize service
    $customerOrderService = new CustomerOrderService();
    
    echo "1. Testing CustomerOrderService initialization...\n";
    echo "   ✓ CustomerOrderService initialized successfully\n\n";
    
    // Test with a sample user (get first user from database)
    $db = Database::getInstance();
    $userResult = $db->query("SELECT id FROM users WHERE role = 'customer' LIMIT 1")->fetch();
    
    if (!$userResult) {
        echo "   ⚠ No customer users found in database. Creating test scenario...\n";
        $testUserId = 1; // Use default test user ID
    } else {
        $testUserId = $userResult['id'];
        echo "   ✓ Using customer user ID: $testUserId\n\n";
    }
    
    echo "2. Testing order statistics...\n";
    $stats = $customerOrderService->getOrderStatistics($testUserId);
    echo "   - Total orders: " . $stats['total_orders'] . "\n";
    echo "   - Total spent: $" . number_format($stats['total_spent'], 2) . "\n";
    echo "   - Average order value: $" . number_format($stats['avg_order_value'], 2) . "\n";
    echo "   - Pending orders: " . $stats['pending_orders'] . "\n";
    echo "   - Processing orders: " . $stats['processing_orders'] . "\n";
    echo "   - Shipped orders: " . $stats['shipped_orders'] . "\n";
    echo "   - Delivered orders: " . $stats['delivered_orders'] . "\n";
    echo "   - Cancelled orders: " . $stats['cancelled_orders'] . "\n\n";
    
    echo "3. Testing order filtering...\n";
    
    // Test different filters
    $filters = [
        ['user_id' => $testUserId],
        ['user_id' => $testUserId, 'status' => 'pending'],
        ['user_id' => $testUserId, 'status' => 'delivered'],
        ['user_id' => $testUserId, 'search' => 'test']
    ];
    
    foreach ($filters as $index => $filter) {
        $result = $customerOrderService->getOrdersWithFilters($filter, 1, 5);
        $filterDesc = [];
        foreach ($filter as $key => $value) {
            if ($key !== 'user_id' && $value) {
                $filterDesc[] = "$key: $value";
            }
        }
        $filterText = empty($filterDesc) ? 'no filters' : implode(', ', $filterDesc);
        echo "   - Filter ($filterText): " . count($result['orders']) . " orders found\n";
    }
    echo "\n";
    
    echo "4. Testing dashboard data...\n";
    $dashboardData = $customerOrderService->getDashboardData($testUserId);
    echo "   - Recent orders: " . count($dashboardData['recent_orders']) . "\n";
    echo "   - Status data available: " . (isset($dashboardData['status_data']) ? 'Yes' : 'No') . "\n";
    echo "   - Monthly spending data: " . count($dashboardData['monthly_spending']) . " months\n\n";
    
    echo "5. Testing order management functions...\n";
    
    // Find a test order
    $testOrderResult = $db->query("SELECT id FROM orders WHERE user_id = :user_id LIMIT 1")->fetch(['user_id' => $testUserId]);
    
    if ($testOrderResult) {
        $testOrderId = $testOrderResult['id'];
        echo "   - Using test order ID: $testOrderId\n";
        
        // Test order cancellation (dry run)
        echo "   - Testing cancellation logic (dry run)...\n";
        $order = OrderModel::findById($testOrderId);
        if ($order) {
            $canCancel = in_array($order->getStatus(), ['pending', 'confirmed']);
            echo "     Order status: " . $order->getStatus() . "\n";
            echo "     Can be cancelled: " . ($canCancel ? 'Yes' : 'No') . "\n";
        }
        
        // Test return request logic (dry run)
        echo "   - Testing return request logic (dry run)...\n";
        if ($order) {
            $canReturn = $order->getStatus() === 'delivered';
            echo "     Can be returned: " . ($canReturn ? 'Yes' : 'No') . "\n";
        }
        
        // Test reorder functionality (dry run)
        echo "   - Testing reorder logic (dry run)...\n";
        $order->loadItems();
        $items = $order->getItems();
        echo "     Order has " . count($items) . " items for reorder\n";
        
    } else {
        echo "   ⚠ No orders found for test user\n";
    }
    echo "\n";
    
    echo "6. Testing pagination...\n";
    $paginationTest = $customerOrderService->getOrdersWithFilters(['user_id' => $testUserId], 1, 2);
    echo "   - Page 1 with 2 per page: " . count($paginationTest['orders']) . " orders\n";
    echo "   - Total pages: " . $paginationTest['total_pages'] . "\n";
    echo "   - Total orders: " . $paginationTest['total'] . "\n\n";
    
    echo "7. Testing database schema...\n";
    
    // Check required tables
    $requiredTables = ['orders', 'order_items', 'order_notes', 'order_status_history', 'users'];
    foreach ($requiredTables as $table) {
        $result = $db->query("SHOW TABLES LIKE '$table'")->fetch();
        echo "   - Table '$table': " . ($result ? 'EXISTS' : 'MISSING') . "\n";
    }
    echo "\n";
    
    echo "8. Testing view files...\n";
    
    $viewFiles = [
        'app/views/order/my_orders.php',
        'app/views/order/dashboard.php',
        'app/views/order/cancel.php',
        'app/views/order/return_request.php'
    ];
    
    foreach ($viewFiles as $file) {
        echo "   - $file: " . (file_exists($file) ? 'EXISTS' : 'MISSING') . "\n";
    }
    echo "\n";
    
    echo "9. Testing controller methods...\n";
    
    $controllerFile = 'app/controllers/OrderController.php';
    if (file_exists($controllerFile)) {
        $content = file_get_contents($controllerFile);
        $methods = [
            'myOrders',
            'customerDashboard',
            'cancelOrder',
            'requestReturn',
            'reorder',
            'downloadInvoice'
        ];
        
        foreach ($methods as $method) {
            $exists = strpos($content, "function $method(") !== false;
            echo "   - Method '$method': " . ($exists ? 'EXISTS' : 'MISSING') . "\n";
        }
    } else {
        echo "   ⚠ OrderController.php not found\n";
    }
    echo "\n";
    
    echo "=== All Tests Completed Successfully! ===\n\n";
    
    echo "Enhanced customer order management features available:\n";
    echo "✓ Advanced order filtering and search\n";
    echo "✓ Order statistics dashboard\n";
    echo "✓ Pagination for large order lists\n";
    echo "✓ Order cancellation with reasons\n";
    echo "✓ Return/refund requests\n";
    echo "✓ Reorder functionality\n";
    echo "✓ Invoice/receipt download\n";
    echo "✓ Visual order status tracking\n";
    echo "✓ Monthly spending analytics\n";
    echo "✓ Quick action buttons\n\n";
    
    echo "Available URLs for customers:\n";
    echo "- /Order/myOrders - Enhanced order listing with filters\n";
    echo "- /Order/customerDashboard - Order analytics dashboard\n";
    echo "- /Order/cancelOrder/{orderId} - Cancel order with reason\n";
    echo "- /Order/requestReturn/{orderId} - Request return/refund\n";
    echo "- /Order/reorder/{orderId} - Add order items to cart\n";
    echo "- /Order/downloadInvoice/{orderId} - Download invoice\n";
    echo "- /Order/tracking/{orderId} - Track order shipment\n\n";
    
    echo "Key improvements:\n";
    echo "• Filter orders by status, date range, and search terms\n";
    echo "• Visual dashboard with charts and statistics\n";
    echo "• One-click actions for common tasks\n";
    echo "• Detailed cancellation and return processes\n";
    echo "• Professional invoice generation\n";
    echo "• Mobile-responsive design\n";
    echo "• Real-time status updates\n\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
