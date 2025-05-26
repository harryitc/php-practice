<?php
// Database migration script to fix order placement issues
require_once 'app/core/Database.php';

echo "<h1>Database Migration for Order System</h1>\n";

try {
    $db = Database::getInstance();
    
    echo "<h2>Step 1: Check Current Database Structure</h2>\n";
    
    // Check if orders table exists
    $tablesCheck = $db->query("SHOW TABLES LIKE 'orders'")->fetch();
    if (!$tablesCheck) {
        echo "✗ Orders table does not exist. Creating...<br>\n";
        
        $createOrdersTable = "
        CREATE TABLE orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            status ENUM('pending', 'confirmed', 'processing', 'packed', 'shipped', 'out_for_delivery', 'delivered', 'cancelled', 'returned', 'refunded') DEFAULT 'pending',
            shipping_address TEXT,
            shipping_city VARCHAR(100),
            shipping_state VARCHAR(100),
            shipping_zip VARCHAR(20),
            shipping_country VARCHAR(100),
            payment_method VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        
        if ($db->query($createOrdersTable)->execute()) {
            echo "✓ Orders table created<br>\n";
        } else {
            echo "✗ Failed to create orders table<br>\n";
            exit();
        }
    } else {
        echo "✓ Orders table exists<br>\n";
    }
    
    // Check if order_items table exists
    $orderItemsCheck = $db->query("SHOW TABLES LIKE 'order_items'")->fetch();
    if (!$orderItemsCheck) {
        echo "✗ Order items table does not exist. Creating...<br>\n";
        
        $createOrderItemsTable = "
        CREATE TABLE order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            price DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )";
        
        if ($db->query($createOrderItemsTable)->execute()) {
            echo "✓ Order items table created<br>\n";
        } else {
            echo "✗ Failed to create order items table<br>\n";
            exit();
        }
    } else {
        echo "✓ Order items table exists<br>\n";
    }
    
    echo "<h2>Step 2: Add Missing Columns</h2>\n";
    
    // Check and add order_number column
    $orderNumberCheck = $db->query("SHOW COLUMNS FROM orders LIKE 'order_number'")->fetch();
    if (!$orderNumberCheck) {
        echo "Adding order_number column...<br>\n";
        $addOrderNumber = "ALTER TABLE orders ADD COLUMN order_number VARCHAR(50) UNIQUE AFTER id";
        if ($db->query($addOrderNumber)->execute()) {
            echo "✓ Order number column added<br>\n";
            
            // Update existing orders with order numbers
            $updateOrderNumbers = "UPDATE orders SET order_number = CONCAT('ORD-', LPAD(id, 6, '0')) WHERE order_number IS NULL";
            $db->query($updateOrderNumbers)->execute();
            echo "✓ Existing orders updated with order numbers<br>\n";
        } else {
            echo "- Order number column may already exist or failed to add<br>\n";
        }
    } else {
        echo "✓ Order number column exists<br>\n";
    }
    
    // Check and add tracking columns
    $trackingCheck = $db->query("SHOW COLUMNS FROM orders LIKE 'tracking_number'")->fetch();
    if (!$trackingCheck) {
        echo "Adding tracking columns...<br>\n";
        $addTracking = "ALTER TABLE orders ADD COLUMN tracking_number VARCHAR(100) AFTER order_number";
        $db->query($addTracking)->execute();
        
        $addCarrier = "ALTER TABLE orders ADD COLUMN carrier VARCHAR(50) AFTER tracking_number";
        $db->query($addCarrier)->execute();
        echo "✓ Tracking columns added<br>\n";
    } else {
        echo "✓ Tracking columns exist<br>\n";
    }
    
    echo "<h2>Step 3: Create Supporting Tables</h2>\n";
    
    // Create order_status_history table
    $statusHistoryCheck = $db->query("SHOW TABLES LIKE 'order_status_history'")->fetch();
    if (!$statusHistoryCheck) {
        echo "Creating order status history table...<br>\n";
        $createStatusHistory = "
        CREATE TABLE order_status_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            old_status VARCHAR(50),
            new_status VARCHAR(50) NOT NULL,
            changed_by INT,
            change_reason VARCHAR(255),
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
        )";
        
        if ($db->query($createStatusHistory)->execute()) {
            echo "✓ Order status history table created<br>\n";
        } else {
            echo "- Failed to create order status history table<br>\n";
        }
    } else {
        echo "✓ Order status history table exists<br>\n";
    }
    
    // Create order_notes table
    $notesCheck = $db->query("SHOW TABLES LIKE 'order_notes'")->fetch();
    if (!$notesCheck) {
        echo "Creating order notes table...<br>\n";
        $createNotes = "
        CREATE TABLE order_notes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            user_id INT,
            title VARCHAR(255),
            content TEXT NOT NULL,
            note_type ENUM('admin', 'customer', 'system') DEFAULT 'admin',
            is_visible_to_customer BOOLEAN DEFAULT FALSE,
            priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )";
        
        if ($db->query($createNotes)->execute()) {
            echo "✓ Order notes table created<br>\n";
        } else {
            echo "- Failed to create order notes table<br>\n";
        }
    } else {
        echo "✓ Order notes table exists<br>\n";
    }
    
    echo "<h2>Step 4: Create Indexes for Performance</h2>\n";
    
    // Create indexes
    $indexes = [
        "CREATE INDEX idx_orders_user_id ON orders(user_id)",
        "CREATE INDEX idx_orders_status ON orders(status)",
        "CREATE INDEX idx_orders_created_at ON orders(created_at)",
        "CREATE INDEX idx_order_items_order_id ON order_items(order_id)",
        "CREATE INDEX idx_order_items_product_id ON order_items(product_id)"
    ];
    
    foreach ($indexes as $index) {
        try {
            $db->query($index)->execute();
            echo "✓ Index created<br>\n";
        } catch (Exception $e) {
            echo "- Index may already exist: " . $e->getMessage() . "<br>\n";
        }
    }
    
    echo "<h2>Step 5: Verify Database Structure</h2>\n";
    
    // Verify orders table structure
    $ordersColumns = $db->query("SHOW COLUMNS FROM orders")->fetchAll();
    echo "Orders table columns:<br>\n";
    foreach ($ordersColumns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")<br>\n";
    }
    
    // Verify order_items table structure
    $orderItemsColumns = $db->query("SHOW COLUMNS FROM order_items")->fetchAll();
    echo "<br>Order items table columns:<br>\n";
    foreach ($orderItemsColumns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")<br>\n";
    }
    
    echo "<h2>Migration Complete!</h2>\n";
    echo "✓ Database structure is now ready for order placement<br>\n";
    echo "✓ All required tables and columns have been created<br>\n";
    echo "✓ Indexes have been added for better performance<br>\n";
    
    echo "<br><strong>Next Steps:</strong><br>\n";
    echo "1. Test order placement functionality<br>\n";
    echo "2. Verify customer checkout process<br>\n";
    echo "3. Test admin order management<br>\n";
    
} catch (Exception $e) {
    echo "<h2>Migration Error</h2>\n";
    echo "✗ Migration failed: " . $e->getMessage() . "<br>\n";
    echo "Stack trace:<br>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>\n";
}

echo "<br><a href='/test_order_placement.php'>→ Test Order Placement</a><br>\n";
echo "<a href='/'>← Back to Home</a>\n";
?>
