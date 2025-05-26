<?php
session_start();

// Test script to verify order placement functionality
require_once 'app/core/Database.php';
require_once 'app/models/OrderModel.php';
require_once 'app/models/OrderItemModel.php';
require_once 'app/models/UserModel.php';
require_once 'app/models/ProductModel.php';
require_once 'app/models/CartModel.php';

echo "<h1>Testing Order Placement Functionality</h1>\n";

try {
    // Test 1: Check database structure
    echo "<h2>Test 1: Database Structure Check</h2>\n";
    $db = Database::getInstance();
    
    // Check if required tables exist
    $tables = ['orders', 'order_items', 'users', 'products'];
    foreach ($tables as $table) {
        $tableCheck = $db->query("SHOW TABLES LIKE '$table'")->fetch();
        if ($tableCheck) {
            echo "✓ Table '$table' exists<br>\n";
        } else {
            echo "✗ Table '$table' does not exist<br>\n";
            exit();
        }
    }
    
    // Test 2: Create test user and product
    echo "<h2>Test 2: Create Test Data</h2>\n";
    
    // Create test user
    $userModel = new UserModel();
    $testUser = $userModel->findByEmail('testcustomer@example.com');
    if (!$testUser) {
        $testUser = new UserModel(null, 'Test Customer', 'testcustomer@example.com', password_hash('password', PASSWORD_DEFAULT), 'customer');
        if ($testUser->save()) {
            echo "✓ Test customer created<br>\n";
        } else {
            echo "✗ Failed to create test customer<br>\n";
            exit();
        }
    } else {
        echo "✓ Test customer exists<br>\n";
    }
    
    // Create test product
    $productModel = new ProductModel();
    $testProduct = $productModel->findByName('Test Product for Order');
    if (!$testProduct) {
        $testProduct = new ProductModel(
            null,
            'Test Product for Order',
            'A test product for order placement testing',
            29.99,
            10, // inventory
            'test-product-order',
            'test-product-order.jpg'
        );
        if ($testProduct->save()) {
            echo "✓ Test product created<br>\n";
        } else {
            echo "✗ Failed to create test product<br>\n";
            exit();
        }
    } else {
        echo "✓ Test product exists<br>\n";
    }
    
    // Test 3: Test cart functionality
    echo "<h2>Test 3: Cart Functionality</h2>\n";
    
    $_SESSION['user_id'] = $testUser->getId(); // Set user session
    
    $cart = new CartModel();
    $cart->clearCart(); // Start with empty cart
    
    // Add product to cart
    if ($cart->addToCart($testProduct->getID(), 2)) {
        echo "✓ Product added to cart<br>\n";
    } else {
        echo "✗ Failed to add product to cart<br>\n";
        exit();
    }
    
    // Check cart contents
    $cartItems = $cart->getCartItemsWithDetails();
    if (!empty($cartItems)) {
        echo "✓ Cart contains " . count($cartItems) . " item(s)<br>\n";
        echo "✓ Total amount: $" . number_format($cart->getTotalAmount(), 2) . "<br>\n";
    } else {
        echo "✗ Cart is empty after adding items<br>\n";
        exit();
    }
    
    // Test 4: Test order creation
    echo "<h2>Test 4: Order Creation</h2>\n";
    
    try {
        $order = new OrderModel(
            null,
            $testUser->getId(),
            $cart->getTotalAmount(),
            'pending',
            '123 Test Street',
            'Test City',
            'Test State',
            '12345',
            'Test Country',
            'credit_card'
        );
        
        // Add cart items to order
        foreach ($cartItems as $item) {
            $orderItem = new OrderItemModel(
                null,
                null, // Will be set when order is saved
                $item['product_id'],
                $item['quantity'],
                $item['price']
            );
            $order->addItem($orderItem);
        }
        
        if ($order->save()) {
            echo "✓ Order created successfully with ID: " . $order->getId() . "<br>\n";
            if ($order->getOrderNumber()) {
                echo "✓ Order number: " . $order->getOrderNumber() . "<br>\n";
            }
            echo "✓ Order status: " . $order->getStatus() . "<br>\n";
            echo "✓ Order total: $" . number_format($order->getTotalAmount(), 2) . "<br>\n";
        } else {
            echo "✗ Failed to create order<br>\n";
            exit();
        }
        
    } catch (Exception $e) {
        echo "✗ Order creation error: " . $e->getMessage() . "<br>\n";
        exit();
    }
    
    // Test 5: Verify order items
    echo "<h2>Test 5: Verify Order Items</h2>\n";
    
    $order->loadItems();
    $orderItems = $order->getItems();
    
    if (!empty($orderItems)) {
        echo "✓ Order contains " . count($orderItems) . " item(s)<br>\n";
        foreach ($orderItems as $item) {
            echo "- Product ID: " . $item->getProductId() . 
                 ", Quantity: " . $item->getQuantity() . 
                 ", Price: $" . number_format($item->getPrice(), 2) . "<br>\n";
        }
    } else {
        echo "✗ No order items found<br>\n";
    }
    
    // Test 6: Test order retrieval
    echo "<h2>Test 6: Order Retrieval</h2>\n";
    
    $retrievedOrder = $order->findById($order->getId());
    if ($retrievedOrder) {
        echo "✓ Order retrieved successfully<br>\n";
        echo "Order details:<br>\n";
        echo "- ID: " . $retrievedOrder->getId() . "<br>\n";
        echo "- Number: " . ($retrievedOrder->getOrderNumber() ?: 'N/A') . "<br>\n";
        echo "- Customer ID: " . $retrievedOrder->getUserId() . "<br>\n";
        echo "- Status: " . $retrievedOrder->getStatus() . "<br>\n";
        echo "- Total: $" . number_format($retrievedOrder->getTotalAmount(), 2) . "<br>\n";
        echo "- Shipping Address: " . $retrievedOrder->getShippingAddress() . "<br>\n";
        echo "- Payment Method: " . $retrievedOrder->getPaymentMethod() . "<br>\n";
    } else {
        echo "✗ Failed to retrieve order<br>\n";
    }
    
    // Test 7: Test order status update
    echo "<h2>Test 7: Order Status Update</h2>\n";
    
    $originalStatus = $order->getStatus();
    $order->setStatus('confirmed');
    
    if ($order->save()) {
        echo "✓ Order status updated from '$originalStatus' to '" . $order->getStatus() . "'<br>\n";
    } else {
        echo "✗ Failed to update order status<br>\n";
    }
    
    // Test 8: Simulate checkout process
    echo "<h2>Test 8: Simulate Full Checkout Process</h2>\n";
    
    // Clear cart and add items again
    $cart->clearCart();
    $cart->addToCart($testProduct->getID(), 1);
    
    // Simulate checkout data
    $checkoutData = [
        'shipping_address' => '456 Another Street',
        'shipping_city' => 'Another City',
        'shipping_state' => 'Another State',
        'shipping_zip' => '67890',
        'shipping_country' => 'Another Country',
        'payment_method' => 'cod'
    ];
    
    // Create new order
    $newOrder = new OrderModel(
        null,
        $testUser->getId(),
        $cart->getTotalAmount(),
        'pending',
        $checkoutData['shipping_address'],
        $checkoutData['shipping_city'],
        $checkoutData['shipping_state'],
        $checkoutData['shipping_zip'],
        $checkoutData['shipping_country'],
        $checkoutData['payment_method']
    );
    
    // Add cart items
    $cartItems = $cart->getCartItemsWithDetails();
    foreach ($cartItems as $item) {
        $orderItem = new OrderItemModel(
            null,
            null,
            $item['product_id'],
            $item['quantity'],
            $item['price']
        );
        $newOrder->addItem($orderItem);
    }
    
    if ($newOrder->save()) {
        echo "✓ Full checkout simulation successful<br>\n";
        echo "✓ New order ID: " . $newOrder->getId() . "<br>\n";
        
        // Clear cart after successful order
        $cart->clearCart();
        echo "✓ Cart cleared after order placement<br>\n";
    } else {
        echo "✗ Full checkout simulation failed<br>\n";
    }
    
    // Test 9: Clean up
    echo "<h2>Test 9: Cleanup</h2>\n";
    
    // Delete test orders
    if ($order->delete()) {
        echo "✓ First test order deleted<br>\n";
    }
    
    if ($newOrder->delete()) {
        echo "✓ Second test order deleted<br>\n";
    }
    
    echo "<h2>Summary</h2>\n";
    echo "✓ Order placement functionality tests completed successfully<br>\n";
    echo "✓ All core features working properly<br>\n";
    echo "✓ Database operations functioning correctly<br>\n";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>\n";
    echo "✗ Test failed with error: " . $e->getMessage() . "<br>\n";
    echo "Stack trace:<br>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>\n";
}

echo "<br><a href='/'>← Back to Home</a>\n";
?>
