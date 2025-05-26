<?php

require_once 'app/core/Database.php';
require_once 'app/models/OrderModel.php';
require_once 'app/models/OrderTrackingModel.php';

class TrackingService
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create comprehensive tracking update
     */
    public function createTrackingUpdate($orderId, $data)
    {
        try {
            $order = OrderModel::findById($orderId);
            if (!$order) {
                throw new Exception("Order not found");
            }
            
            // Create tracking record
            $tracking = new OrderTrackingModel(
                null,
                $orderId,
                $data['tracking_number'] ?? $order->getTrackingNumber(),
                $data['carrier'] ?? $order->getCarrier(),
                $data['status'],
                $data['location'] ?? null,
                $data['description'] ?? null,
                $data['tracking_date'] ?? date('Y-m-d H:i:s')
            );
            
            // Set additional properties if provided
            if (isset($data['estimated_delivery'])) {
                $tracking->setEstimatedDelivery($data['estimated_delivery']);
            }
            
            if (isset($data['recipient_name'])) {
                $tracking->setRecipientName($data['recipient_name']);
            }
            
            if (isset($data['signature_obtained'])) {
                $tracking->setSignatureObtained($data['signature_obtained']);
            }
            
            // Mark as delivered if status is delivered
            if ($data['status'] === 'delivered') {
                $tracking->setIsDelivered(true);
            }
            
            // Update order tracking info if provided
            if (isset($data['tracking_number']) && $data['tracking_number']) {
                $order->setTrackingNumber($data['tracking_number']);
            }
            
            if (isset($data['carrier']) && $data['carrier']) {
                $order->setCarrier($data['carrier']);
            }
            
            // Calculate and set estimated delivery if not provided
            if (!isset($data['estimated_delivery'])) {
                $estimatedDelivery = $tracking->calculateEstimatedDelivery();
                $tracking->setEstimatedDelivery($estimatedDelivery);
                $order->setEstimatedDeliveryDate(date('Y-m-d', strtotime($estimatedDelivery)));
            }
            
            // Save tracking (this will auto-update order status and send notifications)
            if ($tracking->save()) {
                $order->save();
                return $tracking;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Tracking update error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get comprehensive tracking information for an order
     */
    public function getOrderTrackingInfo($orderId)
    {
        $order = OrderModel::findById($orderId);
        if (!$order) {
            return null;
        }
        
        $trackingHistory = OrderTrackingModel::getByOrderId($orderId);
        $latestTracking = OrderTrackingModel::getLatestByOrderId($orderId);
        
        return [
            'order' => $order,
            'tracking_history' => $trackingHistory,
            'latest_tracking' => $latestTracking,
            'progress_percentage' => $this->calculateProgressPercentage($order->getStatus()),
            'estimated_delivery' => $this->getEstimatedDelivery($order, $latestTracking),
            'is_delayed' => $this->isOrderDelayed($order, $latestTracking),
            'next_update_expected' => $this->getNextUpdateExpected($latestTracking)
        ];
    }
    
    /**
     * Calculate progress percentage based on order status
     */
    private function calculateProgressPercentage($status)
    {
        $statusSteps = [
            'pending' => 10,
            'confirmed' => 20,
            'processing' => 40,
            'packed' => 60,
            'shipped' => 70,
            'out_for_delivery' => 90,
            'delivered' => 100
        ];
        
        return $statusSteps[$status] ?? 0;
    }
    
    /**
     * Get estimated delivery date
     */
    private function getEstimatedDelivery($order, $latestTracking)
    {
        // Use order's estimated delivery if available
        if ($order->getEstimatedDeliveryDate()) {
            return $order->getEstimatedDeliveryDate();
        }
        
        // Use tracking's estimated delivery if available
        if ($latestTracking && $latestTracking->getEstimatedDelivery()) {
            return $latestTracking->getEstimatedDelivery();
        }
        
        // Calculate based on order creation date and carrier
        if ($order->getCarrier()) {
            $carrierDays = [
                'FedEx' => 2,
                'UPS' => 3,
                'DHL' => 2,
                'USPS' => 5,
                'Local Delivery' => 1
            ];
            
            $days = $carrierDays[$order->getCarrier()] ?? 3;
            $estimatedDate = new DateTime($order->getCreatedAt());
            $estimatedDate->add(new DateInterval("P{$days}D"));
            
            return $estimatedDate->format('Y-m-d');
        }
        
        return null;
    }
    
    /**
     * Check if order is delayed
     */
    private function isOrderDelayed($order, $latestTracking)
    {
        $estimatedDelivery = $this->getEstimatedDelivery($order, $latestTracking);
        
        if (!$estimatedDelivery) {
            return false;
        }
        
        $today = new DateTime();
        $estimated = new DateTime($estimatedDelivery);
        
        // Consider delayed if past estimated delivery and not delivered
        return $today > $estimated && $order->getStatus() !== 'delivered';
    }
    
    /**
     * Get next expected update time
     */
    private function getNextUpdateExpected($latestTracking)
    {
        if (!$latestTracking) {
            return null;
        }
        
        $status = $latestTracking->getStatus();
        $lastUpdate = new DateTime($latestTracking->getTrackingDate());
        
        // Expected update intervals based on status
        $intervals = [
            'picked_up' => 1, // 1 day
            'in_transit' => 1, // 1 day
            'out_for_delivery' => 0.5, // 12 hours
            'delivered' => 0, // No more updates
            'exception' => 0.5 // 12 hours
        ];
        
        $intervalDays = $intervals[$status] ?? 1;
        
        if ($intervalDays > 0) {
            $nextUpdate = clone $lastUpdate;
            $hours = $intervalDays * 24;
            $nextUpdate->add(new DateInterval("PT{$hours}H"));
            
            return $nextUpdate->format('Y-m-d H:i:s');
        }
        
        return null;
    }
    
    /**
     * Simulate tracking updates from carrier API
     */
    public function simulateCarrierUpdate($orderId)
    {
        $order = OrderModel::findById($orderId);
        if (!$order || !$order->getTrackingNumber()) {
            return false;
        }
        
        $latestTracking = OrderTrackingModel::getLatestByOrderId($orderId);
        $currentStatus = $latestTracking ? $latestTracking->getStatus() : 'picked_up';
        
        // Simulate progression
        $statusProgression = [
            'picked_up' => 'in_transit',
            'in_transit' => 'out_for_delivery',
            'out_for_delivery' => 'delivered'
        ];
        
        $nextStatus = $statusProgression[$currentStatus] ?? null;
        
        if ($nextStatus) {
            $locations = [
                'in_transit' => 'Distribution Center, ' . $order->getShippingCity(),
                'out_for_delivery' => 'Local Delivery Facility, ' . $order->getShippingCity(),
                'delivered' => $order->getShippingAddress()
            ];
            
            $descriptions = [
                'in_transit' => 'Package is in transit to destination',
                'out_for_delivery' => 'Package is out for delivery',
                'delivered' => 'Package has been delivered'
            ];
            
            return $this->createTrackingUpdate($orderId, [
                'status' => $nextStatus,
                'location' => $locations[$nextStatus] ?? null,
                'description' => $descriptions[$nextStatus] ?? null,
                'tracking_date' => date('Y-m-d H:i:s')
            ]);
        }
        
        return false;
    }
    
    /**
     * Get tracking statistics for admin dashboard
     */
    public function getTrackingStatistics($dateRange = 30)
    {
        $sql = "SELECT 
                    COUNT(*) as total_shipments,
                    SUM(CASE WHEN ot.status = 'delivered' THEN 1 ELSE 0 END) as delivered_count,
                    SUM(CASE WHEN ot.status = 'exception' THEN 1 ELSE 0 END) as exception_count,
                    AVG(DATEDIFF(ot.tracking_date, o.created_at)) as avg_delivery_days
                FROM order_tracking ot
                JOIN orders o ON ot.order_id = o.id
                WHERE ot.created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                AND ot.status IN ('delivered', 'exception')";
        
        $result = $this->db->query($sql)->fetch(['days' => $dateRange]);
        
        $deliveryRate = $result['total_shipments'] > 0 
            ? ($result['delivered_count'] / $result['total_shipments']) * 100 
            : 0;
        
        return [
            'total_shipments' => $result['total_shipments'],
            'delivered_count' => $result['delivered_count'],
            'exception_count' => $result['exception_count'],
            'delivery_rate' => round($deliveryRate, 2),
            'avg_delivery_days' => round($result['avg_delivery_days'], 1)
        ];
    }
    
    /**
     * Get orders that need tracking updates
     */
    public function getOrdersNeedingUpdates()
    {
        $sql = "SELECT DISTINCT o.id, o.order_number, o.tracking_number, o.carrier
                FROM orders o
                LEFT JOIN order_tracking ot ON o.id = ot.order_id
                WHERE o.status IN ('shipped', 'out_for_delivery')
                AND o.tracking_number IS NOT NULL
                AND (ot.id IS NULL OR ot.tracking_date < DATE_SUB(NOW(), INTERVAL 24 HOUR))
                ORDER BY o.created_at ASC";
        
        return $this->db->query($sql)->fetchAll();
    }
}
