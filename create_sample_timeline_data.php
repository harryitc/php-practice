<?php
require_once 'app/core/Database.php';
require_once 'app/models/OrderModel.php';
require_once 'app/models/OrderStatusHistoryModel.php';
require_once 'app/models/OrderTrackingModel.php';
require_once 'app/models/OrderNotesModel.php';

echo "Creating sample timeline data...\n";

$db = Database::getInstance();

// Get existing orders
$orders = $db->query('SELECT * FROM orders LIMIT 2')->fetchAll();

foreach ($orders as $orderData) {
    $orderId = $orderData['id'];
    echo "Processing Order ID: $orderId\n";
    
    // Update order with tracking info
    $orderNumber = 'ORD-' . date('Y') . '-' . str_pad($orderId, 6, '0', STR_PAD_LEFT);
    $trackingNumber = 'TRK' . date('Ymd') . str_pad($orderId, 4, '0', STR_PAD_LEFT);
    
    $db->query("UPDATE orders SET 
        order_number = :order_number,
        tracking_number = :tracking_number,
        carrier = 'FedEx',
        estimated_delivery_date = DATE_ADD(NOW(), INTERVAL 3 DAY),
        priority = 'normal'
        WHERE id = :id")
        ->bind([
            'order_number' => $orderNumber,
            'tracking_number' => $trackingNumber,
            'id' => $orderId
        ])->execute();
    
    // Create status history
    $statusChanges = [
        ['old_status' => null, 'new_status' => 'pending', 'reason' => 'Order placed by customer', 'time_offset' => '-2 hours'],
        ['old_status' => 'pending', 'new_status' => 'confirmed', 'reason' => 'Payment verified', 'time_offset' => '-1 hour 30 minutes'],
        ['old_status' => 'confirmed', 'new_status' => 'processing', 'reason' => 'Order sent to fulfillment center', 'time_offset' => '-1 hour'],
    ];
    
    if ($orderId == 1) {
        // Order 1 - More advanced status
        $statusChanges[] = ['old_status' => 'processing', 'new_status' => 'packed', 'reason' => 'Items packed and ready for shipment', 'time_offset' => '-30 minutes'];
        $statusChanges[] = ['old_status' => 'packed', 'new_status' => 'shipped', 'reason' => 'Package picked up by carrier', 'time_offset' => '-15 minutes'];
    }
    
    foreach ($statusChanges as $change) {
        $timestamp = date('Y-m-d H:i:s', strtotime($change['time_offset']));
        
        $db->query("INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, change_reason, created_at) 
                    VALUES (:order_id, :old_status, :new_status, 1, :reason, :created_at)")
            ->bind([
                'order_id' => $orderId,
                'old_status' => $change['old_status'],
                'new_status' => $change['new_status'],
                'reason' => $change['reason'],
                'created_at' => $timestamp
            ])->execute();
    }
    
    // Create tracking events
    $trackingEvents = [
        ['status' => 'picked_up', 'location' => 'Fulfillment Center, New York', 'description' => 'Package picked up by FedEx', 'time_offset' => '-15 minutes'],
        ['status' => 'in_transit', 'location' => 'FedEx Facility, Newark, NJ', 'description' => 'Package in transit to destination', 'time_offset' => '-10 minutes'],
    ];
    
    if ($orderId == 1) {
        // Order 1 - More tracking events
        $trackingEvents[] = ['status' => 'in_transit', 'location' => 'FedEx Facility, Philadelphia, PA', 'description' => 'Package arrived at intermediate facility', 'time_offset' => '-5 minutes'];
        $trackingEvents[] = ['status' => 'out_for_delivery', 'location' => 'Local Delivery Facility', 'description' => 'Out for delivery', 'time_offset' => '-2 minutes'];
    }
    
    foreach ($trackingEvents as $event) {
        $timestamp = date('Y-m-d H:i:s', strtotime($event['time_offset']));
        
        $db->query("INSERT INTO order_tracking (order_id, tracking_number, carrier, status, location, description, tracking_date) 
                    VALUES (:order_id, :tracking_number, 'FedEx', :status, :location, :description, :tracking_date)")
            ->bind([
                'order_id' => $orderId,
                'tracking_number' => $trackingNumber,
                'status' => $event['status'],
                'location' => $event['location'],
                'description' => $event['description'],
                'tracking_date' => $timestamp
            ])->execute();
    }
    
    // Create order notes
    $notes = [
        ['type' => 'internal', 'title' => 'Processing Note', 'content' => 'Customer requested expedited processing', 'visible' => false, 'time_offset' => '-45 minutes'],
        ['type' => 'customer', 'title' => 'Shipping Update', 'content' => 'Your order has been processed and will ship soon', 'visible' => true, 'time_offset' => '-20 minutes'],
    ];
    
    if ($orderId == 1) {
        $notes[] = ['type' => 'system', 'title' => 'Inventory Check', 'content' => 'All items confirmed in stock', 'visible' => false, 'time_offset' => '-1 hour 15 minutes'];
    }
    
    foreach ($notes as $note) {
        $timestamp = date('Y-m-d H:i:s', strtotime($note['time_offset']));
        
        $db->query("INSERT INTO order_notes (order_id, user_id, note_type, title, content, is_visible_to_customer, priority, created_at) 
                    VALUES (:order_id, 1, :note_type, :title, :content, :visible, 'normal', :created_at)")
            ->bind([
                'order_id' => $orderId,
                'note_type' => $note['type'],
                'title' => $note['title'],
                'content' => $note['content'],
                'visible' => $note['visible'] ? 1 : 0,
                'created_at' => $timestamp
            ])->execute();
    }
    
    // Update order status
    $finalStatus = $orderId == 1 ? 'shipped' : 'processing';
    $db->query("UPDATE orders SET status = :status WHERE id = :id")
        ->bind(['status' => $finalStatus, 'id' => $orderId])
        ->execute();
    
    echo "Created timeline data for Order ID: $orderId (Status: $finalStatus)\n";
}

echo "Sample timeline data created successfully!\n";
echo "You can now view the timeline at:\n";
echo "- Customer Timeline: /Order/timeline/1 or /Order/timeline/2\n";
echo "- Admin Timeline: /Order/adminTimeline/1 or /Order/adminTimeline/2\n";
?>
